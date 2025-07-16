<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CatalogBundle\Layout\DataProvider\CategoryBreadcrumbProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CatalogDetailCollector;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\WebCatalogBundle\Layout\DataProvider\WebCatalogBreadcrumbProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogDetailCollectorTest extends TestCase
{
    private const PRODUCT_LIST_ROUTE = 'oro_product_frontend_product_index';

    private ConfigManager&MockObject $configManager;
    private ParameterBag&MockObject $requestQuery;
    private ParameterBag&MockObject $requestAttributes;
    private WebCatalogBreadcrumbProvider&MockObject $webCatalogBreadcrumbProvider;
    private CategoryBreadcrumbProvider&MockObject $categoryBreadcrumbProvider;
    private CatalogDetailCollector $collector;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->requestQuery = $this->createMock(ParameterBag::class);
        $this->requestAttributes = $this->createMock(ParameterBag::class);

        $request = new Request();
        $request->query = $this->requestQuery;
        $request->attributes = $this->requestAttributes;

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->webCatalogBreadcrumbProvider = $this->createMock(WebCatalogBreadcrumbProvider::class);
        $this->categoryBreadcrumbProvider = $this->createMock(CategoryBreadcrumbProvider::class);

        $this->collector = new CatalogDetailCollector(
            $this->configManager,
            $requestStack,
            $this->webCatalogBreadcrumbProvider,
            $this->categoryBreadcrumbProvider
        );
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testWitEnabledWebCatalog(
        ?array $webCatalogItems,
        ?int $requestCategoryId,
        ?string $requestRoute,
        ?array $categoryItems,
        array $excepted
    ): void {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_web_catalog.web_catalog')
            ->willReturn($webCatalogItems);
        if ($webCatalogItems !== null) {
            $this->webCatalogBreadcrumbProvider->expects($this->once())
                ->method('getItems')
                ->willReturn($webCatalogItems);
        }

        if ($requestCategoryId !== null) {
            $this->requestQuery->expects($this->once())
                ->method('get')
                ->with('categoryId')
                ->willReturn($requestCategoryId);
        }

        if ($requestRoute !== null) {
            $this->requestAttributes->expects($this->once())
                ->method('get')
                ->with('_route')
                ->willReturn($requestRoute);
        }

        if ($categoryItems !== null) {
            $this->categoryBreadcrumbProvider->expects($this->once())
                ->method('getItems')
                ->willReturn($categoryItems);
        }

        $data = new ArrayCollection();
        $this->collector->handle($data);
        $this->assertSame($excepted, $data->toArray());
    }

    public function handleDataProvider(): array
    {
        return [
            'empty path' => [
                'webCatalogItems' => [],
                'requestCategoryId' => null,
                'requestRoute' => self::PRODUCT_LIST_ROUTE,
                'categoryItems' => [],
                'excepted' => [],
            ],
            'web catalog' => [
                'webCatalogItems' => [
                    ['label' => 'Single Node']
                ],
                'requestCategoryId' => null,
                'requestRoute' => null,
                'categoryItems' => null,
                'excepted' => [[DataLayerAttributeBag::KEY_CATALOG_PATH => 'Single Node']],
            ],
            'empty catalog for product index page' => [
                'webCatalogItems' => [],
                'requestCategoryId' => null,
                'requestRoute' => self::PRODUCT_LIST_ROUTE,
                'categoryItems' => [
                    ['label' => 'Single Node']
                ],
                'excepted' => [[DataLayerAttributeBag::KEY_CATALOG_PATH => 'Single Node']],
            ],
            'product index page' => [
                'webCatalogItems' => null,
                'requestCategoryId' => null,
                'requestRoute' => self::PRODUCT_LIST_ROUTE,
                'categoryItems' => [
                    ['label' => 'Single Node']
                ],
                'excepted' => [[DataLayerAttributeBag::KEY_CATALOG_PATH => 'Single Node']],
            ],
            'product category page' => [
                'webCatalogItems' => null,
                'requestCategoryId' => 1,
                'requestRoute' => 'another_route',
                'categoryItems' => [
                    ['label' => 'Single Node']
                ],
                'excepted' => [[DataLayerAttributeBag::KEY_CATALOG_PATH => 'Single Node']],
            ],
            'multiple nodes' => [
                'webCatalogItems' => [
                    ['label' => 'First Node'],
                    ['label' => 'Second Node'],
                    ['label' => 'Third Node'],
                ],
                'requestCategoryId' => null,
                'requestRoute' => null,
                'categoryItems' => null,
                'excepted' => [[DataLayerAttributeBag::KEY_CATALOG_PATH => 'First Node / Second Node / Third Node']],
            ],
        ];
    }
}
