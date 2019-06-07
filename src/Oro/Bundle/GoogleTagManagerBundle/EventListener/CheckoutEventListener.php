<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Handles completed Checkout to generate purchase data.
 */
class CheckoutEventListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var PurchaseDetailProvider */
    private $purchaseDetailProvider;

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var array */
    private $data = [];

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param DataLayerManager $dataLayerManager
     * @param PurchaseDetailProvider $purchaseDetailProvider
     * @param GoogleTagManagerSettingsProviderInterface $settingsProvider
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        DataLayerManager $dataLayerManager,
        PurchaseDetailProvider $purchaseDetailProvider,
        GoogleTagManagerSettingsProviderInterface $settingsProvider
    ) {
        $this->tokenStorage = $tokenStorage;
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

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        return $token instanceof AnonymousCustomerUserToken || $token->getUser() instanceof CustomerUser;
    }
}
