<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Provides states for the data collection types supported by inner providers.
 */
class DataCollectionStateProvider implements
    GoogleTagManagerSettingsProviderInterface,
    DataCollectionStateProviderInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private GoogleTagManagerSettingsProviderInterface $googleTagManagerSettingsProvider;

    /** @var iterable<DataCollectionStateProviderInterface> */
    private iterable $providers;

    public function __construct(
        GoogleTagManagerSettingsProviderInterface $googleTagManagerSettingsProvider,
        iterable $providers
    ) {
        $this->googleTagManagerSettingsProvider = $googleTagManagerSettingsProvider;
        $this->providers = $providers;
        $this->logger = new NullLogger();
    }

    public function getGoogleTagManagerSettings(?Website $website = null): ?Transport
    {
        return $this->googleTagManagerSettingsProvider->getGoogleTagManagerSettings($website);
    }

    public function isEnabled(string $dataCollectionType, ?Website $website = null): ?bool
    {
        $gtmSettings = $this->googleTagManagerSettingsProvider->getGoogleTagManagerSettings($website);
        if (!$gtmSettings instanceof GoogleTagManagerSettings || $gtmSettings->getContainerId() === null) {
            return false;
        }

        foreach ($this->providers as $provider) {
            $state = $provider->isEnabled($dataCollectionType, $website);
            if ($state !== null) {
                return $state;
            }
        }

        $this->logger->error(
            'Google Tag Manager data collection type "{type}" is not supported',
            ['type' => $dataCollectionType, 'website' => $website]
        );

        return false;
    }
}
