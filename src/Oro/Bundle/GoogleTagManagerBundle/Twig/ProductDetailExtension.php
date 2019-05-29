<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;

/**
 * Provide twig functions to work with product details for GTM data layer
 */
class ProductDetailExtension extends \Twig_Extension
{
    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /**
     * @param ProductDetailProvider $productDetailProvider
     */
    public function __construct(ProductDetailProvider $productDetailProvider)
    {
        $this->productDetailProvider = $productDetailProvider;
    }

    /**
     * @return array|\Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_google_tag_manager_product_detail', [$this, 'getProductDetail']),
        ];
    }

    /**
     * @param mixed $product
     * @return array
     */
    public function getProductDetail($product): array
    {
        return $product instanceof Product ? $this->productDetailProvider->getData($product) : [];
    }
}
