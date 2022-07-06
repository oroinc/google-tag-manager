<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class GoogleTagManagerSettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    private const CONFIG_KEY = 'oro_google_tag_manager.integration';

    private ChannelRepository|\PHPUnit\Framework\MockObject\MockObject $repository;

    private ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager;

    private GoogleTagManagerSettingsProvider $provider;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ChannelRepository::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::any())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($this->repository);

        $this->configManager = $this->createMock(ConfigManager::class);

        $this->provider = new GoogleTagManagerSettingsProvider($registry, $this->configManager);
    }

    public function testGetGoogleTagManagerSettingsWithEmptyConfig(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, null)
            ->willReturn(null);

        $this->repository->expects(self::never())
            ->method(self::anything());

        self::assertNull($this->provider->getGoogleTagManagerSettings());
    }

    public function testGetGoogleTagManagerSettingsWhenChannelNotFound(): void
    {
        $website = new Website();
        $integrationId = 42;

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects(self::once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn(null);

        self::assertNull($this->provider->getGoogleTagManagerSettings($website));
    }

    public function testGetGoogleTagManagerSettingsWhenChannelDisabled(): void
    {
        $website = new Website();
        $integrationId = 42;

        $channel = new Channel();
        $channel->setEnabled(false);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects(self::once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn($channel);

        self::assertNull($this->provider->getGoogleTagManagerSettings($website));
    }

    public function testGetGoogleTagManagerSettingsWhenChannelWithoutTransport(): void
    {
        $website = new Website();
        $integrationId = 42;

        $channel = new Channel();
        $channel->setEnabled(true);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects(self::once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn($channel);

        self::assertNull($this->provider->getGoogleTagManagerSettings($website));
    }

    public function testGetGoogleTagManagerSettingsWhenChannelWithTransport(): void
    {
        $website = new Website();
        $integrationId = 42;
        $transport = new GoogleTagManagerSettings();

        $channel = new Channel();
        $channel->setEnabled(true);
        $channel->setTransport($transport);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects(self::once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn($channel);

        self::assertSame($transport, $this->provider->getGoogleTagManagerSettings($website));
    }
}
