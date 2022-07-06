<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\OroGoogleTagManagerExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroGoogleTagManagerExtensionTest extends ExtensionTestCase
{
    private ContainerBuilder $container;

    private OroGoogleTagManagerExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();

        $this->extension = new OroGoogleTagManagerExtension();
    }

    public function testGetAlias(): void
    {
        self::assertEquals('oro_google_tag_manager', $this->extension->getAlias());
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        self::assertEquals([
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
            ],
        ], $this->container->getExtensionConfig('oro_google_tag_manager'));
    }
}
