<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;

/**
 * Handles changes of LineItem entities.
 */
class ShoppingListLineItemEventListener
{
    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /** @var ProductPriceDetailProvider */
    private $productPriceDetailProvider;

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var int */
    private $batchSize;

    /** @var array */
    private $added = [];

    /** @var array */
    private $removed = [];

    /** @var int[] */
    private $skipRemovingInShoppingListIds = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductPriceDetailProvider $productPriceDetailProvider,
        GoogleTagManagerSettingsProviderInterface $settingsProvider,
        int $batchSize = 30
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productPriceDetailProvider = $productPriceDetailProvider;
        $this->settingsProvider = $settingsProvider;
        $this->batchSize = $batchSize;
    }

    public function prePersist(LineItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), $item->getQuantity());
    }

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

        $deltaQuantity = $newQuantity - $oldQuantity;
        if (!$deltaQuantity) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), abs($deltaQuantity), $deltaQuantity > 0);
    }

    public function preRemove(LineItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        if (\in_array($item->getShoppingList()->getId(), $this->skipRemovingInShoppingListIds)) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), $item->getQuantity(), false);
    }

    public function addShoppingListIdToIgnore(CheckoutSourceEntityRemoveEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        /**
         * @var ShoppingList $checkoutSourceEntity
         */
        $checkoutSourceEntity = $event->getCheckoutSourceEntity();
        if (!$checkoutSourceEntity instanceof ShoppingList) {
            return;
        }

        $this->skipRemovingInShoppingListIds[] = $checkoutSourceEntity->getId();
    }

    public function postFlush(): void
    {
        foreach ($this->added as $currency => $added) {
            foreach (array_chunk($added, $this->batchSize) as $chunk) {
                $this->dataLayerManager->add(
                    [
                        'event' => 'addToCart',
                        'ecommerce' => [
                            'currencyCode' => $currency,
                            'add' => [
                                'products' => $chunk
                            ]
                        ]
                    ]
                );
            }
        }

        foreach ($this->removed as $currency => $removed) {
            foreach (array_chunk($removed, $this->batchSize) as $chunk) {
                $this->dataLayerManager->add(
                    [
                        'event' => 'removeFromCart',
                        'ecommerce' => [
                            'currencyCode' => $currency,
                            'remove' => [
                                'products' => $chunk
                            ]
                        ]
                    ]
                );
            }
        }

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->added = [];
        $this->removed = [];
        $this->skipRemovingInShoppingListIds = [];
    }

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
            if (empty($this->added[$currency]) || !in_array($data, $this->added[$currency], true)) {
                $this->added[$currency][] = $data;
            }
        } elseif (empty($this->removed[$currency]) || !in_array($data, $this->removed[$currency], true)) {
            $this->removed[$currency][] = $data;
        }
    }

    private function isApplicable(): bool
    {
        return $this->frontendHelper->isFrontendRequest() && $this->settingsProvider->getGoogleTagManagerSettings();
    }
}
