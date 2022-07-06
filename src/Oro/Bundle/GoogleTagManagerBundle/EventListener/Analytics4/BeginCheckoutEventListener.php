<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Component\Action\Event\ExtendableConditionEvent;

/**
 * Adds to the GTM data layer the data for the Google Analytics 4 event:
 * - begin_checkout
 */
class BeginCheckoutEventListener
{
    private FrontendHelper $frontendHelper;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private DataLayerManager $dataLayerManager;

    private CheckoutDetailProvider $checkoutDetailProvider;

    public function __construct(
        FrontendHelper $frontendHelper,
        DataCollectionStateProviderInterface $dataCollectionStateProvider,
        DataLayerManager $dataLayerManager,
        CheckoutDetailProvider $checkoutDetailProvider
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
        $this->dataLayerManager = $dataLayerManager;
        $this->checkoutDetailProvider = $checkoutDetailProvider;
    }

    public function onStartCheckout(ExtendableConditionEvent $event): void
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $checkout = $event->getContext()?->get('checkout');
        if (!$checkout instanceof Checkout) {
            return;
        }

        $data = $this->checkoutDetailProvider->getBeginCheckoutData($checkout);

        // Prepends begin_checkout event data to the data layer because it must be added before other events.
        $this->dataLayerManager->prepend(...$data);
    }

    private function isApplicable(ExtendableConditionEvent $event): bool
    {
        $context = $event->getContext();
        if (!$context instanceof ActionData) {
            return false;
        }

        return $this->frontendHelper->isFrontendRequest()
            && $this->dataCollectionStateProvider->isEnabled('google_analytics4');
    }
}
