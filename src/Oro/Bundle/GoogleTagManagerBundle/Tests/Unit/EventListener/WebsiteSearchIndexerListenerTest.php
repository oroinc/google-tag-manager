<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Oro\Bundle\GoogleTagManagerBundle\EventListener\WebsiteSearchIndexerListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteSearchBundle\Event\IndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Manager\WebsiteContextManager;
use Oro\Component\Testing\Unit\EntityTrait;

class WebsiteSearchIndexerListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject|WebsiteContextManager */
    private $websiteContextManger;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ProductDetailProvider */
    private $productDetailProvider;

    /** @var WebsiteSearchIndexerListener */
    private $listener;

    public function setUp()
    {
        $this->websiteContextManger = $this->createMock(WebsiteContextManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        $this->listener = new WebsiteSearchIndexerListener(
            $this->websiteContextManger,
            $this->productDetailProvider
        );
    }

    public function testRunWithoutWebsiteContext(): void
    {
        $this->websiteContextManger->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(null);

        $event = $this->createIndexEntityEvent();
        $event->expects($this->once())->method('stopPropagation');
        $event->expects($this->never())->method('getEntities');

        $this->listener->onWebsiteSearchIndex($event);
    }

    public function testOnWebsiteSearchIndex(): void
    {
        $products = [
            $this->getEntity(Product::class, ['id' => 1001, 'sku' => 'SKU-1']),
            $this->getEntity(Product::class, ['id' => 1002, 'sku' => 'SKU-2']),
            $this->getEntity(Product::class, ['id' => 1003, 'sku' => 'SKU-3']),
        ];

        $event = $this->createIndexEntityEvent();
        $event->expects($this->never())->method('stopPropagation');
        $event->expects($this->once())
            ->method('getEntities')
            ->willReturn($products);

        $this->websiteContextManger->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(11);

        $expected = [];
        foreach ($products as $product) {
            $expected[] = [
                $product->getId(),
                'product_detail',
                \json_encode(['product_id' => $product->getId()]),
                false
            ];
        }
        $this->productDetailProvider->expects($this->exactly(count($expected)))
            ->method('getData')
            ->willReturnCallback(
                static function (Product $product): array {
                    return ['product_id' => $product->getId()];
                }
            );

        $event->expects($this->exactly(count($expected)))
            ->method('addField')
            ->withConsecutive(...$expected);

        $this->listener->onWebsiteSearchIndex($event);
    }

    /**
     * @param array $context
     * @return \PHPUnit\Framework\MockObject\MockObject|IndexEntityEvent
     */
    private function createIndexEntityEvent(array $context = []): IndexEntityEvent
    {
        $event = $this->createMock(IndexEntityEvent::class);
        $event->expects($this->once())
            ->method('getContext')
            ->willReturn($context);

        return $event;
    }
}
