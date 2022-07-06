<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Twig;

use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider as Analytics4ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Twig\ProductDetailExtension;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class ProductDetailExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    private ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject $productDetailProvider;

    private Analytics4ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject $analytics4ProductDetailProvider;

    private ProductDetailExtension $extension;

    protected function setUp(): void
    {
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->analytics4ProductDetailProvider = $this->createMock(Analytics4ProductDetailProvider::class);

        $container = self::getContainerBuilder()
            ->add('oro_google_tag_manager.provider.product_detail', $this->productDetailProvider)
            ->add('oro_google_tag_manager.provider.analytics4.product_detail', $this->analytics4ProductDetailProvider)
            ->getContainer($this);

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

    public function testGetAnalytics4ProductDetail(): void
    {
        $product = new Product();
        $data = ['data'];

        $this->analytics4ProductDetailProvider->expects(self::once())
            ->method('getData')
            ->with(self::identicalTo($product))
            ->willReturn($data);

        self::assertSame(
            $data,
            self::callTwigFunction($this->extension, 'oro_google_tag_manager_analytics4_product_detail', [$product])
        );
    }

    public function testGetAnalytics4ProductDetailForUnsupportedParameter(): void
    {
        $this->analytics4ProductDetailProvider->expects(self::never())
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
