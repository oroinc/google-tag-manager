<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\PageType;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns page type based on the current request.
 */
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

    public function __construct(RequestStack $requestStack, array $routeToPageTypeMap)
    {
        $this->requestStack = $requestStack;
        $this->routeToPageTypeMap = $routeToPageTypeMap;
    }

    #[\Override]
    public function getType(): ?string
    {
        return $this->routeToPageTypeMap[$this->getRouteName()] ?? null;
    }

    private function getRouteName(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request ? $request->attributes->get('_route') : null;
    }
}
