<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4;

use Brick\Math\BigDecimal;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\PricingBundle\Provider\ProductLineItemPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Model\ProductLineItemInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Adds "add_to_cart", "remove_from_cart" GA4 events data to the GTM data layer.
 */
class ProductLineItemCartHandler implements ResetInterface
{
    private DataLayerManager $dataLayerManager;
    private ProductDetailProvider $productDetailProvider;
    private ProductLineItemPriceProviderInterface $productLineItemPriceProvider;
    private UserCurrencyManager $userCurrencyManager;
    private int $batchSize = 30;
    /**
     * @var array<string,array{
     *     event: string,
     *     key: string,
     *     lineItem: ProductLineItemInterface,
     *     productUnit: ProductUnit,
     *     quantity: float|int}>
     */
    private array $lineItemsDataByCurrency = [];

    public function __construct(
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductLineItemPriceProviderInterface $productLineItemPriceProvider,
        UserCurrencyManager $userCurrencyManager
    ) {
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productLineItemPriceProvider = $productLineItemPriceProvider;
        $this->userCurrencyManager = $userCurrencyManager;
    }

    #[\Override]
    public function reset(): void
    {
        $this->lineItemsDataByCurrency = [];
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    public function addToCart(
        ProductLineItemInterface $lineItem,
        ProductUnit $productUnit = null,
        float|int|null $quantity = null,
        ?string $currency = null
    ): self {
        $this->storeLineItemData('add_to_cart', $lineItem, $productUnit, $quantity, $currency);

        return $this;
    }

    public function removeFromCart(
        ProductLineItemInterface $lineItem,
        ?ProductUnit $productUnit = null,
        float|int|null $quantity = null,
        ?string $currency = null
    ): self {
        $this->storeLineItemData('remove_from_cart', $lineItem, $productUnit, $quantity, $currency);

        return $this;
    }

    private function storeLineItemData(
        string $eventName,
        ProductLineItemInterface $lineItem,
        ?ProductUnit $productUnit,
        float|int|null $quantity,
        ?string $currency
    ): void {
        if ($lineItem->getProduct() === null) {
            return;
        }

        $quantity = $quantity ?? $lineItem->getQuantity();
        if ($quantity === null) {
            return;
        }

        $productUnit = $productUnit ?? $lineItem->getProductUnit();
        if ($productUnit === null) {
            return;
        }

        if ($currency === null) {
            $currency = $this->userCurrencyManager->getUserCurrency() ?:
                $this->userCurrencyManager->getDefaultCurrency();
        }

        // Clones the original line item in order to safely change the unit and quantity without any side effects.
        $productLineItem = clone $lineItem;

        if (method_exists($productLineItem, 'setQuantity')) {
            $productLineItem->setQuantity($quantity);
        }

        if (method_exists($productLineItem, 'setProductUnit')) {
            $productLineItem->setProductUnit($productUnit);
        }

        $data = [
            'event' => $eventName,
            'key' => spl_object_hash($productLineItem),
            'lineItem' => $productLineItem,
            'productUnit' => $productUnit,
            'quantity' => $quantity,
        ];

        $this->lineItemsDataByCurrency[$currency][] = $data;
    }

    /**
     * Flushes the collected events data to the GTM data layer.
     */
    public function flush(): void
    {
        foreach ($this->lineItemsDataByCurrency as $currency => $lineItemsData) {
            $itemsDataByEventName = $this->getGtmItemsData($lineItemsData, $currency);

            foreach ($itemsDataByEventName as $eventName => $itemsData) {
                foreach (array_chunk($itemsData['items'], $this->batchSize) as $n => $chunk) {
                    $payload = [
                        'event' => $eventName,
                        'ecommerce' => [
                            'items' => $chunk,
                        ],
                    ];

                    if ($n === 0) {
                        // First chunk must contain the most complete event data.
                        $payload['ecommerce']['value'] = (float)$itemsData['subtotal']?->toFloat();
                        $payload['ecommerce']['currency'] = $currency;
                    }

                    $this->dataLayerManager->append($payload);
                }
            }
        }

        $this->reset();
    }

    /**
     * @param array<array{
     *     event: string,
     *     key: string,
     *     lineItem: ProductLineItemInterface,
     *     productUnit: ProductUnit,
     *     quantity: float|int}> $lineItemsData
     * @param string $currency
     *
     * @return array{string,array{items: array, subtotal: BigDecimal}} arrays of GTM items and subtotals,
     *  keyed by GTM event name.
     */
    private function getGtmItemsData(array $lineItemsData, string $currency): array
    {
        $lineItems = array_column($lineItemsData, 'lineItem', 'key');
        $productLineItemsPrices = $this->productLineItemPriceProvider
            ->getProductLineItemsPrices($lineItems, null, $currency);
        $itemsDataByEventName = [];

        foreach ($lineItemsData as $lineItemDatum) {
            $eventName = $lineItemDatum['event'];

            $itemData = $this->productDetailProvider->getData($lineItemDatum['lineItem']->getProduct());
            $itemData['item_variant'] = $lineItemDatum['productUnit']->getCode();
            $itemData['quantity'] = $lineItemDatum['quantity'];

            $productLineItemPrice = $productLineItemsPrices[$lineItemDatum['key']] ?? null;

            if (!isset($itemsDataByEventName[$eventName]['subtotal'])) {
                $itemsDataByEventName[$eventName]['subtotal'] = BigDecimal::of(0.0);
            }

            if ($productLineItemPrice !== null) {
                $itemData['price'] = $productLineItemPrice->getPrice()->getValue();
                $itemsDataByEventName[$eventName]['subtotal'] = $itemsDataByEventName[$eventName]['subtotal']
                    ->plus($productLineItemPrice->getSubtotal());
            }

            $itemsDataByEventName[$eventName]['items'][] = $itemData;
        }

        return $itemsDataByEventName;
    }
}
