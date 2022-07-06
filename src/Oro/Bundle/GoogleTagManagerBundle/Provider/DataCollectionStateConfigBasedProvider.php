<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides state for the data collection types taking data from system config.
 */
class DataCollectionStateConfigBasedProvider implements DataCollectionStateProviderInterface
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function isEnabled(string $dataCollectionType, ?Website $website = null): ?bool
    {
        $enabledTypes = (array) $this->configManager->get(
            Configuration::getConfigKeyByName('enabled_data_collection_types'),
            false,
            false,
            $website
        );

        return in_array($dataCollectionType, $enabledTypes, true);
    }
}
