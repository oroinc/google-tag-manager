<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;

/**
 * Adds to the GTM data layer the data for the Google Analytics 4 event:
 * - purchase
 */
class CheckoutPurchaseEventListener
{
    private FrontendHelper $frontendHelper;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private DataLayerManager $dataLayerManager;

    private PurchaseDetailProvider $purchaseDetailProvider;

    private array $data = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataCollectionStateProviderInterface $dataCollectionStateProvider,
        DataLayerManager $dataLayerManager,
        PurchaseDetailProvider $purchaseDetailProvider
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataLayerManager = $dataLayerManager;
        $this->purchaseDetailProvider = $purchaseDetailProvider;
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
    }

    public function preUpdate(Checkout $checkout, PreUpdateEventArgs $args): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        if (!$args->hasChangedField('completed') || !$args->getNewValue('completed')) {
            return;
        }

        $this->data = array_merge($this->data, $this->purchaseDetailProvider->getData($checkout));
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
