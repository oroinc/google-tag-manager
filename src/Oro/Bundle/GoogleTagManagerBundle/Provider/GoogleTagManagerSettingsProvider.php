<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides integration settings based on the configuration for current scope.
 */
class GoogleTagManagerSettingsProvider implements GoogleTagManagerSettingsProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ManagerRegistry $registry
     * @param ConfigManager $configManager
     */
    public function __construct(ManagerRegistry $registry, ConfigManager $configManager)
    {
        $this->registry = $registry;
        $this->configManager = $configManager;
    }

    /**
     * @param Website|null $website
     * @return GoogleTagManagerSettings|null
     */
    public function getGoogleTagManagerSettings(?Website $website = null): ?Transport
    {
        $integrationId = $this->configManager->get('oro_google_tag_manager.integration', false, false, $website);
        if ($integrationId === null) {
            return null;
        }

        $channel = $this->getChannelRepository()->getOrLoadById($integrationId);

        return $channel && $channel->isEnabled() ? $channel->getTransport() : null;
    }

    /**
     * @return ChannelRepository
     */
    private function getChannelRepository(): ChannelRepository
    {
        return $this->registry->getManagerForClass(Channel::class)
            ->getRepository(Channel::class);
    }
}
