<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provide twig functions to work with product details for GTM data layer:
 *   - oro_google_tag_manager_product_detail
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
            new TwigFunction('oro_google_tag_manager_product_detail', [$this, 'getProductDetail']),
        ];
    }

    /**
     * @param mixed $product
     * @return array
     */
    public function getProductDetail($product): array
    {
        return $product instanceof Product
            ? $this->getProductDetailProvider()->getData($product)
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_google_tag_manager.provider.product_detail' => ProductDetailProvider::class
        ];
    }

    private function getProductDetailProvider(): ProductDetailProvider
    {
        return $this->container->get('oro_google_tag_manager.provider.product_detail');
    }
}
