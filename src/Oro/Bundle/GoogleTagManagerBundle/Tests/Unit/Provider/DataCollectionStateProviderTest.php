<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Psr\Log\LoggerInterface;

class DataCollectionStateProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $provider1;

    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $provider2;

    /** @var DataCollectionStateProvider */
    private $dataCollectionStateProvider;

    protected function setUp(): void
    {
        $this->googleTagManagerSettingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);
        $this->provider1 = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->provider2 = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->dataCollectionStateProvider = new DataCollectionStateProvider(
            [$this->provider1, $this->provider2],
            $this->googleTagManagerSettingsProvider,
            $this->logger
        );
    }

    /**
     * @dataProvider getGoogleTagManagerDataProvider
     */
    public function testGetGoogleTagManagerSettings(?Website $website, ?Transport $expected): void
    {
        $this->googleTagManagerSettingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->with($website)
            ->willReturn($expected);

        self::assertSame($expected, $this->dataCollectionStateProvider->getGoogleTagManagerSettings($website));
    }

    public function getGoogleTagManagerDataProvider(): array
    {
        return [
            ['website' => null, 'expected' => null],
            ['website' => null, 'expected' => new GoogleTagManagerSettings()],
            ['website' => new Website(), 'expected' => null],
            ['website' => new Website(), 'expected' => new GoogleTagManagerSettings()],
        ];
    }

    /**
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabledWhenNoSettings(?Website $website, ?Transport $settings): void
    {
        $this->googleTagManagerSettingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->with($website)
            ->willReturn($settings);

        $this->provider1->expects(self::never())
            ->method(self::anything());

        $this->provider2->expects(self::never())
            ->method(self::anything());

        self::assertFalse($this->dataCollectionStateProvider->isEnabled('sample_type', $website));
    }

    public function isEnabledDataProvider(): array
    {
        return [
            ['website' => null, 'settings' => null],
            ['website' => null, 'settings' => new GoogleTagManagerSettings()],
            ['website' => new Website(), 'settings' => null],
            ['website' => new Website(), 'settings' => new GoogleTagManagerSettings()],
        ];
    }

    public function testIsEnabledWhenNotSupported(): void
    {
        $website = new Website();
        $gtmSettings = (new GoogleTagManagerSettings())
            ->setContainerId('sample-id');

        $this->googleTagManagerSettingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->with($website)
            ->willReturn($gtmSettings);

        $dataCollectionType = 'sample_type';
        $this->provider1->expects(self::once())
            ->method('isEnabled')
            ->with($dataCollectionType, $website)
            ->willReturn(null);

        $this->provider2->expects(self::once())
            ->method('isEnabled')
            ->with($dataCollectionType, $website)
            ->willReturn(null);

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Google Tag Manager data collection type "{type}" is not supported',
                ['type' => $dataCollectionType, 'website' => $website]
            );

        self::assertFalse($this->dataCollectionStateProvider->isEnabled($dataCollectionType, $website));
    }

    public function testIsEnabled(): void
    {
        $website = new Website();
        $gtmSettings = (new GoogleTagManagerSettings())
            ->setContainerId('sample-id');

        $this->googleTagManagerSettingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->with($website)
            ->willReturn($gtmSettings);

        $dataCollectionType = 'sample_type';
        $this->provider1->expects(self::once())
            ->method('isEnabled')
            ->with($dataCollectionType, $website)
            ->willReturn(null);

        $this->provider2->expects(self::once())
            ->method('isEnabled')
            ->with($dataCollectionType, $website)
            ->willReturn(true);

        self::assertTrue($this->dataCollectionStateProvider->isEnabled($dataCollectionType, $website));
    }
}
