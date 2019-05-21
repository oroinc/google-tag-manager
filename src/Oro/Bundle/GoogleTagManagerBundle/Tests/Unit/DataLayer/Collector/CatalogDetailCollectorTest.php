<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CatalogDetailCollector;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\WebCatalogBundle\Layout\DataProvider\WebCatalogBreadcrumbProvider;

class CatalogDetailCollectorTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebCatalogBreadcrumbProvider|\PHPUnit\Framework\MockObject\MockObject*/
    private $webCatalogBreadcrumbProvider;

    /** @var CatalogDetailCollector */
    private $collector;

    protected function setUp()
    {
        $this->webCatalogBreadcrumbProvider = $this->createMock(WebCatalogBreadcrumbProvider::class);
        $this->collector = new CatalogDetailCollector($this->webCatalogBreadcrumbProvider);
    }

    /**
     * @dataProvider handleDataProvider
     *
     * @param array $items
     * @param array $excepted
     */
    public function testHandle(array $items, array $excepted): void
    {
        $this->webCatalogBreadcrumbProvider->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $data = new ArrayCollection();
        $this->collector->handle($data);
        $this->assertSame($excepted, $data->toArray());
    }

    /**
     * @return array
     */
    public function handleDataProvider(): array
    {
        return [
            'empty path' => [
                'items' => [],
                'excepted' => [],
            ],
            'single node' => [
                'items' => [
                    ['label' => 'Single Node']
                ],
                'excepted' => [
                    [DataLayerAttributeBag::KEY_CATALOG_PATH => 'Single Node']
                ],
            ],
            'multiple nodes' => [
                'items' => [
                    ['label' => 'First Node'],
                    ['label' => 'Second Node'],
                    ['label' => 'Third Node'],
                ],
                'excepted' => [
                    [DataLayerAttributeBag::KEY_CATALOG_PATH => 'First Node / Second Node / Third Node'],
                ],
            ],
        ];
    }
}
