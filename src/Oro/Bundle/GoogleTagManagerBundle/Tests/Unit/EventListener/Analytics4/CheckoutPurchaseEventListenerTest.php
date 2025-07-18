<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\CheckoutPurchaseEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CheckoutPurchaseEventListenerTest extends TestCase
{
    private const INITIAL_DATA = ['option1' => 'value1'];

    private FrontendHelper&MockObject $frontendHelper;
    private DataCollectionStateProviderInterface&MockObject $dataCollectionStateProvider;
    private DataLayerManager $dataLayerManager;
    private PurchaseDetailProvider&MockObject $purchaseDetailProvider;
    private CheckoutPurchaseEventListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->purchaseDetailProvider = $this->createMock(PurchaseDetailProvider::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::any())
            ->method('getSession')
            ->willReturn(new Session(new MockArraySessionStorage()));

        $this->dataLayerManager = new DataLayerManager($requestStack, []);
        $this->dataLayerManager->append(['option1' => 'value1']);

        $container = TestContainerBuilder::create()
            ->add(DataCollectionStateProviderInterface::class, $this->dataCollectionStateProvider)
            ->add(DataLayerManager::class, $this->dataLayerManager)
            ->add(PurchaseDetailProvider::class, $this->purchaseDetailProvider)
            ->getContainer($this);

        $this->listener = new CheckoutPurchaseEventListener($this->frontendHelper, $container);
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

        $this->purchaseDetailProvider->expects(self::never())
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

        $this->purchaseDetailProvider->expects(self::never())
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

        $this->purchaseDetailProvider->expects(self::never())
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
        $this->purchaseDetailProvider->expects(self::once())
            ->method('getData')
            ->with($checkout)
            ->willReturn([$shippingInfoData]);

        $changeSet = ['completed' => [false, true]];

        $this->listener->preUpdate($checkout, $this->getPreUpdateEventArgs($checkout, $changeSet));
        $this->listener->postFlush();
        // Ensures that event data is empty now.
        $this->listener->postFlush();

        self::assertSame([self::INITIAL_DATA, $shippingInfoData], $this->dataLayerManager->collectAll());
    }
}
