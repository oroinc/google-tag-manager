<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\PageType;

use Symfony\Component\HttpFoundation\RequestStack;

class PageTypeGuesserByRequestRoute implements PageTypeGuesserInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array, [..., routeName => pageType, ...]
     */
    private $routeToPageTypeMap;

    /**
     * @param RequestStack $requestStack
     * @param array $routeToPageTypeMap
     */
    public function __construct(RequestStack $requestStack, array $routeToPageTypeMap)
    {
        $this->requestStack = $requestStack;
        $this->routeToPageTypeMap = $routeToPageTypeMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): ?string
    {
        return $this->routeToPageTypeMap[$this->getRouteName()] ?? null;
    }

    /**
     * @return null|string
     */
    private function getRouteName(): ?string
    {
        return $this->requestStack->getCurrentRequest()->attributes->get('_route');
    }
}
