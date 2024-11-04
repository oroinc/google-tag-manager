<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\IntegrationSettingsProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProvider;

class IntegrationSettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var GoogleTagManagerSettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var IntegrationSettingsProvider */
    private $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProvider::class);

        $this->provider = new IntegrationSettingsProvider($this->settingsProvider);
    }

    /**
     * @dataProvider getContainerIdDataProvider
     */
    public function testGetContainerId(?GoogleTagManagerSettings $settings, ?string $expected): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($settings);

        $this->assertEquals($expected, $this->provider->getContainerId());

        // second call
        if ($expected) {
            $this->assertEquals($expected, $this->provider->getContainerId());
        }
    }

    public function getContainerIdDataProvider(): array
    {
        $containerId = 'test-container-id';

        $settings = new GoogleTagManagerSettings();
        $settings->setContainerId($containerId);

        return [
            'empty settings' => [
                'settings' => null,
                'expected' => null
            ],
            'not empty settings without container id' => [
                'settings' => new GoogleTagManagerSettings(),
                'expected' => null
            ],
            'not empty settings with container id' => [
                'settings' => $settings,
                'expected' => $containerId
            ],
        ];
    }

    /**
     * @dataProvider isReadyDataProvider
     */
    public function testIsReady(?GoogleTagManagerSettings $settings, bool $expected): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($settings);

        $this->assertEquals($expected, $this->provider->isReady());

        // second call
        if ($expected) {
            $this->assertEquals($expected, $this->provider->isReady());
        }
    }

    public function isReadyDataProvider(): array
    {
        $settings = new GoogleTagManagerSettings();
        $settings->setContainerId('test-container-id');

        return [
            'empty settings' => [
                'settings' => null,
                'expected' => false
            ],
            'not empty settings without container id' => [
                'settings' => new GoogleTagManagerSettings(),
                'expected' => false
            ],
            'not empty settings with container id' => [
                'settings' => $settings,
                'expected' => true
            ],
        ];
    }
}
