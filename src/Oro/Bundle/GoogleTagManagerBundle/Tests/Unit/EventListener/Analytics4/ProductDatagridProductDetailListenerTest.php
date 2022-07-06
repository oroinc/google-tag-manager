<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\ProductDatagridProductDetailListener;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\WebsiteSearchIndexerListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;

class ProductDatagridProductDetailListenerTest extends \PHPUnit\Framework\TestCase
{
    private DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject $dataCollectionStateProvider;

    private ProductDatagridProductDetailListener $listener;

    protected function setUp(): void
    {
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

        $this->listener = new ProductDatagridProductDetailListener($this->dataCollectionStateProvider);
    }

    public function testOnPreBuildNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $config = DatagridConfiguration::create([]);

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag()));

        self::assertSame([], $config->toArray());
    }

    public function testOnPreBuild(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $config = DatagridConfiguration::create([]);

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag()));

        self::assertEquals([
            'source' => [
                'query' => [
                    'select' => [
                        sprintf('text.%1$s as %1$s', WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD),
                    ],
                ],
            ],
            'properties' => [
                WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD => [
                    'type' => 'field',
                    'frontend_type' => 'row_array',
                ],
            ],
        ], $config->toArray());
    }

    public function testOnResultAfterNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $event = $this->createMock(SearchResultAfter::class);
        $event->expects(self::never())
            ->method(self::anything());

        $this->listener->onResultAfter($event);
    }

    public function testOnResultAfter(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $record1 = new ResultRecord([
            WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD => '{"attr":"value"}',
            'another_value' => '{"attr":"value"}',
        ]);

        $record2 = new ResultRecord([
            WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD => '',
            'another_value' => '{"attr":"value"}',
        ]);

        $event = $this->createMock(SearchResultAfter::class);
        $event->expects(self::once())
            ->method('getRecords')
            ->willReturn([
                'default' => $record1,
                'empty' => $record2,
            ]);

        $this->listener->onResultAfter($event);

        self::assertEquals(['attr' => 'value'], $record1->getValue(WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD));
        self::assertEquals('{"attr":"value"}', $record1->getValue('another_value'));

        self::assertEquals('', $record2->getValue(WebsiteSearchIndexerListener::PRODUCT_DETAIL_FIELD));
        self::assertEquals('{"attr":"value"}', $record2->getValue('another_value'));
    }
}
