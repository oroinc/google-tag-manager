<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Handles changes of LineItem entities.
 */
class ShoppingListLineItemEventListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /** @var ProductPriceDetailProvider */
    private $productPriceDetailProvider;

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var array */
    private $added = [];

    /** @var array */
    private $removed = [];

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param DataLayerManager $dataLayerManager
     * @param ProductDetailProvider $productDetailProvider
     * @param ProductPriceDetailProvider $productPriceDetailProvider
     * @param GoogleTagManagerSettingsProviderInterface $settingsProvider
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductPriceDetailProvider $productPriceDetailProvider,
        GoogleTagManagerSettingsProviderInterface $settingsProvider
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productPriceDetailProvider = $productPriceDetailProvider;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @param LineItem $item
     */
    public function prePersist(LineItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), $item->getQuantity());
    }

    /**
     * @param LineItem $item
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(LineItem $item, PreUpdateEventArgs $args): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $oldQuantity = $item->getQuantity();
        $newQuantity = $item->getQuantity();

        if ($args->hasChangedField('quantity')) {
            $oldQuantity = $args->getOldValue('quantity');
            $newQuantity = $args->getNewValue('quantity');
        }

        if ($args->hasChangedField('unit')) {
            /** @var ProductUnit $oldUnit */
            $oldUnit = $args->getOldValue('unit');
            /** @var ProductUnit $newUnit */
            $newUnit = $args->getNewValue('unit');

            $this->storeProductData($item, $oldUnit, $oldQuantity, false);
            $this->storeProductData($item, $newUnit, $newQuantity);

            return;
        }

        $deltaQuantity = $args->getNewValue('quantity') - $args->getOldValue('quantity');
        if (!$deltaQuantity) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), abs($deltaQuantity), $deltaQuantity > 0);
    }

    /**
     * @param LineItem $item
     */
    public function preRemove(LineItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), $item->getQuantity(), false);
    }

    public function postFlush(): void
    {
        foreach ($this->added as $currency => $added) {
            $this->dataLayerManager->add(
                [
                    'event' => 'addToCart',
                    'ecommerce' => [
                        'currencyCode' => $currency,
                        'add' => [
                            'products' => $added
                        ]
                    ]
                ]
            );
        }

        foreach ($this->removed as $currency => $removed) {
            $this->dataLayerManager->add(
                [
                    'event' => 'removeFromCart',
                    'ecommerce' => [
                        'currencyCode' => $currency,
                        'remove' => [
                            'products' => $removed
                        ]
                    ]
                ]
            );
        }

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->added = [];
        $this->removed = [];
    }

    /**
     * @param LineItem $item
     * @param ProductUnit $unit
     * @param float $qty
     * @param bool $add
     */
    private function storeProductData(LineItem $item, ProductUnit $unit, float $qty, bool $add = true): void
    {
        $data = $this->productDetailProvider->getData($item->getProduct());
        $data['variant'] = $unit->getCode();
        $data['quantity'] = $qty;

        $price = $this->productPriceDetailProvider->getPrice($item->getProduct(), $unit, $item->getQuantity());
        $currency = null;

        if ($price instanceof Price) {
            $data['price'] = $price->getValue();
            $currency = $price->getCurrency();
        }

        if ($add) {
            $this->added[$currency][] = $data;
        } else {
            $this->removed[$currency][] = $data;
        }
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
