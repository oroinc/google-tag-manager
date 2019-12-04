<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oro_google_tag_manager');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('config')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('batch_size')
                            ->info('Number of product items in each batch for sending to GTM')
                            ->min(1)
                            ->defaultValue(30)
                        ->end()
                    ->end()
                ->end()
            ->end();

        SettingsBuilder::append(
            $rootNode,
            [
                'integration' => [
                    'value' => null,
                    'type' => 'integer',
                ],
            ]
        );

        return $treeBuilder;
    }
}
