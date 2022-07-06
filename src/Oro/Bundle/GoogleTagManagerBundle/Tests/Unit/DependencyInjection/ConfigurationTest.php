<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder(): void
    {
        $configuration = new Configuration();

        self::assertInstanceOf(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }

    public function testProcessConfiguration(): void
    {
        $processor = new Processor();

        self::assertEquals(
            [
                'settings' => [
                    'resolved' => true,
                    'integration' => [
                        'value' => null,
                        'scope' => 'app',
                    ],
                    'enabled_data_collection_types' => [
                        'value' => ['universal_analytics'],
                        'scope' => 'app',
                    ],
                ],
                'config' => [
                    'batch_size' => 30,
                ],
            ],
            $processor->processConfiguration(new Configuration(), [])
        );
    }
}
