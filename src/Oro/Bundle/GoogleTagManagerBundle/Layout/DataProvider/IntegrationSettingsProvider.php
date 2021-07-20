<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;

/**
 * Layout data provider for integration settings.
 */
class IntegrationSettingsProvider
{
    /**
     * @var GoogleTagManagerSettingsProviderInterface
     */
    private $settingsProvider;

    /**
     * @var GoogleTagManagerSettings
     */
    private $settings;

    public function __construct(GoogleTagManagerSettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function getContainerId(): ?string
    {
        if (!$this->settings) {
            $this->settings = $this->settingsProvider->getGoogleTagManagerSettings();
        }

        return $this->settings instanceof GoogleTagManagerSettings
            ? $this->settings->getContainerId()
            : null;
    }

    public function isReady(): bool
    {
        return $this->getContainerId() !== null;
    }
}
