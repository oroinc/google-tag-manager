<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\ProductBundle\Event\BuildQueryProductListEvent;
use Oro\Bundle\ProductBundle\Event\BuildResultProductListEvent;

/**
 * Adds to the storefront product lists the product details for Google Analytics 4 for using in GTM data layer.
 */
class ProductListProductDetailListener
{
    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private ?bool $applicable = null;

    public function __construct(DataCollectionStateProviderInterface $dataCollectionStateProvider)
    {
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
    }

    public function onBuildQuery(BuildQueryProductListEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $event->getQuery()
            ->addSelect('text.' . WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD);
    }

    public function onBuildResult(BuildResultProductListEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        foreach ($event->getProductData() as $productId => $data) {
            $productDetail = $data[WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD];
            if ($productDetail) {
                $productView = $event->getProductView($productId);
                $productView->set(
                    WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD,
                    json_decode($productDetail, true, 512, JSON_THROW_ON_ERROR)
                );
            }
        }
    }

    private function isApplicable(): bool
    {
        if (null === $this->applicable) {
            $this->applicable = $this->dataCollectionStateProvider->isEnabled('google_analytics4');
        }

        return $this->applicable;
    }
}
