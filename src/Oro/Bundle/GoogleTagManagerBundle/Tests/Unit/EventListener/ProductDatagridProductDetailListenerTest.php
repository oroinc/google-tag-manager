<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\ProductDatagridProductDetailListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;

class ProductDatagridProductDetailListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var GoogleTagManagerSettingsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var ProductDatagridProductDetailListener */
    private $listener;

    protected function setUp(): void
    {
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new ProductDatagridProductDetailListener($this->settingsProvider);
    }

    public function testOnPreBuildNotApplicable(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $config = DatagridConfiguration::create([]);

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag()));

        self::assertSame([], $config->toArray());
    }

    public function testOnPreBuild(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->createMock(Transport::class));

        $config = DatagridConfiguration::create([]);

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag()));

        self::assertEquals([
            'source' => [
                'query' => [
                    'select' => [
                        'text.product_detail as product_detail'
                    ],
                ],
            ],
            'properties' => [
                'product_detail' => [
                    'type' => 'field',
                    'frontend_type' => 'row_array',
                ]
            ]
        ], $config->toArray());
    }

    public function testOnResultAfterNotApplicable(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $event = $this->createMock(SearchResultAfter::class);
        $event->expects(self::never())
            ->method(self::anything());

        $this->listener->onResultAfter($event);
    }

    public function testOnResultAfter(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->createMock(Transport::class));

        $record1 = new ResultRecord([
            'product_detail' => '{"attr":"value"}',
            'another_value' => '{"attr":"value"}',
        ]);

        $record2 = new ResultRecord([
            'product_detail' => '',
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

        self::assertEquals(['attr' => 'value'], $record1->getValue('product_detail'));
        self::assertEquals('{"attr":"value"}', $record1->getValue('another_value'));

        self::assertEquals('', $record2->getValue('product_detail'));
        self::assertEquals('{"attr":"value"}', $record2->getValue('another_value'));
    }
}
