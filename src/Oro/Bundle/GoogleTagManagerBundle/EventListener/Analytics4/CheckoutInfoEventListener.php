<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;

/**
 * Adds to the GTM data layer the data for the Google Analytics 4 events:
 * - add_shipping_info
 * - add_payment_info
 */
class CheckoutInfoEventListener
{
    private FrontendHelper $frontendHelper;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private DataLayerManager $dataLayerManager;

    private CheckoutDetailProvider $checkoutDetailProvider;

    private array $data = [];

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

    public function preUpdate(Checkout $checkout, PreUpdateEventArgs $args): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        if ($args->hasChangedField('shippingMethod') && $args->getNewValue('shippingMethod')) {
            $this->data = array_merge($this->data, $this->checkoutDetailProvider->getShippingInfoData($checkout));
        }

        if ($args->hasChangedField('paymentMethod') && $args->getNewValue('paymentMethod')) {
            $this->data = array_merge($this->data, $this->checkoutDetailProvider->getPaymentInfoData($checkout));
        }
    }

    public function postFlush(): void
    {
        foreach ($this->data as $data) {
            $this->dataLayerManager->append($data);
        }

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->data = [];
    }

    private function isApplicable(): bool
    {
        return $this->frontendHelper->isFrontendRequest()
            && $this->dataCollectionStateProvider->isEnabled('google_analytics4');
    }
}
