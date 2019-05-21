<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteBundle\Provider\AbstractWebsiteLocalizationProvider;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteLocalizationProvider;
use Oro\Bundle\WebsiteSearchBundle\Event\IndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Manager\WebsiteContextManager;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\LocalizationIdPlaceholder;

/**
 * Add product details to search index for using in frontend product datagrid
 */
class WebsiteSearchIndexerListener
{
    public const PRODUCT_DETAIL_L10N_FIELD = 'product_detail_' . LocalizationIdPlaceholder::NAME;

    /** @var WebsiteLocalizationProvider */
    private $websiteLocalizationProvider;

    /** @var WebsiteContextManager */
    private $websiteContextManager;

    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /**
     * @param AbstractWebsiteLocalizationProvider $websiteLocalizationProvider
     * @param WebsiteContextManager $websiteContextManager
     * @param ProductDetailProvider $productDetailProvider
     */
    public function __construct(
        AbstractWebsiteLocalizationProvider $websiteLocalizationProvider,
        WebsiteContextManager $websiteContextManager,
        ProductDetailProvider $productDetailProvider
    ) {
        $this->websiteLocalizationProvider = $websiteLocalizationProvider;
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

        $localizations = $this->websiteLocalizationProvider->getLocalizationsByWebsiteId($websiteId);

        /** @var Product $product */
        foreach ($event->getEntities() as $product) {
            foreach ($localizations as $localization) {
                $data = $this->productDetailProvider->getData($product, $localization);
                if ($data) {
                    $event->addPlaceholderField(
                        $product->getId(),
                        static::PRODUCT_DETAIL_L10N_FIELD,
                        \json_encode($data),
                        [LocalizationIdPlaceholder::NAME => $localization->getId()],
                        false
                    );
                }
            }
        }
    }
}
