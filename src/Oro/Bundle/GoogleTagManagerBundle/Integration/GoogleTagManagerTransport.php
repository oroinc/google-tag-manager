<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Integration;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Form\Type\GoogleTagManagerSettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class GoogleTagManagerTransport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'oro.google_tag_manager.integration.transport.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType(): string
    {
        return GoogleTagManagerSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN(): string
    {
        return GoogleTagManagerSettings::class;
    }
}
