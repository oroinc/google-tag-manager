<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects, store and reset data layer data.
 */
class DataLayerManager
{
    private const KEY = 'oro_google_tag_manager.data_layer';

    /**
     * @param RequestStack $requestStack
     * @param iterable|CollectorInterface[] $collectors
     */
    public function __construct(private RequestStack $requestStack, private iterable $collectors)
    {
    }

    /**
     * Appends data to the data layer in the current session.
     *
     * @param array ...$data ['data_name' => 'data_value']
     */
    public function append(array ...$data): void
    {
        $session = $this->requestStack->getSession();
        $current = $session->get(self::KEY, []);
        array_push($current, ...$data);

        $session->set(self::KEY, $current);
    }

    /**
     * Prepends data to the data layer in the current session.
     *
     * @param array ...$data ['data_name' => 'data_value']
     */
    public function prepend(array ...$data): void
    {
        $session = $this->requestStack->getSession();
        $current = $session->get(self::KEY, []);
        array_unshift($current, ...$data);

        $session->set(self::KEY, $current);
    }

    /**
     * Returns all data layer data.
     *
     * @return array [['data_name' => 'data_value']]
     */
    public function collectAll(): array
    {
        $data = new ArrayCollection($this->requestStack->getSession()->get(self::KEY, []));

        foreach ($this->collectors as $collector) {
            $collector->handle($data);
        }

        return $data->toArray();
    }

    /**
     * @param array $events
     * @return array [['data_name' => 'data_value']]
     */
    public function getForEvents(array $events): array
    {
        $session = $this->requestStack->getSession();
        $data = $session->get(self::KEY, []);

        $result = [];
        foreach ($data as $key => $item) {
            if (!isset($item['event']) || !\in_array($item['event'], $events, true)) {
                continue;
            }

            $result[] = $item;

            unset($data[$key]);
        }

        if ($result) {
            $session->set(self::KEY, $data);
        }

        return $result;
    }

    /**
     * Reset data layer data in the current session.
     */
    public function reset(): void
    {
        $this->requestStack->getSession()->remove(self::KEY);
    }
}
