<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\PageType;

use Oro\Bundle\GoogleTagManagerBundle\Provider\PageType\PageTypeGuesserByRequestRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PageTypeGuesserByRequestRouteTest extends \PHPUnit\Framework\TestCase
{
    /** @var Request */
    private $request;

    /** @var PageTypeGuesserByRequestRoute */
    private $guesser;

    protected function setUp(): void
    {
        $this->request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->guesser = new PageTypeGuesserByRequestRoute($requestStack, ['oro_test_route' => 'matched_type']);
    }

    /**
     * @dataProvider getTypeDataProvider
     *
     * @param string|null $testRoute
     * @param string|null $expected
     */
    public function testGetType(?string $testRoute, ?string $expected): void
    {
        $this->request->attributes->set('_route', $testRoute);

        $this->assertEquals($expected, $this->guesser->getType());
    }

    /**
     * @return array
     */
    public function getTypeDataProvider(): array
    {
        return [
            'route exists' => [
                'route' => 'oro_test_route',
                'expected' => 'matched_type',
            ],
            'route does not exist' => [
                'route' => 'unknown_route',
                'expected' => null,
            ],
            'no route' => [
                'route' => null,
                'expected' => null,
            ],
            'empty route' => [
                'route' => '',
                'expected' => null,
            ]
        ];
    }
}
