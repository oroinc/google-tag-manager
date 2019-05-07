<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\PageType\PageTypeGuesserInterface;

class PageTypeProvider
{
    /**
     * @var array PageTypeGuesserInterface[]
     */
    private $guessers = [];

    /**
     * @param PageTypeGuesserInterface $guesser
     */
    public function addGuesser(PageTypeGuesserInterface $guesser): void
    {
        $this->guessers[] = $guesser;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        foreach ($this->guessers as $guesser) {
            $type = $guesser->getType();
            if ($type) {
                return $type;
            }
        }

        return null;
    }
}
