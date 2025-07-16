<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DataLayerManagerTest extends TestCase
{
    private const KEY = 'oro_google_tag_manager.data_layer';

    private SessionInterface&MockObject $session;
    private RequestStack&MockObject $requestStack;
    private CollectorInterface&MockObject $collector1;
    private CollectorInterface&MockObject $collector2;
    private DataLayerManager $manager;

    #[\Override]
    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->collector1 = $this->createMock(CollectorInterface::class);
        $this->collector2 = $this->createMock(CollectorInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);

        $this->manager = new DataLayerManager($this->requestStack, [$this->collector1, $this->collector2]);
    }

    /**
     * @dataProvider appendDataProvider
     */
    public function testAppend(array $expected, array ...$data): void
    {
        $original = [['option1' => 'value1'], ['option2' => 'value2']];

        $this->session->expects(self::once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($original);

        $this->session->expects(self::once())
            ->method('set')
            ->with(self::KEY, $expected);

        $this->collector1->expects(self::never())
            ->method(self::anything());

        $this->collector2->expects(self::never())
            ->method(self::anything());

        $this->manager->append(...$data);
    }

    public function appendDataProvider(): array
    {
        return [
            [
                'expected' => [['option1' => 'value1'], ['option2' => 'value2'], ['option3' => 'value3']],
                'data1' => ['option3' => 'value3'],
            ],
            [
                'expected' => [
                    ['option1' => 'value1'],
                    ['option2' => 'value2'],
                    ['option3' => 'value3'],
                    ['option4' => 'value4'],
                ],
                'data1' => ['option3' => 'value3'],
                'data2' => ['option4' => 'value4'],
            ],
        ];
    }

    /**
     * @dataProvider prependDataProvider
     */
    public function testPrepend(array $expected, array ...$data): void
    {
        $original = [['option1' => 'value1'], ['option2' => 'value2']];

        $this->session->expects(self::once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($original);

        $this->session->expects(self::once())
            ->method('set')
            ->with(self::KEY, $expected);

        $this->collector1->expects(self::never())
            ->method(self::anything());

        $this->collector2->expects(self::never())
            ->method(self::anything());

        $this->manager->prepend(...$data);
    }

    public function prependDataProvider(): array
    {
        return [
            [
                'expected' => [['option3' => 'value3'], ['option1' => 'value1'], ['option2' => 'value2']],
                'data1' => ['option3' => 'value3'],
            ],
            [
                'expected' => [
                    ['option3' => 'value3'],
                    ['option4' => 'value4'],
                    ['option1' => 'value1'],
                    ['option2' => 'value2'],
                ],
                'data1' => ['option3' => 'value3'],
                'data2' => ['option4' => 'value4'],
            ],
        ];
    }

    public function testCollectAll(): void
    {
        $original = [['option1' => 'value1']];
        $data1 = ['option2' => 'value2'];
        $data2 = ['option3' => 'value3'];

        $this->session->expects(self::once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($original);

        $this->collector1->expects(self::once())
            ->method('handle')
            ->willReturnCallback(function (ArrayCollection $data) use ($original, $data1) {
                $this->assertEquals(new ArrayCollection($original), $data);

                $data->add($data1);

                return $data;
            });

        $this->collector2->expects(self::once())
            ->method('handle')
            ->willReturnCallback(function (ArrayCollection $data) use ($original, $data1, $data2) {
                $this->assertEquals(new ArrayCollection(array_merge($original, [$data1])), $data);

                $data->add($data2);

                return $data;
            });

        self::assertEquals(array_merge($original, [$data1], [$data2]), $this->manager->collectAll());
    }

    public function testGetForEvents(): void
    {
        $data = [
            ['event' => 'test_event1', 'option1' => 'value1'],
            ['option2' => 'value2'],
            ['event' => 'test_event2', 'option3' => 'value3'],
        ];

        $this->session->expects(self::once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($data);

        $this->collector1->expects(self::never())
            ->method('handle');

        $this->collector2->expects(self::never())
            ->method('handle');

        self::assertEquals([$data[2]], $this->manager->getForEvents(['test_event2']));
    }

    public function testReset(): void
    {
        $this->session->expects(self::once())
            ->method('remove')
            ->with(self::KEY);

        $this->manager->reset();
    }
}
