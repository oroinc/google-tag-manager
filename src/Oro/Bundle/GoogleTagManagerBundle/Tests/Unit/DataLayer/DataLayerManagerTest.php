<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DataLayerManagerTest extends \PHPUnit\Framework\TestCase
{
    private const KEY = 'oro_google_tag_manager.data_layer';

    /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    /** @var CollectorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $collector1;

    /** @var CollectorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $collector2;

    /** @var DataLayerManager */
    private $manager;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->collector1 = $this->createMock(CollectorInterface::class);
        $this->collector2 = $this->createMock(CollectorInterface::class);

        $this->manager = new DataLayerManager($this->session, [$this->collector1, $this->collector2]);
    }

    public function testAdd(): void
    {
        $original = [['option1' => 'value1'], ['option2' => 'value2']];
        $data = ['option3' => 'value3'];
        $expected = array_merge($original, [$data]);

        $this->session->expects($this->once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($original);

        $this->session->expects($this->once())
            ->method('set')
            ->with(self::KEY, $expected);

        $this->collector1->expects($this->never())
            ->method($this->anything());

        $this->collector2->expects($this->never())
            ->method($this->anything());

        $this->manager->add($data);
    }

    public function testCollectAll(): void
    {
        $original = [['option1' => 'value1']];
        $data1 = ['option2' => 'value2'];
        $data2 = ['option3' => 'value3'];

        $this->session->expects($this->once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($original);

        $this->collector1->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function (ArrayCollection $data) use ($original, $data1) {
                    $this->assertEquals(new ArrayCollection($original), $data);

                    $data->add($data1);

                    return $data;
                }
            );

        $this->collector2->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function (ArrayCollection $data) use ($original, $data1, $data2) {
                    $this->assertEquals(new ArrayCollection(array_merge($original, [$data1])), $data);

                    $data->add($data2);

                    return $data;
                }
            );

        $this->assertEquals(array_merge($original, [$data1], [$data2]), $this->manager->collectAll());
    }

    public function testGetForEvents(): void
    {
        $data = [
            ['event' => 'test_event1', 'option1' => 'value1'],
            ['option2' => 'value2'],
            ['event' => 'test_event2', 'option3' => 'value3'],
        ];

        $this->session->expects($this->once())
            ->method('get')
            ->with(self::KEY, [])
            ->willReturn($data);

        $this->collector1->expects($this->never())
            ->method('handle');

        $this->collector2->expects($this->never())
            ->method('handle');

        $this->assertEquals([$data[2]], $this->manager->getForEvents(['test_event2']));
    }

    public function testReset(): void
    {
        $this->session->expects($this->once())
            ->method('remove')
            ->with(self::KEY);

        $this->manager->reset();
    }
}
