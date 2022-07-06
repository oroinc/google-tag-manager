<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Twig\ProductDetailExtension;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class ProductDetailExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    private ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject $productDetailProvider;

    private ProductDetailExtension $extension;

    protected function setUp(): void
    {
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        $container = self::getContainerBuilder()
            ->add('oro_google_tag_manager.provider.analytics4.product_detail', $this->productDetailProvider)
            ->getContainer($this);

        $this->extension = new ProductDetailExtension($container);
    }

    public function testGetProductDetail(): void
    {
        $product = new Product();
        $data = ['data'];

        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with(self::identicalTo($product))
            ->willReturn($data);

        self::assertSame(
            $data,
            self::callTwigFunction($this->extension, 'oro_google_tag_manager_analytics4_product_detail', [$product])
        );
    }

    public function testGetProductDetailForUnsupportedParameter(): void
    {
        $this->productDetailProvider->expects(self::never())
            ->method('getData');

        self::assertSame(
            [],
            self::callTwigFunction(
                $this->extension,
                'oro_google_tag_manager_analytics4_product_detail',
                [new \stdClass()]
            )
        );
    }
}
