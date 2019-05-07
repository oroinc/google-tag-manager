<?php

namespace Oro\Bundle\GoogleTagManagerBundle;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroGoogleTagManagerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CollectorCompilerPass());
    }
}
