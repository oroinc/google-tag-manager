<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;

/**
 * Adds "add_to_cart" GA4 event to the GTM data layer when line item is added to RFP on a storefront.
 */
class RequestProductItemEventListener
{
    private FrontendHelper $frontendHelper;

    private DataLayerManager $dataLayerManager;

    private ProductDetailProvider $productDetailProvider;

    private ProductPriceDetailProvider $productPriceDetailProvider;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private ?ProductLineItemCartHandler $productLineItemCartHandler = null;

    private int $batchSize;

    private array $items = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductPriceDetailProvider $productPriceDetailProvider,
        DataCollectionStateProviderInterface $dataCollectionStateProvider,
        int $batchSize
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productPriceDetailProvider = $productPriceDetailProvider;
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
        $this->batchSize = $batchSize;
    }

    public function setProductLineItemCartHandler(?ProductLineItemCartHandler $productLineItemCartHandler): void
    {
        $this->productLineItemCartHandler = $productLineItemCartHandler;
    }

    public function prePersist(RequestProductItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        if ($this->productLineItemCartHandler !== null) {
            $this->productLineItemCartHandler->addToCart($item);

            return;
        }

        // BC layer below.

        $product = $item->getProduct();
        if ($product === null) {
            return;
        }

        $data = $this->productDetailProvider->getData($product);

        $unit = $item->getProductUnit();

        $data['item_variant'] = $unit->getCode();
        $data['quantity'] = $item->getQuantity();

        $price = $this->productPriceDetailProvider->getPrice($product, $unit, $item->getQuantity());

        $currency = null;
        if ($price instanceof Price) {
            $data['price'] = $price->getValue();
            $currency = $price->getCurrency();
        }

        $this->items[$currency][] = $data;
    }

    public function postFlush(): void
    {
        if ($this->productLineItemCartHandler !== null) {
            $this->productLineItemCartHandler->flush();
            return;
        }

        // BC layer below.

        foreach ($this->items as $currency => $products) {
            foreach (array_chunk($products, $this->batchSize) as $chunk) {
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

        $this->onClear();
    }

    public function onClear(): void
    {
        if ($this->productLineItemCartHandler !== null) {
            $this->productLineItemCartHandler->reset();
            return;
        }

        // BC layer below.

        $this->items = [];
    }

    private function isApplicable(): bool
    {
        return $this->frontendHelper->isFrontendRequest()
            && $this->dataCollectionStateProvider->isEnabled('google_analytics4');
    }
}
