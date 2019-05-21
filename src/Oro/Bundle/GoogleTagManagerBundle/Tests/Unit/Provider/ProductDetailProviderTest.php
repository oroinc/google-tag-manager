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
use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Brand as BrandStub;
use Oro\Bundle\ProductBundle\Tests\Unit\Entity\Stub\Product as ProductStub;
use Oro\Component\Testing\Unit\EntityTrait;

class ProductDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject|LocalizationProviderInterface */
    private $currentLocalizationProvider;

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
        $this->currentLocalizationProvider = $this->createMock(LocalizationProviderInterface::class);

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

        $this->provider = new ProductDetailProvider($this->currentLocalizationProvider, $this->doctrineHelper);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param ProductStub $product
     * @param array $excepted
     */
    public function testGetData(ProductStub $product, array $excepted): void
    {
        $this->currentLocalizationProvider->expects($this->any())
            ->method('getCurrentLocalization')
            ->willReturn($this->getLocalization());

        $this->assertSame($excepted, $this->provider->getData($product));
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param ProductStub $product
     * @param array $excepted
     */
    public function testGetDataForLocalization(ProductStub $product, array $excepted): void
    {
        $this->currentLocalizationProvider->expects($this->never())
            ->method('getCurrentLocalization');

        $this->assertSame($excepted, $this->provider->getData($product, $this->getLocalization()));
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
     * @param string $translate
     * @param Localization $localization
     * @return LocalizedFallbackValue
     */
    private function createLocalizedFallbackValue(string $translate, Localization $localization): LocalizedFallbackValue
    {
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
     * @return ProductStub
     */
    private function getProduct(?string $sku, ?string $name): ProductStub
    {
        $product = $this->getEntity(ProductStub::class, ['sku' => $sku]);

        if ($name) {
            $product->addName($this->createLocalizedFallbackValue($name, $this->getLocalization()));
        }

        return $product;
    }

    /**
     * @param string $name
     * @return BrandStub
     */
    private function getBrand(string $name): BrandStub
    {
        return $this->getEntity(
            BrandStub::class,
            [
                'names' => new ArrayCollection(
                    [
                        $this->createLocalizedFallbackValue($name, $this->getLocalization())
                    ]
                ),
            ]
        );
    }

    /**
     * @param string $title
     * @param int|null $id
     * @return CategoryStub
     */
    private function getCategory(string $title, ?int$id = null): CategoryStub
    {
        return $this->getEntity(
            CategoryStub::class,
            [
                'id' => $id,
                'titles' => new ArrayCollection(
                    [
                        $this->createLocalizedFallbackValue($title, $this->getLocalization())
                    ]
                ),
            ]
        );
    }
}
