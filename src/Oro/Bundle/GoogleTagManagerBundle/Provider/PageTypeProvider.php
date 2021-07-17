<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\PageType\PageTypeGuesserInterface;

/**
 * Provides page type.
 */
class PageTypeProvider
{
    /** @var iterable|PageTypeGuesserInterface[] */
    private $guessers;

    /**
     * @param iterable|PageTypeGuesserInterface[] $guessers
     */
    public function __construct(iterable $guessers)
    {
        $this->guessers = $guessers;
    }

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
