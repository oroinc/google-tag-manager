<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider as Analytics4ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provide twig functions to work with product details for GTM data layer:
 *   - oro_google_tag_manager_analytics4_product_detail
 */
class ProductDetailExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFunctions()
    {
        return [
            // Component added back for theme layout BC from version 5.0
            new TwigFunction(
                'oro_google_tag_manager_product_detail',
                [$this, 'getProductDetail'],
                ['deprecated' => true]
            ),
            new TwigFunction('oro_google_tag_manager_analytics4_product_detail', [$this, 'getAnalytics4ProductDetail']),
        ];
    }

    /**
     * Component added back for theme layout BC from version 5.0
     */
    public function getProductDetail(mixed $product): array
    {
        return $product instanceof Product
            ? $this->getProductDetailProvider()->getData($product)
            : [];
    }

    public function getAnalytics4ProductDetail(mixed $product): array
    {
        return $product instanceof Product
            ? $this->getAnalytics4ProductDetailProvider()->getData($product)
            : [];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ProductDetailProvider::class,
            'oro_google_tag_manager.provider.analytics4.product_detail' => Analytics4ProductDetailProvider::class,
        ];
    }

    /**
     * Component added back for theme layout BC from version 5.0
     */
    private function getProductDetailProvider(): ProductDetailProvider
    {
        return $this->container->get(ProductDetailProvider::class);
    }

    private function getAnalytics4ProductDetailProvider(): Analytics4ProductDetailProvider
    {
        return $this->container->get('oro_google_tag_manager.provider.analytics4.product_detail');
    }
}
