<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\PageType\PageTypeGuesserInterface;

/**
 * Provides page type.
 */
class PageTypeProvider
{
    /**
     * @var array|PageTypeGuesserInterface[]
     */
    private $guessers;

    /**
     * @param array|PageTypeGuesserInterface[] $guessers
     */
    public function __construct(array $guessers = [])
    {
        $this->guessers = $guessers;
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
