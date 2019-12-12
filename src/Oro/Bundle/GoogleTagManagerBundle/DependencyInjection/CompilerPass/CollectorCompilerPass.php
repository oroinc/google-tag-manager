<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * All collectors tagged with "oro_google_tag_manager.data_layer.collector" are aggregated
 * by !tagged oro_google_tag_manager.data_layer.collector in the service definition directly.
 * This prevents circular reference error. This compiler pass remains only to maintain backwards compatibility.
 */
class CollectorCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        return;
    }
}
