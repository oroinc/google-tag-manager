<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\CheckoutPurchaseEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CheckoutPurchaseEventListenerTest extends TestCase
{
    private const INITIAL_DATA = ['option1' => 'value1'];

    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject $dataCollectionStateProvider;

    private DataLayerManager $dataLayerManager;

    private CheckoutDetailProvider|\PHPUnit\Framework\MockObject\MockObject $purchaseDetailProvider;

    private CheckoutPurchaseEventListener $listener;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->dataLayerManager = new DataLayerManager(new Session(new MockArraySessionStorage()), []);
        $this->purchaseDetailProvider = $this->createMock(PurchaseDetailProvider::class);

        $this->dataLayerManager->append(['option1' => 'value1']);

        $this->listener = new CheckoutPurchaseEventListener(
            $this->frontendHelper,
            $this->dataCollectionStateProvider,
            $this->dataLayerManager,
            $this->purchaseDetailProvider
        );
    }

    public function testPreUpdateWhenNotFrontendRequest(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->purchaseDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = [];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenNoGtmSettings(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataCollectionStateProvider
            ->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->purchaseDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = [];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenNoChanges(): void
    {
        $this->dataCollectionStateProvider
            ->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->purchaseDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = [];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testPreUpdateWhenChangedShippingMethod(): void
    {
        $this->dataCollectionStateProvider
            ->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $checkout = new Checkout();
        $shippingInfoData = ['event' => 'add_shipping_info', 'ecommerce' => ['option1' => 'value1']];
        $this->purchaseDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($checkout)
            ->willReturn([$shippingInfoData]);

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $changeSet = ['completed' => [false, true]];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
        // Ensures that event data is empty now.
        $this->listener->postFlush();

        self::assertSame([self::INITIAL_DATA, $shippingInfoData], $this->dataLayerManager->collectAll());
    }
}
