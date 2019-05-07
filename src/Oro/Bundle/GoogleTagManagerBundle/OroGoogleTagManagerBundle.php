<?php

namespace Oro\Bundle\GoogleTagManagerBundle;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\PageTypeGuesserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The GoogleTagManager bundle class.
 */
class OroGoogleTagManagerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CollectorCompilerPass());
        $container->addCompilerPass(new PageTypeGuesserCompilerPass());
    }
}
