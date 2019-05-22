<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;

/**
 * Layout data provider for data layer.
 */
class DataLayerProvider
{
    /**
     * @var DataLayerManager
     */
    private $dataLayerManager;

    /**
     * @var string
     */
    private $variableName;

    /**
     * @param DataLayerManager $dataLayerManager
     * @param string $variableName
     */
    public function __construct(DataLayerManager $dataLayerManager, string $variableName)
    {
        $this->dataLayerManager = $dataLayerManager;
        $this->variableName = $variableName;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @param array $events
     * @return array
     */
    public function getData(array $events = []): array
    {
        if ($events) {
            $data = $this->dataLayerManager->getForEvents($events);
        } else {
            $data = $this->dataLayerManager->collectAll();
            $this->dataLayerManager->reset();
        }

        return $this->filterEmptyData($data);
    }

    /**
     * Filters nullable attributes. Skip whole config item if all elements are null.
     *
     * @param array $config
     * @return array
     */
    private function filterEmptyData(array $config): array
    {
        foreach ($config as $configItems) {
            $resultConfigItems = array_filter($configItems);

            if ($resultConfigItems) {
                $resultConfig[] = $resultConfigItems;
            }
        }

        return $resultConfig ?? [];
    }
}
