<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityClearEvent;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Adds "add_to_cart", "remove_from_cart" GA4 events to the GTM data layer when shopping list line items
 * are added/removed from a shopping list on storefront.
 */
class ShoppingListLineItemEventListener
{
    private FrontendHelper $frontendHelper;

    private DataLayerManager $dataLayerManager;

    private ProductDetailProvider $productDetailProvider;

    private ProductPriceDetailProvider $productPriceDetailProvider;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private int $batchSize;

    private array $added = [];

    private array $removed = [];

    /** @var int[] */
    private array $skipRemovingInShoppingListIds = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductPriceDetailProvider $productPriceDetailProvider,
        DataCollectionStateProviderInterface $dataCollectionStateProvider,
        int $batchSize = 30
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productPriceDetailProvider = $productPriceDetailProvider;
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
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

        if (\in_array($item->getShoppingList()->getId(), $this->skipRemovingInShoppingListIds, true)) {
            return;
        }

        $this->storeProductData($item, $item->getProductUnit(), $item->getQuantity(), false);
    }

    /**
     * @param CheckoutSourceEntityRemoveEvent|CheckoutSourceEntityClearEvent $event
     */
    public function onCheckoutSourceEntityClearOrRemove(Event $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $checkoutSourceEntity = $event->getCheckoutSourceEntity();
        if ($checkoutSourceEntity instanceof ShoppingList) {
            $this->skipRemovingInShoppingListIds[] = $checkoutSourceEntity->getId();
        }
    }

    public function postFlush(): void
    {
        foreach ($this->added as $currency => $added) {
            foreach (array_chunk($added, $this->batchSize) as $chunk) {
                $this->dataLayerManager->append(
                    [
                        'event' => 'add_to_cart',
                        'ecommerce' => [
                            'currency' => $currency,
                            'items' => $chunk,
                        ],
                    ]
                );
            }
        }

        foreach ($this->removed as $currency => $removed) {
            foreach (array_chunk($removed, $this->batchSize) as $chunk) {
                $this->dataLayerManager->append(
                    [
                        'event' => 'remove_from_cart',
                        'ecommerce' => [
                            'currency' => $currency,
                            'items' => $chunk,
                        ],
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
        $data['item_variant'] = $unit->getCode();
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
        return $this->frontendHelper->isFrontendRequest()
            && $this->dataCollectionStateProvider->isEnabled('google_analytics4');
    }
}
