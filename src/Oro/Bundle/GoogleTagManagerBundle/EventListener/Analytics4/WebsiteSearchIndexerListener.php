<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteSearchBundle\Event\IndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Manager\WebsiteContextManager;

/**
 * Adds to the search index the product details for Google Analytics 4 for using in the GTM data layer.
 */
class WebsiteSearchIndexerListener
{
    public const PRODUCT_DETAIL_FIELD = 'gtm_analytics4_product_detail';

    private WebsiteContextManager $websiteContextManager;

    private ProductDetailProvider $productDetailProvider;

    public function __construct(
        WebsiteContextManager $websiteContextManager,
        ProductDetailProvider $productDetailProvider
    ) {
        $this->websiteContextManager = $websiteContextManager;
        $this->productDetailProvider = $productDetailProvider;
    }

    public function onWebsiteSearchIndex(IndexEntityEvent $event): void
    {
        $websiteId = $this->websiteContextManager->getWebsiteId($event->getContext());
        if (!$websiteId) {
            $event->stopPropagation();
            return;
        }

        /** @var Product $product */
        foreach ($event->getEntities() as $product) {
            $data = $this->productDetailProvider->getData($product);
            if ($data) {
                $event->addField(
                    $product->getId(),
                    self::PRODUCT_DETAIL_FIELD,
                    json_encode($data, JSON_THROW_ON_ERROR)
                );
            }
        }
    }
}
