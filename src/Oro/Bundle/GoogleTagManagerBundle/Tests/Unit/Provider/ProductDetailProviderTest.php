<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Oro\Bundle\CatalogBundle\Tests\Unit\Entity\Stub\Category as CategoryStub;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Brand as BrandStub;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Product as ProductStub;
use Oro\Component\Testing\Unit\EntityTrait;

class ProductDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var ProductDetailProvider */
    private $provider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CategoryRepository */
    private $categoryRepository;

    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var Localization */
    private $localization;

    public function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->categoryRepository->expects($this->any())
            ->method('getPath')
            ->with($this->isInstanceOf(Category::class))
            ->willReturn([$this->getCategory('Category 1'), $this->getCategory('Category 2')]);

        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->with(Category::class)
            ->willReturn($this->categoryRepository);

        $this->provider = new ProductDetailProvider($this->doctrineHelper);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param ProductStub $product
     * @param array $excepted
     */
    public function testGetData(ProductStub $product, array $excepted): void
    {
        $this->assertSame($excepted, $this->provider->getData($product));
    }

    /**
     * @return array
     */
    public function getDataDataProvider(): array
    {
        $product = $this->getProduct('SKU-1', 'Product name');
        $product->setBrand($this->getBrand('ACME Brand'))
            ->setCategory($this->getCategory('Category 2'));

        return [
            'product without required name' => [
                'product' => $this->getProduct(null, 'Product name'),
                'excepted' =>  [],
            ],
            'product without required sku' => [
                'product' => $this->getProduct('SKU-1', null),
                'excepted' =>  [],
            ],
            'product with required data' => [
                'product' => $this->getProduct('SKU-1', 'Product name'),
                'excepted' =>  [
                    'id' => 'SKU-1',
                    'name' => 'Product name',
                ],
            ],
            'product with all data' => [
                'product' => $product,
                'excepted' =>  [
                    'id' => 'SKU-1',
                    'name' => 'Product name',
                    'brand' => 'ACME Brand',
                    'category' => 'Category 1 / Category 2',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataForLocalizationProvider
     *
     * @param ProductStub $product
     * @param array $excepted
     */
    public function testGetDataForLocalization(ProductStub $product, array $excepted): void
    {
        $this->assertSame($excepted, $this->provider->getData($product, $this->getLocalization()));
    }

    /**
     * @return array
     */
    public function getDataForLocalizationProvider(): array
    {
        $product = $this->getProduct('SKU-1', 'Product name', true);
        $product->setBrand($this->getBrand('ACME Brand', true))
            ->setCategory($this->getCategory('Category 2', null, true));

        return [
            'product without required name' => [
                'product' => $this->getProduct(null, 'Product name', true),
                'excepted' =>  [],
            ],
            'product without required sku' => [
                'product' => $this->getProduct('SKU-1', null, true),
                'excepted' =>  [],
            ],
            'product with required data' => [
                'product' => $this->getProduct('SKU-1', 'Product name', true),
                'excepted' =>  [
                    'id' => 'SKU-1',
                    'name' => 'Product name',
                ],
            ],
            'product with all data' => [
                'product' => $product,
                'excepted' =>  [
                    'id' => 'SKU-1',
                    'name' => 'Product name',
                    'brand' => 'ACME Brand',
                    'category' => 'Category 1 / Category 2',
                ],
            ],
        ];
    }

    /**
     * @param string $translate
     * @param Localization|null $localization
     * @return LocalizedFallbackValue
     */
    private function createLocalizedFallbackValue(
        string $translate,
        ?Localization $localization
    ): LocalizedFallbackValue {
        $fallbackValue = new LocalizedFallbackValue();
        $fallbackValue->setString($translate);
        $fallbackValue->setLocalization($localization);

        return $fallbackValue;
    }

    /**
     * @return Localization
     */
    private function getLocalization(): Localization
    {
        if (!$this->localization) {
            $this->localization = $this->getEntity(Localization::class, ['id' => 42]);
        }

        return $this->localization;
    }

    /**
     * @param string|null $sku
     * @param string|null $name
     * @param bool $isLocalized
     * @return ProductStub
     */
    private function getProduct(?string $sku, ?string $name, bool $isLocalized = false): ProductStub
    {
        $product = $this->getEntity(ProductStub::class, ['sku' => $sku]);

        if ($name) {
            $product->addName(
                $this->createLocalizedFallbackValue($name, $isLocalized ? $this->getLocalization() : null)
            );
        }

        return $product;
    }

    /**
     * @param string $name
     * @param bool $isLocalized
     * @return BrandStub
     */
    private function getBrand(string $name, bool $isLocalized = false): BrandStub
    {
        return $this->getEntity(
            BrandStub::class,
            [
                'names' => new ArrayCollection(
                    [
                        $this->createLocalizedFallbackValue($name, $isLocalized ? $this->getLocalization() : null)
                    ]
                ),
            ]
        );
    }

    /**
     * @param string $title
     * @param int|null $id
     * @param bool $isLocalized
     * @return CategoryStub
     */
    private function getCategory(string $title, ?int$id = null, bool $isLocalized = false): CategoryStub
    {
        return $this->getEntity(
            CategoryStub::class,
            [
                'id' => $id,
                'titles' => new ArrayCollection(
                    [
                        $this->createLocalizedFallbackValue($title, $isLocalized ? $this->getLocalization() : null)
                    ]
                ),
            ]
        );
    }
}
