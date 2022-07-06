<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Interface for the state providers of specific data collection types (Google Analytics 4, Universal Analytics, etc).
 */
interface DataCollectionStateProviderInterface
{
    public function isEnabled(string $dataCollectionType, ?Website $website = null): ?bool;
}
