<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
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
            new TwigFunction('oro_google_tag_manager_analytics4_product_detail', [$this, 'getAnalytics4ProductDetail']),
        ];
    }

    /**
     * @param mixed $product
     * @return array
     */
    public function getAnalytics4ProductDetail($product): array
    {
        return $product instanceof Product
            ? $this->getProductDetailProvider()->getData($product)
            : [];
    }

    public static function getSubscribedServices(): array
    {
        return [
            'oro_google_tag_manager.provider.analytics4.product_detail' => ProductDetailProvider::class
        ];
    }

    private function getProductDetailProvider(): ProductDetailProvider
    {
        return $this->container->get('oro_google_tag_manager.provider.analytics4.product_detail');
    }
}
