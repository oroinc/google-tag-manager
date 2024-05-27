<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\BeginCheckoutEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Component\Action\Event\ExtendableConditionEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class BeginCheckoutEventListenerTest extends \PHPUnit\Framework\TestCase
{
    private const INITIAL_DATA = ['option1' => 'value1'];

    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject $dataCollectionStateProvider;

    private DataLayerManager $dataLayerManager;

    private CheckoutDetailProvider|\PHPUnit\Framework\MockObject\MockObject $checkoutDetailProvider;

    private BeginCheckoutEventListener $listener;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->dataLayerManager = new DataLayerManager(new Session(new MockArraySessionStorage()), []);
        $this->checkoutDetailProvider = $this->createMock(CheckoutDetailProvider::class);

        $this->dataLayerManager->append(['option1' => 'value1']);

        $this->listener = new BeginCheckoutEventListener(
            $this->frontendHelper,
            $this->dataCollectionStateProvider,
            $this->dataLayerManager,
            $this->checkoutDetailProvider
        );
    }

    public function testOnStartCheckoutWhenContextIsNotActionData(): void
    {
        $this->checkoutDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->listener->onStartCheckout(new ExtendableConditionEvent(new \stdClass()));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testOnStartCheckoutWhenNotFrontendRequest(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->checkoutDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->listener->onStartCheckout(new ExtendableConditionEvent(new ActionData()));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testOnStartCheckoutWhenNoGtmSettings(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataCollectionStateProvider
            ->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->checkoutDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->listener->onStartCheckout(new ExtendableConditionEvent(new ActionData()));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testOnStartCheckoutWhenNoCheckout(): void
    {
        $this->dataCollectionStateProvider
            ->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->checkoutDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $context = new ActionData();
        $context->set('checkout', new \stdClass());
        $this->listener->onStartCheckout(new ExtendableConditionEvent($context));

        self::assertSame([self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }

    public function testOnStartCheckout(): void
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
        $beginCheckoutData = ['event' => 'begin_checkout', 'ecommerce' => ['option1' => 'value1']];
        $this->checkoutDetailProvider
            ->expects(self::once())
            ->method('getBeginCheckoutData')
            ->with($checkout)
            ->willReturn([$beginCheckoutData]);

        $context = new ActionData();
        $context->set('checkout', $checkout);
        $this->listener->onStartCheckout(new ExtendableConditionEvent($context));

        self::assertSame([$beginCheckoutData, self::INITIAL_DATA], $this->dataLayerManager->collectAll());
    }
}
