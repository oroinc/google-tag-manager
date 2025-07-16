<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\DataLayerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataLayerProviderTest extends TestCase
{
    private const DATA_LAYER_VARIABLE_NAME = 'dataLayer';

    private const BATCH_SIZE = 11;

    private DataLayerManager&MockObject $dataLayerManager;
    private DataLayerProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);

        $this->provider = new DataLayerProvider(
            $this->dataLayerManager,
            self::DATA_LAYER_VARIABLE_NAME,
            self::BATCH_SIZE
        );
    }

    public function testGetVariableName(): void
    {
        $this->assertEquals(self::DATA_LAYER_VARIABLE_NAME, $this->provider->getVariableName());
    }

    public function testGetBatchSize(): void
    {
        $this->assertEquals(self::BATCH_SIZE, $this->provider->getBatchSize());
    }

    public function testGetConfigForEvents(): void
    {
        $events = ['test_event2'];
        $data = [['data']];

        $this->dataLayerManager->expects($this->once())
            ->method('getForEvents')
            ->with($events)
            ->willReturn($data);

        $this->dataLayerManager->expects($this->never())
            ->method('collectAll');

        $this->dataLayerManager->expects($this->never())
            ->method('reset');

        $this->assertEquals($data, $this->provider->getData($events));
    }

    /**
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(array $config, array $expected): void
    {
        $this->dataLayerManager->expects($this->once())
            ->method('collectAll')
            ->willReturn($config);

        $this->dataLayerManager->expects($this->once())
            ->method('reset');

        $this->assertEquals($expected, $this->provider->getData());
    }

    public function getConfigDataProvider(): array
    {
        return [
            'without nullable data' => [
                'config' => [
                    [
                        'key1' => 'value1'
                    ],
                    [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3'
                    ]
                ],
                'expected' => [
                    [
                        'key1' => 'value1'
                    ],
                    [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3'
                    ]
                ]
            ],
            'with nullable data' => [
                'config' => [
                    [
                        'key1' => 'value1'
                    ],
                    [
                        'key1' => 'value1',
                        'key2' => '',
                        'key3' => null,
                        'key4' => [],
                    ]
                ],
                'expected' => [
                    [
                        'key1' => 'value1'
                    ],
                    [
                        'key1' => 'value1',
                    ]
                ]
            ],
            'with nullable config item' => [
                'config' => [
                    [
                        'key1' => 'value1'
                    ],
                    [
                        'key1' => null,
                        'key2' => null,
                        'key3' => null
                    ]
                ],
                'expected' => [
                    [
                        'key1' => 'value1'
                    ]
                ]
            ],
            'with nullable all config data' => [
                'config' => [
                    [
                        'key1' => null
                    ],
                    [
                        'key1' => null,
                        'key2' => null,
                        'key3' => null
                    ]
                ],
                'expected' => []
            ],
        ];
    }
}
