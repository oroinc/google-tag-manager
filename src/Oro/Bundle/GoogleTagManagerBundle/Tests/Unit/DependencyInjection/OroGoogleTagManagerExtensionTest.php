<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\OroGoogleTagManagerExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroGoogleTagManagerExtensionTest extends ExtensionTestCase
{
    /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private $container;

    /** @var OroGoogleTagManagerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);

        $this->extension = new OroGoogleTagManagerExtension();
    }

    public function testLoad(): void
    {
        $this->container->expects($this->once())
            ->method('prependExtensionConfig')
            ->with(
                'oro_google_tag_manager',
                [
                    'settings' => [
                        'resolved' => true,
                        'integration' => [
                            'value' => null,
                            'scope' => 'app',
                        ],
                    ]
                ]
            );

        $this->extension->load([], $this->container);
    }
}
