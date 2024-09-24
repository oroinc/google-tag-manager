<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\CheckoutInfoEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CheckoutInfoEventListenerTest extends \PHPUnit\Framework\TestCase
{
    private const INITIAL_DATA = ['option1' => 'value1'];

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dataCollectionStateProvider;

    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var CheckoutDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $checkoutDetailProvider;

    /** @var CheckoutInfoEventListener */
    private $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->checkoutDetailProvider = $this->createMock(CheckoutDetailProvider::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())
            ->method('getSession')
            ->willReturn(new Session(new MockArraySessionStorage()));

        $this->dataLayerManager = new DataLayerManager($requestStack, []);
        $this->dataLayerManager->append(['option1' => 'value1']);

        $container = TestContainerBuilder::create()
            ->add(DataCollectionStateProviderInterface::class, $this->dataCollectionStateProvider)
            ->add(DataLayerManager::class, $this->dataLayerManager)
            ->add(CheckoutDetailProvider::class, $this->checkoutDetailProvider)
            ->getContainer($this);

        $this->listener = new CheckoutInfoEventListener($this->frontendHelper, $container);
    }

    private function getPreUpdateEventArgs(Checkout $checkout, array $changeSet): PreUpdateEventArgs
    {
        return new PreUpdateEventArgs(
            $checkout,
            $this->createMock(EntityManagerInterface::class),
            $changeSet
        );
    }

    public function testPreUpdateWhenNotFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->checkoutDetailProvider->expects(self::never())
            ->method(self::anything());

        $checkout = new Checkout();
        $changeSet = [];

        $this->listener->preUpdate($checkout, $this->getPreUpdateEventArgs($checkout, $changeSet));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenNoGtmSettings(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->checkoutDetailProvider->expects(self::never())
            ->method(self::anything());

        $checkout = new Checkout();
        $changeSet = [];

        $this->listener->preUpdate($checkout, $this->getPreUpdateEventArgs($checkout, $changeSet));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenNoChanges(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->checkoutDetailProvider->expects(self::never())
            ->method(self::anything());

        $checkout = new Checkout();
        $changeSet = [];

        $this->listener->preUpdate($checkout, $this->getPreUpdateEventArgs($checkout, $changeSet));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenChangedShippingMethod(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $checkout = new Checkout();
        $shippingInfoData = ['event' => 'add_shipping_info', 'ecommerce' => ['option1' => 'value1']];
        $this->checkoutDetailProvider->expects(self::once())
            ->method('getShippingInfoData')
            ->with($checkout)
            ->willReturn([$shippingInfoData]);

        $changeSet = ['shippingMethod' => [null, 'sample_method']];

        $this->listener->preUpdate($checkout, $this->getPreUpdateEventArgs($checkout, $changeSet));
        $this->listener->postFlush();
        // Ensures that event data is empty now.
        $this->listener->postFlush();

        self::assertSame([self::INITIAL_DATA, $shippingInfoData], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenChangedPaymentMethod(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $checkout = new Checkout();
        $paymentInfoData = ['event' => 'add_shipping_info', 'ecommerce' => ['option1' => 'value1']];
        $this->checkoutDetailProvider->expects(self::once())
            ->method('getPaymentInfoData')
            ->with($checkout)
            ->willReturn([$paymentInfoData]);

        $changeSet = ['paymentMethod' => [null, 'sample_method']];

        $this->listener->preUpdate($checkout, $this->getPreUpdateEventArgs($checkout, $changeSet));
        $this->listener->postFlush();
        // Ensures that event data is empty now.
        $this->listener->postFlush();

        self::assertSame([self::INITIAL_DATA, $paymentInfoData], $this->dataLayerManager->collectAll());
    }
}
