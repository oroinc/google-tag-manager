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

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var array|CollectorInterface[]
     */
    protected $collectors;

    /**
     * @param SessionInterface $session
     * @param array|CollectorInterface[] $collectors
     */
    public function __construct(SessionInterface $session, array $collectors = [])
    {
        $this->session = $session;
        $this->collectors = $collectors;
    }

    /**
     * Adds data to the data layer in the current session.
     *
     * @param array $data ['data_name' => 'data_value']
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
    public function all(): array
    {
        $data = new ArrayCollection($this->session->get(self::KEY, []));

        foreach ($this->collectors as $collector) {
            $collector->handle($data);
        }

        return $data->toArray();
    }

    /**
     * Reset data layer data in the current session.
     */
    public function reset(): void
    {
        $this->session->remove(self::KEY);
    }
}
