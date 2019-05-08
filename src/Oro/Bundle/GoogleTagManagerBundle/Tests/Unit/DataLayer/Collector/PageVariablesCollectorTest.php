<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\PageVariablesCollector;
use Oro\Bundle\GoogleTagManagerBundle\Provider\PageTypeProvider;

class PageVariablesCollectorTest extends \PHPUnit\Framework\TestCase
{
    /** @var PageTypeProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $pageTypeProvider;

    /** @var PageVariablesCollector */
    private $collector;

    protected function setUp()
    {
        $this->pageTypeProvider = $this->createMock(PageTypeProvider::class);

        $this->collector = new PageVariablesCollector($this->pageTypeProvider);
    }

    /**
     * @dataProvider handleDataProvider
     *
     * @param string|null $testType
     * @param array $expected
     */
    public function testHandle(?string $testType, array $expected): void
    {
        $this->pageTypeProvider->expects($this->once())
            ->method('getType')
            ->willReturn($testType);

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals($expected, $data[0]);
    }

    /**
     * @return array
     */
    public function handleDataProvider(): array
    {
        return [
            'with type' => [
                'type' => 'test_type',
                'expected' => [
                    'page' => [
                        'type' => 'test_type',
                    ]
                ]
            ],
            'without type' => [
                'type' => null,
                'expected' => [
                    'page' => [
                        'type' => null,
                    ]
                ]
            ],
        ];
    }
}
