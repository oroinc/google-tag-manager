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
use Oro\Component\Checkout\Entity\CheckoutSourceEntityInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Adds "add_to_cart", "remove_from_cart" GA4 events to the GTM data layer when shopping list line items
 * are added/removed from a shopping list on storefront.
 */
class ShoppingListLineItemEventListener implements ServiceSubscriberInterface
{
    private FrontendHelper $frontendHelper;
    private ContainerInterface $container;
    private ?ProductLineItemCartHandler $productLineItemCartHandler = null;
    /** @var int[] */
    private array $skipRemovingInShoppingListIds = [];

    public function __construct(FrontendHelper $frontendHelper, ContainerInterface $container)
    {
        $this->frontendHelper = $frontendHelper;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            DataCollectionStateProviderInterface::class,
            ProductLineItemCartHandler::class
        ];
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

    public function onCheckoutSourceEntityBeforeRemove(CheckoutSourceEntityRemoveEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->handleCheckoutSourceEntity($event->getCheckoutSourceEntity());
    }

    public function onCheckoutSourceEntityClear(CheckoutSourceEntityClearEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->handleCheckoutSourceEntity($event->getCheckoutSourceEntity());
    }

    public function postFlush(): void
    {
        $this->getProductLineItemCartHandler()->flush();
        $this->onClear();
    }

    public function onClear(): void
    {
        $this->getProductLineItemCartHandler()->reset();
        $this->skipRemovingInShoppingListIds = [];
    }

    private function storeProductData(LineItem $item, ProductUnit $unit, float $qty, bool $add = true): void
    {
        $currency = $item->getShoppingList()->getCurrency();
        if ($add) {
            $this->getProductLineItemCartHandler()->addToCart($item, $unit, $qty, $currency);
        } else {
            $this->getProductLineItemCartHandler()->removeFromCart($item, $unit, $qty, $currency);
        }
    }

    private function handleCheckoutSourceEntity(CheckoutSourceEntityInterface $checkoutSourceEntity): void
    {
        if ($checkoutSourceEntity instanceof ShoppingList) {
            $this->skipRemovingInShoppingListIds[] = $checkoutSourceEntity->getId();
        }
    }

    private function isApplicable(): bool
    {
        return
            $this->frontendHelper->isFrontendRequest()
            && $this->getDataCollectionStateProvider()->isEnabled('google_analytics4');
    }

    private function getDataCollectionStateProvider(): DataCollectionStateProviderInterface
    {
        return $this->container->get(DataCollectionStateProviderInterface::class);
    }

    private function getProductLineItemCartHandler(): ProductLineItemCartHandler
    {
        // need to store this service in a property because this service is not shared,
        // so, each call of the container::get() created a new instance of it
        if (null === $this->productLineItemCartHandler) {
            $this->productLineItemCartHandler = $this->container->get(ProductLineItemCartHandler::class);
        }

        return $this->productLineItemCartHandler;
    }
}
