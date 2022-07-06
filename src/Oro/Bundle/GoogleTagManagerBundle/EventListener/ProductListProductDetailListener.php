<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\ProductBundle\Event\BuildQueryProductListEvent;
use Oro\Bundle\ProductBundle\Event\BuildResultProductListEvent;

/**
 * Adds product details for using in GTM data layer to storefront product lists.
 *
 * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
 */
class ProductListProductDetailListener
{
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

    public function onBuildQuery(BuildQueryProductListEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $event->getQuery()
            ->addSelect('text.product_detail');
    }

    public function onBuildResult(BuildResultProductListEvent $event): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        foreach ($event->getProductData() as $productId => $data) {
            $productDetail = $data['product_detail'];
            if ($productDetail) {
                $productView = $event->getProductView($productId);
                $productView->set('product_detail', json_decode($productDetail, true, 512, JSON_THROW_ON_ERROR));
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
