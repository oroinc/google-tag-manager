<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\Provider\PageTypeProvider;

/**
 * Adds page related information for data layer.
 */
class PageVariablesCollector implements CollectorInterface
{
    /**
     * @var PageTypeProvider
     */
    private $pageTypeProvider;

    /**
     * @param PageTypeProvider $pageTypeProvider
     */
    public function __construct(PageTypeProvider $pageTypeProvider)
    {
        $this->pageTypeProvider = $pageTypeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ArrayCollection $data): void
    {
        $data->add(
            [
                'page' => [
                    'type' => $this->pageTypeProvider->getType(),
                ]
            ]
        );
    }
}
