<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\PageType;

/**
 * Interface for the page type guessers.
 */
interface PageTypeGuesserInterface
{
    public function getType(): ?string;
}
