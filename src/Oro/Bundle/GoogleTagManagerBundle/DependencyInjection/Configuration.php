<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_NAME = 'oro_google_tag_manager';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NAME);
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
                'enabled_data_collection_types' => [
                    'value' => ['google_analytics4'],
                    'type' => 'array',
                ],
            ]
        );

        return $treeBuilder;
    }

    public static function getConfigKeyByName(string $name): string
    {
        return TreeUtils::getConfigKey(self::ROOT_NAME, $name, ConfigManager::SECTION_MODEL_SEPARATOR);
    }

    public static function getFieldKeyByName(string $name): string
    {
        return TreeUtils::getConfigKey(self::ROOT_NAME, $name, ConfigManager::SECTION_VIEW_SEPARATOR);
    }
}
