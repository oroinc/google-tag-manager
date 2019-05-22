<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\CheckoutDataProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider;

class CheckoutDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CheckoutDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $checkoutDetailProvider;

    /** @var CheckoutDataProvider */
    private $provider;

    protected function setUp()
    {
        $this->checkoutDetailProvider = $this->createMock(CheckoutDetailProvider::class);

        $this->provider = new CheckoutDataProvider($this->checkoutDetailProvider);
    }

    public function testGetData(): void
    {
        $checkout = new Checkout();
        $data = ['data'];

        $this->checkoutDetailProvider->expects($this->once())
            ->method('getData')
            ->with($this->identicalTo($checkout))
            ->willReturn($data);

        $this->assertSame($data, $this->provider->getData($checkout));
    }
}
