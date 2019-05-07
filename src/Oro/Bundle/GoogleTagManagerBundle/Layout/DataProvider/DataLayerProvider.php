<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;

/**
 * Layout data provider for data layer.
 */
class DataLayerProvider
{
    public const DEFAULT_BATCH_SIZE = 30;

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
     * @param bool $reset
     * @return array
     */
    public function getData(bool $reset = false): array
    {
        $data = $this->dataLayerManager->all();

        if ($reset) {
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
            $resultConfigItems = array_filter(
                $configItems,
                static function ($configItem) {
                    return $configItem !== null;
                }
            );

            if ($resultConfigItems) {
                $resultConfig[] = $resultConfigItems;
            }
        }

        return $resultConfig ?? [];
    }
}
