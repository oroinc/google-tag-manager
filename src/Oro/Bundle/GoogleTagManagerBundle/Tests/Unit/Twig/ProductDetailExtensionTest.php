<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Twig\ProductDetailExtension;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use Psr\Container\ContainerInterface;

class ProductDetailExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productDetailProvider;

    /** @var ProductDetailExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->with(ProductDetailProvider::class)
            ->willReturn($this->productDetailProvider);

        $this->extension = new ProductDetailExtension($container);
    }

    public function testGetProductDetail(): void
    {
        $product = new Product();
        $data = ['data'];

        $this->productDetailProvider->expects($this->once())
            ->method('getData')
            ->with($this->identicalTo($product))
            ->willReturn($data);

        $this->assertSame(
            $data,
            $this->callTwigFunction($this->extension, 'oro_google_tag_manager_product_detail', [$product])
        );
    }

    public function testGetProductDetailForUnsupportedParameter(): void
    {
        $this->productDetailProvider->expects($this->never())
            ->method('getData');

        $this->assertSame(
            [],
            $this->callTwigFunction($this->extension, 'oro_google_tag_manager_product_detail', [new \stdClass()])
        );
    }
}
