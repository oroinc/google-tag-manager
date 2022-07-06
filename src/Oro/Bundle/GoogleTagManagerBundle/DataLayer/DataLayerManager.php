<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Collects, store and reset data layer data.
 */
class DataLayerManager
{
    private const KEY = 'oro_google_tag_manager.data_layer';

    /** @var SessionInterface */
    private $session;

    /** @var iterable|CollectorInterface[] */
    private $collectors;

    /**
     * @param SessionInterface $session
     * @param iterable|CollectorInterface[] $collectors
     */
    public function __construct(SessionInterface $session, iterable $collectors)
    {
        $this->session = $session;
        $this->collectors = $collectors;
    }

    /**
     * Appends data to the data layer in the current session.
     *
     * @param array ...$data ['data_name' => 'data_value']
     */
    public function append(array ...$data): void
    {
        $current = $this->session->get(self::KEY, []);
        array_push($current, ...$data);

        $this->session->set(self::KEY, $current);
    }

    /**
     * Prepends data to the data layer in the current session.
     *
     * @param array ...$data ['data_name' => 'data_value']
     */
    public function prepend(array ...$data): void
    {
        $current = $this->session->get(self::KEY, []);
        array_unshift($current, ...$data);

        $this->session->set(self::KEY, $current);
    }

    /**
     * Adds data to the data layer in the current session.
     *
     * @param array $data ['data_name' => 'data_value']
     *
     * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0. Use append instead.
     */
    public function add(array $data): void
    {
        $current = $this->session->get(self::KEY, []);
        $current[] = $data;

        $this->session->set(self::KEY, $current);
    }

    /**
     * Returns all data layer data.
     *
     * @return array [['data_name' => 'data_value']]
     */
    public function collectAll(): array
    {
        $data = new ArrayCollection($this->session->get(self::KEY, []));

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
        $data = $this->session->get(self::KEY, []);

        $result = [];
        foreach ($data as $key => $item) {
            if (!isset($item['event']) || !\in_array($item['event'], $events, true)) {
                continue;
            }

            $result[] = $item;

            unset($data[$key]);
        }

        if ($result) {
            $this->session->set(self::KEY, $data);
        }

        return $result;
    }

    /**
     * Reset data layer data in the current session.
     */
    public function reset(): void
    {
        $this->session->remove(self::KEY);
    }
}
