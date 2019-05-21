<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\WebCatalogBundle\Layout\DataProvider\WebCatalogBreadcrumbProvider;

/**
 * Adds current patch of web catalog for data layer
 */
class CatalogDetailCollector implements CollectorInterface
{
    /** @var WebCatalogBreadcrumbProvider */
    private $breadcrumbProvider;

    /**
     * @param WebCatalogBreadcrumbProvider $breadcrumbProvider
     */
    public function __construct(WebCatalogBreadcrumbProvider $breadcrumbProvider)
    {
        $this->breadcrumbProvider = $breadcrumbProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Collection $data): void
    {
        $items = $this->breadcrumbProvider->getItems();
        if ($items) {
            $data->add([
                DataLayerAttributeBag::KEY_CATALOG_PATH => implode(' / ', array_column($items, 'label'))
            ]);
        }
    }
}
