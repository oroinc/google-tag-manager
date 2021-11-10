<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Oro\Bundle\GoogleTagManagerBundle\EventListener\ProductListProductDetailListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\ProductBundle\Event\BuildQueryProductListEvent;
use Oro\Bundle\ProductBundle\Event\BuildResultProductListEvent;
use Oro\Bundle\ProductBundle\Model\ProductView;
use Oro\Bundle\SearchBundle\Query\SearchQueryInterface;

class ProductListProductDetailListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var GoogleTagManagerSettingsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var ProductListProductDetailListener */
    private $listener;

    protected function setUp(): void
    {
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new ProductListProductDetailListener($this->settingsProvider);
    }

    public function testOnBuildQueryNotApplicable(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $query = $this->createMock(SearchQueryInterface::class);

        $query->expects(self::never())
            ->method(self::anything());

        $this->listener->onBuildQuery(new BuildQueryProductListEvent('test_list', $query));
    }

    public function testOnBuildQuery(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->createMock(Transport::class));

        $query = $this->createMock(SearchQueryInterface::class);

        $query->expects(self::once())
            ->method('addSelect')
            ->with('text.product_detail')
            ->willReturnSelf();

        $this->listener->onBuildQuery(new BuildQueryProductListEvent('test_list', $query));
    }

    public function testOnBuildResultNotApplicable(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $productData = [
            1 => [
                'id' => 1
            ]
        ];
        $productView = $this->createMock(ProductView::class);
        $productViews = [1 => $productView];

        $productView->expects(self::never())
            ->method('set');

        $this->listener->onBuildResult(new BuildResultProductListEvent('test_list', $productData, $productViews));
    }

    public function testOnBuildResult(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->createMock(Transport::class));

        $productData = [
            1 => [
                'id'             => 1,
                'product_detail' => '{"key": "value"}'
            ],
            2 => [
                'id'             => 2,
                'product_detail' => ''
            ]
        ];
        $productView1 = $this->createMock(ProductView::class);
        $productView2 = $this->createMock(ProductView::class);
        $productViews = [1 => $productView1, 2 => $productView2];

        $productView1->expects(self::once())
            ->method('set')
            ->with('product_detail', ['key' => 'value']);
        $productView2->expects(self::never())
            ->method('set');

        $this->listener->onBuildResult(new BuildResultProductListEvent('test_list', $productData, $productViews));
    }
}
