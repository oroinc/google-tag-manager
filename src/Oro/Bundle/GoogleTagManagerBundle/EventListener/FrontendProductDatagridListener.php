<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;

/**
 * Added product details to frontend product datagrind for using in GTM data layer
 */
class FrontendProductDatagridListener
{
    private const COLUMN_PRODUCT_DETAIL = 'product_detail';

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var bool */
    private $applicable;

    public function __construct(GoogleTagManagerSettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function onPreBuild(PreBuild $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $config = $event->getConfig();

        $config->offsetAddToArrayByPath('[source][query][select]', [
            sprintf(
                'text.%s as %s',
                WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD,
                self::COLUMN_PRODUCT_DETAIL
            ),
        ]);

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

        /** @var ResultRecord $record */
        foreach ($event->getRecords() as $record) {
            $productDetail = $record->getValue(self::COLUMN_PRODUCT_DETAIL);
            if ($productDetail) {
                $record->setValue(self::COLUMN_PRODUCT_DETAIL, \json_decode($productDetail, true));
            }
        }
    }

    private function isApplicable(): bool
    {
        if ($this->applicable === null) {
            $this->applicable = $this->settingsProvider->getGoogleTagManagerSettings() !== null;
        }

        return $this->applicable;
    }
}
