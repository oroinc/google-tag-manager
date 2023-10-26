<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityClearEvent;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;

/**
 * Adds "add_to_cart", "remove_from_cart" GA4 events to the GTM data layer when shopping list line items
 * are added/removed from a shopping list on storefront.
 */
class ShoppingListLineItemEventListener
{
    private FrontendHelper $frontendHelper;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private ProductLineItemCartHandler $productLineItemCartHandler;

    /** @var int[] */
    private array $skipRemovingInShoppingListIds = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataCollectionStateProviderInterface $dataCollectionStateProvider,
        ProductLineItemCartHandler $productLineItemCartHandler
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
        $this->productLineItemCartHandler = $productLineItemCartHandler;
    }

    public function setProductLineItemCartHandler(?ProductLineItemCartHandler $productLineItemCartHandler): void
    {
        $this->productLineItemCartHandler = $productLineItemCartHandler;
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

    public function onCheckoutSourceEntityClearOrRemove(
        CheckoutSourceEntityRemoveEvent|CheckoutSourceEntityClearEvent $event
    ): void {
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
        $this->productLineItemCartHandler->flush();
        $this->onClear();
    }

    public function onClear(): void
    {
        $this->productLineItemCartHandler->reset();
        $this->skipRemovingInShoppingListIds = [];
    }

    private function storeProductData(LineItem $item, ProductUnit $unit, float $qty, bool $add = true): void
    {
        $currency = $item->getShoppingList()->getCurrency();
        if ($add) {
            $this->productLineItemCartHandler->addToCart($item, $unit, $qty, $currency);
        } else {
            $this->productLineItemCartHandler->removeFromCart($item, $unit, $qty, $currency);
        }
    }

    private function isApplicable(): bool
    {
        return $this->frontendHelper->isFrontendRequest()
            && $this->dataCollectionStateProvider->isEnabled('google_analytics4');
    }
}
