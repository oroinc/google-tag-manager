<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\ProductListProductDetailListener;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\WebsiteSearchIndexerListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\ProductBundle\Event\BuildQueryProductListEvent;
use Oro\Bundle\ProductBundle\Event\BuildResultProductListEvent;
use Oro\Bundle\ProductBundle\Model\ProductView;
use Oro\Bundle\SearchBundle\Query\SearchQueryInterface;

class ProductListProductDetailListenerTest extends \PHPUnit\Framework\TestCase
{
    private DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject $dataCollectionStateProvider;

    private ProductListProductDetailListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

        $this->listener = new ProductListProductDetailListener($this->dataCollectionStateProvider);
    }

    public function testOnBuildQueryNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $query = $this->createMock(SearchQueryInterface::class);

        $query->expects(self::never())
            ->method(self::anything());

        $this->listener->onBuildQuery(new BuildQueryProductListEvent('test_list', $query));
    }

    public function testOnBuildQuery(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $query = $this->createMock(SearchQueryInterface::class);

        $query->expects(self::once())
            ->method('addSelect')
            ->with('text.' . WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD)
            ->willReturnSelf();

        $this->listener->onBuildQuery(new BuildQueryProductListEvent('test_list', $query));
    }

    public function testOnBuildResultNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $productData = [
            1 => [
                'id' => 1,
            ],
        ];
        $productView = $this->createMock(ProductView::class);
        $productViews = [1 => $productView];

        $productView->expects(self::never())
            ->method('set');

        $this->listener->onBuildResult(new BuildResultProductListEvent('test_list', $productData, $productViews));
    }

    public function testOnBuildResult(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $productData = [
            1 => [
                'id' => 1,
                WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD => '{"key": "value"}',
            ],
            2 => [
                'id' => 2,
                WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD => '',
            ],
        ];
        $productView1 = $this->createMock(ProductView::class);
        $productView2 = $this->createMock(ProductView::class);
        $productViews = [1 => $productView1, 2 => $productView2];

        $productView1->expects(self::once())
            ->method('set')
            ->with(WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD, ['key' => 'value']);
        $productView2->expects(self::never())
            ->method('set');

        $this->listener->onBuildResult(new BuildResultProductListEvent('test_list', $productData, $productViews));
    }
}
