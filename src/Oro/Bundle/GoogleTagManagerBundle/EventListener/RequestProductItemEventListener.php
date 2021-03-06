<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;

/**
 * Handles changes of RequestProductItem entities.
 */
class RequestProductItemEventListener
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
    private $items = [];

    public function __construct(
        FrontendHelper $frontendHelper,
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductPriceDetailProvider $productPriceDetailProvider,
        GoogleTagManagerSettingsProviderInterface $settingsProvider,
        int $batchSize
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productPriceDetailProvider = $productPriceDetailProvider;
        $this->settingsProvider = $settingsProvider;
        $this->batchSize = $batchSize;
    }

    /**
     * @param RequestProductItem $item
     */
    public function prePersist(RequestProductItem $item = null): void
    {
        if (!$this->isApplicable()) {
            return;
        }
        if ($item === null) {
            return;
        }
        $product = $item->getProduct();
        if ($product === null) {
            return;
        }

        $data = $this->productDetailProvider->getData($product);

        $unit = $item->getProductUnit();

        $data['variant'] = $unit->getCode();
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
        foreach ($this->items as $currency => $products) {
            foreach (array_chunk($products, $this->batchSize) as $chunk) {
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

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->items = [];
    }

    private function isApplicable(): bool
    {
        if (!$this->settingsProvider->getGoogleTagManagerSettings()) {
            return false;
        }

        return $this->frontendHelper->isFrontendRequest();
    }
}
