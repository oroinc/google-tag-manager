<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface for the data layer collector.
 */
interface CollectorInterface
{
    /**
     * @param ArrayCollection $data
     * @return void
     */
    public function handle(ArrayCollection $data): void;
}
