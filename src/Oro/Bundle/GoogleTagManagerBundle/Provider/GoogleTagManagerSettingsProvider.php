<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides GTM integration settings based on the configuration for current scope.
 */
class GoogleTagManagerSettingsProvider implements GoogleTagManagerSettingsProviderInterface
{
    private ManagerRegistry $doctrine;
    private ConfigManager $configManager;

    public function __construct(ManagerRegistry $doctrine, ConfigManager $configManager)
    {
        $this->doctrine = $doctrine;
        $this->configManager = $configManager;
    }

    /**
     * @param Website|null $website
     *
     * @return GoogleTagManagerSettings|null
     */
    #[\Override]
    public function getGoogleTagManagerSettings(?Website $website = null): ?Transport
    {
        $integrationId = $this->configManager->get(
            Configuration::getConfigKeyByName('integration'),
            false,
            false,
            $website
        );
        if ($integrationId === null) {
            return null;
        }

        $channel = $this->getChannelRepository()->getOrLoadById($integrationId);

        return $channel && $channel->isEnabled() ? $channel->getTransport() : null;
    }

    private function getChannelRepository(): ChannelRepository
    {
        return $this->doctrine->getRepository(Channel::class);
    }
}
