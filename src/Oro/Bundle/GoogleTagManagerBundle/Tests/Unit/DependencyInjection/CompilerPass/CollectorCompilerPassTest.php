<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Oro\Component\DependencyInjection\Tests\Unit\AbstractExtensionCompilerPassTest;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CollectorCompilerPassTest extends AbstractExtensionCompilerPassTest
{
    public function testProcess(): void
    {
        $this->assertServiceDefinitionMethodCalled('replaceArgument');
        $this->assertContainerBuilderCalled();

        $this->getCompilerPass()->process($this->containerBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompilerPass(): CompilerPassInterface
    {
        return new CollectorCompilerPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceId(): string
    {
        return 'oro_google_tag_manager.data_layer.manager';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTagName(): string
    {
        return 'oro_google_tag_manager.data_layer.collector';
    }

    /**
     * @param string $method
     */
    protected function assertServiceDefinitionMethodCalled($method): void
    {
        $this->serviceDefinition->expects($this->once())
            ->method($method)
            ->with(
                1,
                [
                    new Reference(self::TAGGED_SERVICE_1),
                    new Reference(self::TAGGED_SERVICE_3),
                    new Reference(self::TAGGED_SERVICE_2),
                ]
            );
    }
}
