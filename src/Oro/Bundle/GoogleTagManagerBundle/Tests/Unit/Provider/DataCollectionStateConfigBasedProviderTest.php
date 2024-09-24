<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateConfigBasedProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class DataCollectionStateConfigBasedProviderTest extends \PHPUnit\Framework\TestCase
{
    private ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager;

    private DataCollectionStateConfigBasedProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->provider = new DataCollectionStateConfigBasedProvider($this->configManager);
    }

    /**
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled(?array $enabledTypes, ?Website $website, bool $expected): void
    {
        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName('enabled_data_collection_types'), false, false, $website)
            ->willReturn($enabledTypes);

        self::assertSame($expected, $this->provider->isEnabled('sample_type', $website));
    }

    public function isEnabledDataProvider(): array
    {
        return [
            [
                'enabledTypes' => null,
                'website' => null,
                'expected' => false,
            ],
            [
                'enabledTypes' => [],
                'website' => new Website(),
                'expected' => false,
            ],
            [
                'enabledTypes' => ['sample_type2'],
                'website' => null,
                'expected' => false,
            ],
            [
                'enabledTypes' => ['sample_type2'],
                'website' => new Website(),
                'expected' => false,
            ],

            [
                'enabledTypes' => ['sample_type'],
                'website' => null,
                'expected' => true,
            ],
            [
                'enabledTypes' => ['sample_type'],
                'website' => new Website(),
                'expected' => true,
            ],
        ];
    }
}
