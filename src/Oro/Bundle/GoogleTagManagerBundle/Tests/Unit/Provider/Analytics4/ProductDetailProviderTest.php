<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Analytics4;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Entity\CategoryTitle;
use Oro\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Oro\Bundle\CatalogBundle\Tests\Unit\Entity\Stub\Category as CategoryStub;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\LocaleBundle\Entity\AbstractLocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Tests\Unit\Stub\LocalizationStub;
use Oro\Bundle\ProductBundle\Entity\ProductName;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Brand as BrandStub;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Product as ProductStub;

class ProductDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    private ProductDetailProvider $provider;

    private \PHPUnit\Framework\MockObject\MockObject|CategoryRepository $categoryRepository;

    private ?Localization $localization = null;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects(self::any())
            ->method('getRepository')
            ->with(Category::class)
            ->willReturn($this->categoryRepository);

        $this->provider = new ProductDetailProvider($managerRegistry);
    }

    /**
     * @dataProvider getDataDataProvider
     */
    public function testGetData(ProductStub $product, array $categoriesPath, array $expected): void
    {
        $this->categoryRepository->expects(self::any())
            ->method('getPath')
            ->with(self::isInstanceOf(Category::class))
            ->willReturn($categoriesPath);

        self::assertSame($expected, $this->provider->getData($product));
    }

    public function getDataDataProvider(): array
    {
        $product = $this->getProduct('SKU-1', 'Product name');
        $product->setBrand($this->getBrand('ACME Brand'))
            ->setCategory($this->createMock(Category::class));

        return [
            'product without required name' => [
                'product' => $this->getProduct(null, 'Product name'),
                'categoriesPath' => [],
                'expected' => [],
            ],
            'product without required sku' => [
                'product' => $this->getProduct('SKU-1', null),
                'categoriesPath' => [],
                'expected' => [],
            ],
            'product with required data' => [
                'product' => $this->getProduct('SKU-1', 'Product name'),
                'categoriesPath' => [],
                'expected' => [
                    'item_id' => 'SKU-1',
                    'item_name' => 'Product name',
                ],
            ],
            'product with all data' => [
                'product' => $product,
                'categoriesPath' => [$this->getCategory('Root Category'), $this->getCategory('Category 2')],
                'expected' => [
                    'item_id' => 'SKU-1',
                    'item_name' => 'Product name',
                    'item_brand' => 'ACME Brand',
                    'item_category' => 'Category 2',
                ],
            ],
            'product with a single category' => [
                'product' => $product,
                'categoriesPath' => [$this->getCategory('Root Category')],
                'expected' => [
                    'item_id' => 'SKU-1',
                    'item_name' => 'Product name',
                    'item_brand' => 'ACME Brand',
                ],
            ],
        ];
    }

    public function testGetDataWhenMoreThan6Categories(): void
    {
        $category7 = $this->getCategory('Category 7');
        $product = $this->getProduct('SKU-1', 'Product name');
        $product->setBrand($this->getBrand('ACME Brand'))
            ->setCategory($category7);

        $this->categoryRepository->expects(self::any())
            ->method('getPath')
            ->with($category7)
            ->willReturn(
                [
                    $this->getCategory('Root Category'),
                    $this->getCategory('Category 2'),
                    $this->getCategory('Category 3'),
                    $this->getCategory('Category 4'),
                    $this->getCategory('Category 5'),
                    $this->getCategory('Category 6'),
                    $category7,
                ]
            );

        self::assertSame([
            'item_id' => 'SKU-1',
            'item_name' => 'Product name',
            'item_brand' => 'ACME Brand',
            'item_category' => 'Category 2',
            'item_category2' => 'Category 3',
            'item_category3' => 'Category 4',
            'item_category4' => 'Category 5',
            'item_category5' => 'Category 6 / Category 7',
        ], $this->provider->getData($product));
    }

    /**
     * @dataProvider getDataForLocalizationProvider
     */
    public function testGetDataForLocalization(ProductStub $product, array $categoriesPath, array $expected): void
    {
        $this->categoryRepository->expects(self::any())
            ->method('getPath')
            ->with(self::isInstanceOf(Category::class))
            ->willReturn($categoriesPath);

        self::assertSame($expected, $this->provider->getData($product, $this->getLocalization()));
    }

    public function getDataForLocalizationProvider(): array
    {
        $product = $this->getProduct('SKU-1', 'Product name', true);
        $product->setBrand($this->getBrand('ACME Brand', true))
            ->setCategory($this->createMock(Category::class));

        return [
            'product without required name' => [
                'product' => $this->getProduct(null, 'Product name', true),
                'categoriesPath' => [],
                'expected' => [],
            ],
            'product without required sku' => [
                'product' => $this->getProduct('SKU-1', null, true),
                'categoriesPath' => [],
                'expected' => [],
            ],
            'product with required data' => [
                'product' => $this->getProduct('SKU-1', 'Product name', true),
                'categoriesPath' => [],
                'expected' => [
                    'item_id' => 'SKU-1',
                    'item_name' => 'Product name',
                ],
            ],
            'product with all data' => [
                'product' => $product,
                'categoriesPath' => [$this->getCategory('Root Category', true), $this->getCategory('Category 2', true)],
                'expected' => [
                    'item_id' => 'SKU-1',
                    'item_name' => 'Product name',
                    'item_brand' => 'ACME Brand',
                    'item_category' => 'Category 2',
                ],
            ],
            'product with a single category' => [
                'product' => $product,
                'categoriesPath' => [$this->getCategory('Root Category', true)],
                'expected' => [
                    'item_id' => 'SKU-1',
                    'item_name' => 'Product name',
                    'item_brand' => 'ACME Brand',
                ],
            ],
        ];
    }

    public function testGetDataForLocalizationWhenMoreThan6Categories(): void
    {
        $category7 = $this->getCategory('Category 7', true);
        $product = $this->getProduct('SKU-1', 'Product name');
        $product->setBrand($this->getBrand('ACME Brand'))
            ->setCategory($category7);

        $this->categoryRepository->expects(self::any())
            ->method('getPath')
            ->with($category7)
            ->willReturn(
                [
                    $this->getCategory('Root Category', true),
                    $this->getCategory('Category 2', true),
                    $this->getCategory('Category 3', true),
                    $this->getCategory('Category 4', true),
                    $this->getCategory('Category 5', true),
                    $this->getCategory('Category 6', true),
                    $category7,
                ]
            );

        self::assertSame([
            'item_id' => 'SKU-1',
            'item_name' => 'Product name',
            'item_brand' => 'ACME Brand',
            'item_category' => 'Category 2',
            'item_category2' => 'Category 3',
            'item_category3' => 'Category 4',
            'item_category4' => 'Category 5',
            'item_category5' => 'Category 6 / Category 7',
        ], $this->provider->getData($product, $this->getLocalization()));
    }

    private function createLocalizedFallbackValue(
        string $translate,
        ?Localization $localization,
        string $className = LocalizedFallbackValue::class
    ): AbstractLocalizedFallbackValue {
        $fallbackValue = new $className();
        $fallbackValue->setString($translate);
        $fallbackValue->setLocalization($localization);

        return $fallbackValue;
    }

    private function getLocalization(): Localization
    {
        if (!$this->localization) {
            $this->localization = new LocalizationStub(42);
        }

        return $this->localization;
    }

    private function getProduct(?string $sku, ?string $name, bool $isLocalized = false): ProductStub
    {
        $product = (new ProductStub())
            ->setSku($sku);

        if ($name) {
            $product->addName(
                $this->createLocalizedFallbackValue(
                    $name,
                    $isLocalized ? $this->getLocalization() : null,
                    ProductName::class
                )
            );
        }

        return $product;
    }

    private function getBrand(string $name, bool $isLocalized = false): BrandStub
    {
        return (new BrandStub())
            ->addName(
                $this->createLocalizedFallbackValue($name, $isLocalized ? $this->getLocalization() : null)
            );
    }

    private function getCategory(string $title, bool $isLocalized = false): CategoryStub
    {
        return (new CategoryStub())
            ->addTitle(
                $this->createLocalizedFallbackValue(
                    $title,
                    $isLocalized ? $this->getLocalization() : null,
                    CategoryTitle::class
                )
            );
    }
}
