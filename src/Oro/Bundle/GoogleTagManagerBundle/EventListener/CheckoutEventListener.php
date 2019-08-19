<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;

/**
 * Handles completed Checkout to generate purchase data.
 */
class CheckoutEventListener
{
    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var PurchaseDetailProvider */
    private $purchaseDetailProvider;

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var array */
    private $data = [];

    /**
     * @param FrontendHelper $frontendHelper
     * @param DataLayerManager $dataLayerManager
     * @param PurchaseDetailProvider $purchaseDetailProvider
     * @param GoogleTagManagerSettingsProviderInterface $settingsProvider
     */
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

    /**
     * @param Checkout $checkout
     * @param PreUpdateEventArgs $args
     */
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

    /**
     * @return bool
     */
    private function isApplicable(): bool
    {
        if (!$this->settingsProvider->getGoogleTagManagerSettings()) {
            return false;
        }

        return $this->frontendHelper->isFrontendRequest();
    }
}
