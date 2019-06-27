<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CatalogBundle\Layout\DataProvider\CategoryBreadcrumbProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\WebCatalogBundle\Layout\DataProvider\WebCatalogBreadcrumbProvider;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds current patch of web catalog for data layer
 */
class CatalogDetailCollector implements CollectorInterface
{
    /** @var ConfigManager */
    private $configManager;

    /** @var RequestStack */
    private $requestStack;

    /** @var WebCatalogBreadcrumbProvider */
    private $webCatalogBreadcrumbProvider;

    /** @var CategoryBreadcrumbProvider */
    private $categoryBreadcrumbProvider;

    /**
     * @param ConfigManager $configManager
     * @param RequestStack $requestStack
     * @param WebCatalogBreadcrumbProvider $webCatalogBreadcrumbProvider
     * @param CategoryBreadcrumbProvider $categoryBreadcrumbProvider
     */
    public function __construct(
        ConfigManager $configManager,
        RequestStack $requestStack,
        WebCatalogBreadcrumbProvider $webCatalogBreadcrumbProvider,
        CategoryBreadcrumbProvider $categoryBreadcrumbProvider
    ) {
        $this->configManager = $configManager;
        $this->requestStack = $requestStack;
        $this->webCatalogBreadcrumbProvider = $webCatalogBreadcrumbProvider;
        $this->categoryBreadcrumbProvider = $categoryBreadcrumbProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Collection $data): void
    {
        $items = $this->getWebCatalogBreadcrumbs() ?: $this->getCategoryBreadcrumbs();

        if ($items) {
            $data->add([
                DataLayerAttributeBag::KEY_CATALOG_PATH => implode(' / ', array_column($items, 'label'))
            ]);
        }
    }

    /**
     * @return array|null
     */
    private function getWebCatalogBreadcrumbs(): ?array
    {
        return $this->configManager->get('oro_web_catalog.web_catalog') !== null
            ? $this->webCatalogBreadcrumbProvider->getItems()
            : null;
    }

    /**
     * @return array|null
     */
    private function getCategoryBreadcrumbs(): ?array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $currentRoute = $request->attributes->get('_route');
        return ($currentRoute === 'oro_product_frontend_product_index' || $request->query->get('categoryId'))
            ? $this->categoryBreadcrumbProvider->getItems()
            : null;
    }
}
