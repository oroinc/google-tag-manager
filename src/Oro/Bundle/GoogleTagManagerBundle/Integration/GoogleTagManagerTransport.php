<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Integration;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Form\Type\GoogleTagManagerSettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

/**
 * GoogleTagManager integration transport.
 */
class GoogleTagManagerTransport implements TransportInterface
{
    #[\Override]
    public function init(Transport $transportEntity)
    {
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'oro.google_tag_manager.integration.transport.label';
    }

    #[\Override]
    public function getSettingsFormType(): string
    {
        return GoogleTagManagerSettingsType::class;
    }

    #[\Override]
    public function getSettingsEntityFQCN(): string
    {
        return GoogleTagManagerSettings::class;
    }
}
