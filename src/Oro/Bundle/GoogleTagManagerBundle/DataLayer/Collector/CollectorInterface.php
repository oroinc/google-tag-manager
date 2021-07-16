<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for the data layer collector.
 */
interface CollectorInterface
{
    public function handle(Collection $data): void;
}
