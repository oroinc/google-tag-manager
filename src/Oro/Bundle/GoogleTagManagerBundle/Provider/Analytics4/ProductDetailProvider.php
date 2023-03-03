<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\ProductBundle\Entity\Product;

/**
 * Provides product details suitable for use in the GTM data layer.
 */
class ProductDetailProvider
{
    private ManagerRegistry $managerRegistry;

    /** @var Category[][] */
    private array $categoryPaths = [];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Get product detail for single Product entity
     */
    public function getData(Product $product, Localization $localization = null): array
    {
        if (!$product->getSku()) {
            return [];
        }

        $name = $product->getName($localization);
        if (!$name || !$name->getString()) {
            return [];
        }

        $data = [
            'item_id' => $product->getSku(),
            'item_name' => $name->getString(),
        ];

        $brand = $product->getBrand();
        if ($brand) {
            $data['item_brand'] = $brand->getName($localization)->getString();
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        if ($accessor->isReadable($product, 'category') && $accessor->getValue($product, 'category')) {
            $categoryTitles = $this->getCategoryTitles($accessor->getValue($product, 'category'), $localization);
            $this->fillCategories($categoryTitles, $data);
        }

        return array_filter($data);
    }

    private function fillCategories(array $categoryTitles, array &$data): void
    {
        // Removes the first category because the root category can be the only one in catalog so there is no sense
        // in including it in the categories` path.
        array_shift($categoryTitles);

        $n = 0;
        while (count($categoryTitles)) {
            if ($n < 4) {
                $categoryTitle = array_shift($categoryTitles);
            } else {
                // Adds all remaining categories to the "item_category5" if there are more categories.
                $categoryTitle = implode(' / ', $categoryTitles);
                $categoryTitles = [];
            }

            $data['item_category' . ($n++ ? $n : '')] = $categoryTitle;
        }
    }

    private function getCategoryTitles(Category $category, ?Localization $localization): array
    {
        if (!isset($this->categoryPaths[$category->getId()])) {
            $this->categoryPaths[$category->getId()] = $this->managerRegistry
                ->getRepository(Category::class)
                ->getPath($category);
        }

        return array_map(
            static fn (Category $node) => (string)$node->getTitle($localization),
            $this->categoryPaths[$category->getId()]
        );
    }
}
