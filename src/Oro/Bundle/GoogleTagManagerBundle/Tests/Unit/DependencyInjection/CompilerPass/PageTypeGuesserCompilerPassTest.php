<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\PageTypeGuesserCompilerPass;
use Oro\Component\DependencyInjection\Tests\Unit\AbstractExtensionCompilerPassTest;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class PageTypeGuesserCompilerPassTest extends AbstractExtensionCompilerPassTest
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
        return new PageTypeGuesserCompilerPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceId(): string
    {
        return 'oro_google_tag_manager.provider.page_type';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTagName(): string
    {
        return 'oro_google_tag_manager.page_type_guesser';
    }

    /**
     * @param string $method
     */
    protected function assertServiceDefinitionMethodCalled($method): void
    {
        $this->serviceDefinition->expects($this->once())
            ->method($method)
            ->with(
                0,
                [
                    new Reference(self::TAGGED_SERVICE_1),
                    new Reference(self::TAGGED_SERVICE_3),
                    new Reference(self::TAGGED_SERVICE_2),
                ]
            );
    }
}
