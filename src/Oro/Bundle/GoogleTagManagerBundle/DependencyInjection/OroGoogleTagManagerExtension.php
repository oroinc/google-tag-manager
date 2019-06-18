<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroGoogleTagManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('collectors.yml');
        $loader->load('form_types.yml');
        $loader->load('integration.yml');
        $loader->load('layout.yml');
        $loader->load('services.yml');
        $loader->load('twig.yml');

        $container->setParameter('oro_google_tag_manager.products.batch_size', $config['config']['batch_size']);
    }
}
