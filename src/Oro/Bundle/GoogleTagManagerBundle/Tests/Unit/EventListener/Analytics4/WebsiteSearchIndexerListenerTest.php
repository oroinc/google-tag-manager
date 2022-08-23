<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\WebsiteSearchIndexerListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteSearchBundle\Engine\AbstractIndexer;
use Oro\Bundle\WebsiteSearchBundle\Event\IndexEntityEvent;
use Oro\Bundle\WebsiteSearchBundle\Manager\WebsiteContextManager;
use Oro\Component\Testing\Unit\EntityTrait;

class WebsiteSearchIndexerListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private \PHPUnit\Framework\MockObject\MockObject $websiteContextManger;

    private \PHPUnit\Framework\MockObject\MockObject $productDetailProvider;

    private WebsiteSearchIndexerListener $listener;

    protected function setUp(): void
    {
        $this->markTestSkipped('BB-21644');

        $this->websiteContextManger = $this->createMock(WebsiteContextManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        $this->listener = new WebsiteSearchIndexerListener(
            $this->websiteContextManger,
            $this->productDetailProvider
        );
    }

    public function testRunWithoutWebsiteContext(): void
    {
        $this->websiteContextManger->expects(self::once())
            ->method('getWebsiteId')
            ->willReturn(null);

        $event = $this->createIndexEntityEvent();
        $event->expects(self::once())->method('stopPropagation');
        $event->expects(self::never())->method('getEntities');

        $this->listener->onWebsiteSearchIndex($event);
    }

    /**
     * @dataProvider validContextDataProvider
     */
    public function testOnWebsiteSearchIndex(array $context): void
    {
        $products = [
            $this->getEntity(Product::class, ['id' => 1001, 'sku' => 'SKU-1']),
            $this->getEntity(Product::class, ['id' => 1002, 'sku' => 'SKU-2']),
            $this->getEntity(Product::class, ['id' => 1003, 'sku' => 'SKU-3']),
        ];

        $event = $this->createIndexEntityEvent($context);
        $event->expects(self::never())->method('stopPropagation');
        $event->expects(self::once())
            ->method('getEntities')
            ->willReturn($products);

        $this->websiteContextManger->expects(self::once())
            ->method('getWebsiteId')
            ->willReturn(11);

        $expected = [];
        foreach ($products as $product) {
            $expected[] = [
                $product->getId(),
                WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD,
                \json_encode(['product_id' => $product->getId()]),
                false,
            ];
        }
        $this->productDetailProvider->expects(self::exactly(count($expected)))
            ->method('getData')
            ->willReturnCallback(static fn (Product $product) => ['product_id' => $product->getId()]);

        $event->expects(self::exactly(count($expected)))
            ->method('addField')
            ->withConsecutive(...$expected);

        $this->listener->onWebsiteSearchIndex($event);
    }

    public function validContextDataProvider(): \Generator
    {
        yield [[]];
        yield [[AbstractIndexer::CONTEXT_FIELD_GROUPS => ['main']]];
    }

    public function testOnWebsiteSearchIndexWithUnsupportedFieldGroup(): void
    {
        $event = $this->createIndexEntityEvent([AbstractIndexer::CONTEXT_FIELD_GROUPS => ['image']]);

        $this->websiteContextManger->expects(self::never())
            ->method(self::anything());

        $this->productDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->listener->onWebsiteSearchIndex($event);
    }

    private function createIndexEntityEvent(
        array $context = []
    ): IndexEntityEvent {
        $event = $this->createMock(IndexEntityEvent::class);
        $event->expects(self::any())
            ->method('getContext')
            ->willReturn($context);

        return $event;
    }
}
