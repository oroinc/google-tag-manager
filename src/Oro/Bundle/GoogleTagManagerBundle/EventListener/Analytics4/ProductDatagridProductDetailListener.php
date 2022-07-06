<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;

/**
 * Adds to the storefront product grid the product details for Google Analytics 4 for using in GTM data layer.
 */
class ProductDatagridProductDetailListener
{
    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private ?bool $applicable = null;

    public function __construct(DataCollectionStateProviderInterface $dataCollectionStateProvider)
    {
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
    }

    public function onPreBuild(PreBuild $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $config = $event->getConfig();

        $config->offsetAddToArrayByPath(
            '[source][query][select]',
            [sprintf('text.%1$s as %1$s', WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD)]
        );

        $config->offsetAddToArrayByPath(
            '[properties]',
            [
                WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD => [
                    'type' => 'field',
                    'frontend_type' => PropertyInterface::TYPE_ROW_ARRAY,
                ],
            ]
        );
    }

    public function onResultAfter(SearchResultAfter $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        foreach ($event->getRecords() as $record) {
            $productDetail = $record->getValue(WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD);
            if ($productDetail) {
                $record->setValue(
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
