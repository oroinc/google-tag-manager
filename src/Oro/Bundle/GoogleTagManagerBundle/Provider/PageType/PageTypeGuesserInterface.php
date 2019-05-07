<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\PageType;

/**
 * Interface for the page type guessers.
 */
interface PageTypeGuesserInterface
{
    /**
     * @return null|string
     */
    public function getType(): ?string;
}
