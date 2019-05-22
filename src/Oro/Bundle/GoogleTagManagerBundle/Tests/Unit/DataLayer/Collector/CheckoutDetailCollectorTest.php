<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CheckoutDetailCollector;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutDetailCollectorTest extends \PHPUnit\Framework\TestCase
{
    private const ROUTE_NAME = 'test_route';

    /** @var RequestStack */
    private $requestStack;

    /** @var CheckoutDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $checkoutDetailProvider;

    /** @var PurchaseDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $purchaseDetailProvider;

    /** @var CheckoutDetailCollector */
    private $collector;

    protected function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->checkoutDetailProvider = $this->createMock(CheckoutDetailProvider::class);
        $this->purchaseDetailProvider = $this->createMock(PurchaseDetailProvider::class);

        $this->collector = new CheckoutDetailCollector(
            $this->requestStack,
            $this->checkoutDetailProvider,
            $this->purchaseDetailProvider,
            self::ROUTE_NAME
        );
    }

    public function testHandleNoRequest(): void
    {
        $this->checkoutDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->purchaseDetailProvider->expects($this->never())
            ->method($this->anything());

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals(new ArrayCollection(), $data);
    }

    public function testHandleNoCheckout(): void
    {
        $this->requestStack->push(new Request([], [], ['_route' => self::ROUTE_NAME]));

        $this->checkoutDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->purchaseDetailProvider->expects($this->never())
            ->method($this->anything());

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals(new ArrayCollection(), $data);
    }

    public function testHandleUnsupportedRequest(): void
    {
        $this->requestStack->push(new Request([], [], ['checkout' => new Checkout(), '_route' => 'some_route']));

        $this->checkoutDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->purchaseDetailProvider->expects($this->never())
            ->method($this->anything());

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals(new ArrayCollection(), $data);
    }

    public function testHandleForNotCompletedCheckout(): void
    {
        $checkout = new Checkout();
        $result = ['data'];

        $this->requestStack->push(new Request([], [], ['checkout' => $checkout, '_route' => self::ROUTE_NAME]));

        $this->checkoutDetailProvider->expects($this->once())
            ->method('getData')
            ->with($checkout)
            ->willReturn($result);

        $this->purchaseDetailProvider->expects($this->never())
            ->method($this->anything());

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals(new ArrayCollection([['data']]), $data);
    }

    public function testHandleForCompletedCheckout(): void
    {
        $checkout = new Checkout();
        $checkout->setCompleted(true);

        $result = ['data'];

        $this->requestStack->push(new Request([], [], ['checkout' => $checkout, '_route' => self::ROUTE_NAME]));

        $this->checkoutDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->purchaseDetailProvider->expects($this->once())
            ->method('getData')
            ->with($checkout)
            ->willReturn($result);

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals(new ArrayCollection([['data']]), $data);
    }
}
