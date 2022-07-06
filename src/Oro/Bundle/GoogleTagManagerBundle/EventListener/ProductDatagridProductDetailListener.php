<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;

/**
 * Adds product details for using in GTM data layer to storefront product grid.
 *
 * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
 */
class ProductDatagridProductDetailListener
{
    private const COLUMN_PRODUCT_DETAIL = 'product_detail';

    private GoogleTagManagerSettingsProviderInterface $settingsProvider;

    private ?DataCollectionStateProviderInterface $dataCollectionStateProvider = null;

    private ?bool $applicable = null;

    public function __construct(GoogleTagManagerSettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function setDataCollectionStateProvider(
        ?DataCollectionStateProviderInterface $dataCollectionStateProvider
    ): void {
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
            ['text.product_detail as product_detail']
        );

        $config->offsetAddToArrayByPath(
            '[properties]',
            [
                self::COLUMN_PRODUCT_DETAIL => [
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
            $productDetail = $record->getValue('product_detail');
            if ($productDetail) {
                $record->setValue(
                    self::COLUMN_PRODUCT_DETAIL,
                    json_decode($productDetail, true, 512, JSON_THROW_ON_ERROR)
                );
            }
        }
    }

    private function isApplicable(): bool
    {
        if (null === $this->applicable) {
            if ($this->dataCollectionStateProvider) {
                $this->applicable = $this->dataCollectionStateProvider->isEnabled('universal_analytics');
            } else {
                $this->applicable = (bool) $this->settingsProvider->getGoogleTagManagerSettings();
            }
        }

        return $this->applicable;
    }
}
