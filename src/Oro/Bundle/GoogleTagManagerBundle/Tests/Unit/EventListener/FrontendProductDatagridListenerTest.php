<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\FrontendProductDatagridListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;

class FrontendProductDatagridListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Transport|\PHPUnit\Framework\MockObject\MockObject */
    private $transport;

    /** @var GoogleTagManagerSettingsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var FrontendProductDatagridListener */
    private $listener;

    public function setUp(): void
    {
        $this->transport = $this->createMock(Transport::class);

        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new FrontendProductDatagridListener($this->settingsProvider);
    }

    public function testOnPreBuildNotApplicable(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $config = DatagridConfiguration::create([]);

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag()));

        $this->assertEquals([], $config->toArray());
    }

    public function testOnPreBuild(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $config = DatagridConfiguration::create([]);

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag()));

        $this->assertEquals([
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
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        /** @var \PHPUnit\Framework\MockObject\MockObject|SearchResultAfter $event */
        $event = $this->createMock(SearchResultAfter::class);
        $event->expects($this->never())
            ->method($this->anything());

        $this->listener->onResultAfter($event);
    }

    public function testOnResultAfter(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $record1 = new ResultRecord([
            'product_detail' => '{"attr":"value"}',
            'another_value' => '{"attr":"value"}',
        ]);

        $record2 = new ResultRecord([
            'product_detail' => '',
            'another_value' => '{"attr":"value"}',
        ]);

        /** @var \PHPUnit\Framework\MockObject\MockObject|SearchResultAfter $event */
        $event = $this->createMock(SearchResultAfter::class);
        $event->expects($this->once())
            ->method('getRecords')
            ->willReturn([
                'default' => $record1,
                'empty' => $record2,
            ]);

        $this->listener->onResultAfter($event);

        $this->assertEquals(['attr' => 'value'], $record1->getValue('product_detail'));
        $this->assertEquals('{"attr":"value"}', $record1->getValue('another_value'));

        $this->assertEquals('', $record2->getValue('product_detail'));
        $this->assertEquals('{"attr":"value"}', $record2->getValue('another_value'));
    }
}
