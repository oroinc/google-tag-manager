<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteSearchBundle\Event\IndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Manager\WebsiteContextManager;

/**
 * Adds product details for using in GTM data layer to search index.
 */
class WebsiteSearchIndexerListener
{
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
                $event->addField($product->getId(), 'product_detail', json_encode($data, JSON_THROW_ON_ERROR));
            }
        }
    }
}
