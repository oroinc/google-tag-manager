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
     * @var int
     */
    private $batchSize;

    public function __construct(DataLayerManager $dataLayerManager, string $variableName, int $batchSize)
    {
        $this->dataLayerManager = $dataLayerManager;
        $this->variableName = $variableName;
        $this->batchSize = $batchSize;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

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
