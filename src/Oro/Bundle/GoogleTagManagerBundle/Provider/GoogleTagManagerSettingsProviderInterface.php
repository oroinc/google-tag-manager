<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Interface for the settings providers.
 */
interface GoogleTagManagerSettingsProviderInterface
{
    public function getGoogleTagManagerSettings(?Website $website = null): ?Transport;
}
