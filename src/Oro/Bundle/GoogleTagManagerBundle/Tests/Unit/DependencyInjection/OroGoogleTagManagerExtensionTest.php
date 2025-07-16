<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\OroGoogleTagManagerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroGoogleTagManagerExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroGoogleTagManagerExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'integration' => ['value' => null, 'scope' => 'app'],
                        'enabled_data_collection_types' => ['value' => ['google_analytics4'], 'scope' => 'app']
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_google_tag_manager')
        );

        self::assertSame(30, $container->getParameter('oro_google_tag_manager.products.batch_size'));
    }

    public function testLoadWithCustomConfigs(): void
    {
        $container = new ContainerBuilder();

        $configs = [
            ['config' => ['batch_size' => 100]]
        ];

        $extension = new OroGoogleTagManagerExtension();
        $extension->load($configs, $container);

        self::assertSame(100, $container->getParameter('oro_google_tag_manager.products.batch_size'));
    }
}
