<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\WebsiteBundle\Entity\Website;

interface GoogleTagManagerSettingsProviderInterface
{
    /**
     * @param Website|null $website
     *
     * @return Transport|null
     */
    public function getGoogleTagManagerSettings(?Website $website = null): ?Transport;
}
