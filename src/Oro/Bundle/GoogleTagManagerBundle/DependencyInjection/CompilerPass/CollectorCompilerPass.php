<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass;

use Oro\Component\DependencyInjection\Compiler\TaggedServicesCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CollectorCompilerPass implements CompilerPassInterface
{
    use TaggedServicesCompilerPassTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerTaggedServices(
            $container,
            'oro_google_tag_manager.data_layer.manager',
            'oro_google_tag_manager.data_layer.collector',
            'addCollector'
        );
    }
}
