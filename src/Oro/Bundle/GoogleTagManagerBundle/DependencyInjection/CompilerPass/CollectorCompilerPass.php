<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers all collector of data layer data.
 */
class CollectorCompilerPass implements CompilerPassInterface
{
    private const SERVICE_ID = 'oro_google_tag_manager.data_layer.manager';
    private const TAG_NAME = 'oro_google_tag_manager.data_layer.collector';

    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_ID)) {
            return;
        }

        $container->getDefinition(self::SERVICE_ID)
            ->replaceArgument(1, $this->findAndSortTaggedServices(self::TAG_NAME, $container));
    }
}
