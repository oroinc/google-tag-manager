<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\PageType\PageTypeGuesserInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\PageTypeProvider;

class PageTypeProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var PageTypeGuesserInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $guesser1;

    /** @var PageTypeGuesserInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $guesser2;

    /** @var PageTypeProvider */
    private $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->guesser1 = $this->createMock(PageTypeGuesserInterface::class);
        $this->guesser2 = $this->createMock(PageTypeGuesserInterface::class);

        $this->provider = new PageTypeProvider([$this->guesser1, $this->guesser2]);
    }

    public function testGetTypeForFirstGuesser(): void
    {
        $this->guesser1->expects($this->once())
            ->method('getType')
            ->willReturn('test-type-1');

        $this->guesser2->expects($this->never())
            ->method('getType');

        $this->assertEquals('test-type-1', $this->provider->getType());
    }

    public function testGetTypeForSecondGuesser(): void
    {
        $this->guesser1->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        $this->guesser2->expects($this->once())
            ->method('getType')
            ->willReturn('test-type-2');

        $this->assertEquals('test-type-2', $this->provider->getType());
    }

    public function testGetTypeForNoGuesser(): void
    {
        $this->guesser1->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        $this->guesser2->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        $this->assertNull($this->provider->getType());
    }
}
