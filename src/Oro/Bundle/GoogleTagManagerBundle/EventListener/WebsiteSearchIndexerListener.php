<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteSearchBundle\Event\IndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Manager\WebsiteContextManager;

/**
 * Add product details to search index for using in frontend product datagrid
 */
class WebsiteSearchIndexerListener
{
    public const PRODUCT_DETAIL_FIELD = 'product_detail';

    /** @var WebsiteContextManager */
    private $websiteContextManager;

    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /**
     * @param WebsiteContextManager $websiteContextManager
     * @param ProductDetailProvider $productDetailProvider
     */
    public function __construct(
        WebsiteContextManager $websiteContextManager,
        ProductDetailProvider $productDetailProvider
    ) {
        $this->websiteContextManager = $websiteContextManager;
        $this->productDetailProvider = $productDetailProvider;
    }

    /**
     * @param IndexEntityEvent $event
     */
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
                    static::PRODUCT_DETAIL_FIELD,
                    \json_encode($data)
                );
            }
        }
    }
}
