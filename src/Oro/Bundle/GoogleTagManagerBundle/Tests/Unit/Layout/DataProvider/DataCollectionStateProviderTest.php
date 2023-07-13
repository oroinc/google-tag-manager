<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\DataCollectionStateProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class DataCollectionStateProviderTest extends \PHPUnit\Framework\TestCase
{
    private DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject $dataCollectionStateProvider;

    private DataCollectionStateProvider $provider;

    protected function setUp(): void
    {
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

        $this->provider = new DataCollectionStateProvider($this->dataCollectionStateProvider);
    }

    /**
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled(bool $isEnabled, ?Website $website): void
    {
        $this->dataCollectionStateProvider
            ->expects(self::once())
            ->method('isEnabled')
            ->with('sample_tag_type')
            ->willReturn($isEnabled);

        self::assertSame($isEnabled, $this->provider->isEnabled('sample_tag_type', $website));
    }

    public function isEnabledDataProvider(): array
    {
        return [[true, null], [false, null], [true, new Website()], [false, new Website()]];
    }
}
