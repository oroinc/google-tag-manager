<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Layout data provider for GTM data collection states (enabled/disabled).
 */
class DataCollectionStateProvider
{
    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    public function __construct(DataCollectionStateProviderInterface $dataCollectionStateProvider)
    {
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
    }

    public function isEnabled(string $dataCollectionType, ?Website $website = null): bool
    {
        return (bool)$this->dataCollectionStateProvider->isEnabled($dataCollectionType, $website);
    }
}
