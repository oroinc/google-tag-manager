<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\PageVariablesCollector;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\GoogleTagManagerBundle\Provider\PageTypeProvider;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageVariablesCollectorTest extends TestCase
{
    use EntityTrait;

    private PageTypeProvider&MockObject $pageTypeProvider;
    private LocalizationHelper&MockObject $localizationHelper;
    private PageVariablesCollector $collector;

    #[\Override]
    protected function setUp(): void
    {
        $this->pageTypeProvider = $this->createMock(PageTypeProvider::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);

        $this->collector = new PageVariablesCollector($this->pageTypeProvider, $this->localizationHelper);
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(?string $type, ?Localization $localization, array $expected): void
    {
        $this->pageTypeProvider->expects($this->once())
            ->method('getType')
            ->willReturn($type);

        $this->localizationHelper->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals($expected, $data->toArray());
    }

    public function handleDataProvider(): array
    {
        $localization = $this->getEntity(Localization::class, ['id' => 42]);

        return [
            'with type' => [
                'type' => 'test_type',
                'localization' => $localization,
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_PAGE_TYPE => 'test_type',
                        DataLayerAttributeBag::KEY_LOCALIZATION_ID => '42',
                    ]
                ]
            ],
            'without type' => [
                'type' => null,
                'localization' => null,
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_PAGE_TYPE => null,
                        DataLayerAttributeBag::KEY_LOCALIZATION_ID => null,
                    ]
                ]
            ],
        ];
    }
}
