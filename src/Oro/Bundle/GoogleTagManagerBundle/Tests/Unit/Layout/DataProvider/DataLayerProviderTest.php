<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\DataLayerProvider;

class DataLayerProviderTest extends \PHPUnit\Framework\TestCase
{
    private const DATA_LAYER_VARIABLE_NAME = 'dataLayer';

    /** @var DataLayerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $dataLayerManager;

    /** @var DataLayerProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);

        $this->provider = new DataLayerProvider($this->dataLayerManager, self::DATA_LAYER_VARIABLE_NAME);
    }

    public function testGetVariableName(): void
    {
        $this->assertEquals(self::DATA_LAYER_VARIABLE_NAME, $this->provider->getVariableName());
    }

    public function testGetConfigWithReset(): void
    {
        $this->dataLayerManager->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $this->dataLayerManager->expects($this->once())
            ->method('reset');

        $this->assertEquals([], $this->provider->getData(true));
    }

    /**
     * @dataProvider getConfigDataProvider
     *
     * @param array $config
     * @param array $expected
     */
    public function testGetConfig(array $config, array $expected): void
    {
        $this->dataLayerManager->expects($this->once())
            ->method('all')
            ->willReturn($config);

        $this->dataLayerManager->expects($this->never())
            ->method('reset');

        $this->assertEquals($expected, $this->provider->getData());
    }

    /**
     * @return array
     */
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
                        'key3' => null
                    ]
                ],
                'expected' => [
                    [
                        'key1' => 'value1'
                    ],
                    [
                        'key1' => 'value1',
                        'key2' => '',
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
