<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Formatter\NumberFormatter;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider as Analytics4ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Provide twig functions to work with product details for GTM data layer:
 *   - oro_google_tag_manager_product_detail
 *   - oro_google_tag_manager_analytics4_product_detail
 *   - oro_google_tag_manager_format_price
 */
class ProductDetailExtension extends \Twig_Extension implements ServiceSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            // @deprecated oro_google_tag_manager_product_detail will be removed in oro/google-tag-manager-bundle:5.1.0.
            new TwigFunction(
                'oro_google_tag_manager_product_detail',
                [$this, 'getProductDetail'],
                ['deprecated' => true]
            ),
            new TwigFunction('oro_google_tag_manager_analytics4_product_detail', [$this, 'getAnalytics4ProductDetail']),
            new TwigFunction('oro_google_tag_manager_format_price', [$this, 'getAnalytics4ProductDetail']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('oro_google_tag_manager_format_price', [$this, 'formatPriceValue']),
        ];
    }

    /**
     * @param mixed $product
     * @return array
     *
     * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
     */
    public function getProductDetail($product): array
    {
        return $product instanceof Product
            ? $this->getProductDetailProvider()->getData($product)
            : [];
    }

    /**
     * @param mixed $product
     * @return array
     */
    public function getAnalytics4ProductDetail($product): array
    {
        return $product instanceof Product
            ? $this->getAnalytics4ProductDetailProvider()->getData($product)
            : [];
    }

    /**
     * @param string|int|float $value
     *
     * @return float
     */
    public function formatPriceValue($value): float
    {
        return $this->getNumberFormatter()->formatPriceValue((float)$value);
    }

    public static function getSubscribedServices(): array
    {
        return [
            'oro_google_tag_manager.provider.product_detail' => ProductDetailProvider::class,
            'oro_google_tag_manager.provider.analytics4.product_detail' => Analytics4ProductDetailProvider::class,
            'oro_google_tag_manager.formatter.number' => NumberFormatter::class,
        ];
    }

    private function getProductDetailProvider(): ProductDetailProvider
    {
        return $this->container->get('oro_google_tag_manager.provider.product_detail');
    }

    private function getAnalytics4ProductDetailProvider(): Analytics4ProductDetailProvider
    {
        return $this->container->get('oro_google_tag_manager.provider.analytics4.product_detail');
    }

    private function getNumberFormatter(): NumberFormatter
    {
        return $this->container->get('oro_google_tag_manager.formatter.number');
    }
}
