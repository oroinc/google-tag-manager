<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\CheckoutEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\TestCase;

class CheckoutEventListenerTest extends TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DataLayerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $dataLayerManager;

    /** @var PurchaseDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $purchaseDetailProvider;

    /** @var Transport|\PHPUnit\Framework\MockObject\MockObject */
    private $transport;

    /** @var GoogleTagManagerSettingsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var CheckoutEventListener */
    private $listener;

    protected function setUp()
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->frontendHelper->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->purchaseDetailProvider = $this->createMock(PurchaseDetailProvider::class);
        $this->transport = $this->createMock(Transport::class);
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new CheckoutEventListener(
            $this->frontendHelper,
            $this->dataLayerManager,
            $this->purchaseDetailProvider,
            $this->settingsProvider
        );
    }

    public function testPreUpdateNotApplicable(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->purchaseDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateNotApplicableChangeSet(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->purchaseDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $checkout = new Checkout();
        $changeSet = ['completed' => [true, false]];

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdate(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->purchaseDetailProvider->expects($this->any())
            ->method('getData')
            ->with($checkout)
            ->willReturn([['data1'], ['data2']]);

        $this->dataLayerManager->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive([['data1']], [['data2']]);

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWithClear(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $checkout = new Checkout();
        $changeSet = ['completed' => [false, true]];

        $this->purchaseDetailProvider->expects($this->any())
            ->method('getData')
            ->with($checkout)
            ->willReturn([['data1'], ['data2']]);

        $this->dataLayerManager->expects($this->never())
            ->method('add');

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($checkout, new PreUpdateEventArgs($checkout, $objectManager, $changeSet));
        $this->listener->onClear();
        $this->listener->postFlush();
    }
}
