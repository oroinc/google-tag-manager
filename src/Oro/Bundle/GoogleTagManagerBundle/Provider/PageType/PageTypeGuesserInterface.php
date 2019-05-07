<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\PageType;

interface PageTypeGuesserInterface
{
    /**
     * @return null|string
     */
    public function getType(): ?string;
}
