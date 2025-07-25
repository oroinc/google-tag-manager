<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\PageType\PageTypeGuesserInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\PageTypeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageTypeProviderTest extends TestCase
{
    private PageTypeGuesserInterface&MockObject $guesser1;
    private PageTypeGuesserInterface&MockObject $guesser2;
    private PageTypeProvider $provider;

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
