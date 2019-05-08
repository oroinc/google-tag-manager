<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

/**
 * GoogleTagManager integration channel.
 */
class GoogleTagManagerChannel implements ChannelInterface, IconAwareIntegrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'oro.google_tag_manager.integration.channel.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'bundles/orogoogletagmanager/img/gtm-icon.png';
    }
}
