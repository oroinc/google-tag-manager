<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;

/**
 * Handles completed Checkout to generate purchase data.

 * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
 */
class CheckoutEventListener
{
    private FrontendHelper $frontendHelper;

    private DataLayerManager $dataLayerManager;

    private PurchaseDetailProvider $purchaseDetailProvider;

    private GoogleTagManagerSettingsProviderInterface $settingsProvider;

    private ?DataCollectionStateProviderInterface $dataCollectionStateProvider = null;

    private array $data = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataLayerManager $dataLayerManager,
        PurchaseDetailProvider $purchaseDetailProvider,
        GoogleTagManagerSettingsProviderInterface $settingsProvider
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataLayerManager = $dataLayerManager;
        $this->purchaseDetailProvider = $purchaseDetailProvider;
        $this->settingsProvider = $settingsProvider;
    }

    public function setDataCollectionStateProvider(
        ?DataCollectionStateProviderInterface $dataCollectionStateProvider
    ): void {
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
            $this->dataLayerManager->add($data);
        }

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->data = [];
    }

    private function isApplicable(): bool
    {
        if ($this->dataCollectionStateProvider) {
            $isApplicable = $this->dataCollectionStateProvider->isEnabled('universal_analytics');
        } else {
            $isApplicable = (bool) $this->settingsProvider->getGoogleTagManagerSettings();
        }

        return $isApplicable && $this->frontendHelper->isFrontendRequest();
    }
}
