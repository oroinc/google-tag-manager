<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\CheckoutEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\TestCase;

class CheckoutEventListenerTest extends TestCase
{
    private DataLayerManager|\PHPUnit\Framework\MockObject\MockObject $dataLayerManager;

    private PurchaseDetailProvider|\PHPUnit\Framework\MockObject\MockObject $purchaseDetailProvider;

    private Transport|\PHPUnit\Framework\MockObject\MockObject $transport;

    private GoogleTagManagerSettingsProviderInterface|\PHPUnit\Framework\MockObject\MockObject $settingsProvider;

    private DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject $dataCollectionStateProvider;

    private CheckoutEventListener $listener;

    protected function setUp(): void
    {
        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects(self::any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->purchaseDetailProvider = $this->createMock(PurchaseDetailProvider::class);
        $this->transport = $this->createMock(Transport::class);
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

        $this->listener = new CheckoutEventListener(
            $frontendHelper,
            $this->dataLayerManager,
            $this->purchaseDetailProvider,
            $this->settingsProvider
        );

        $this->listener->setDataCollectionStateProvider($this->dataCollectionStateProvider);
    }

    public function testPreUpdateNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(false);

        $this->purchaseDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateNotApplicableChangeSet(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $this->purchaseDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = ['completed' => [true, false]];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdate(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->purchaseDetailProvider->expects(self::once())
            ->method('getData')
            ->with($checkout)
            ->willReturn([['data1'], ['data2']]);

        $this->dataLayerManager->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive([['data1']], [['data2']]);

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWithClear(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->purchaseDetailProvider->expects(self::once())
            ->method('getData')
            ->with($checkout)
            ->willReturn([['data1'], ['data2']]);

        $this->dataLayerManager->expects(self::never())
            ->method('add');

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->onClear();
        $this->listener->postFlush();
    }

    public function testPreUpdateNotApplicableWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->purchaseDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateNotApplicableChangeSetWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->purchaseDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = ['completed' => [true, false]];

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->purchaseDetailProvider->expects(self::any())
            ->method('getData')
            ->with($checkout)
            ->willReturn([['data1'], ['data2']]);

        $this->dataLayerManager->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive([['data1']], [['data2']]);

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWithClearWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->purchaseDetailProvider->expects(self::any())
            ->method('getData')
            ->with($checkout)
            ->willReturn([['data1'], ['data2']]);

        $this->dataLayerManager->expects(self::never())
            ->method('add');

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->onClear();
        $this->listener->postFlush();
    }
}
