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
        $treeBuilder = new TreeBuilder();

        SettingsBuilder::append(
            $treeBuilder->root('oro_google_tag_manager'),
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
