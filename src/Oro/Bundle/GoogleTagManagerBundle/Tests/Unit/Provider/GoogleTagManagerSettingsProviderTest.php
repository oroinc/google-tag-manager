<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class GoogleTagManagerSettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    private const CONFIG_KEY = 'oro_google_tag_manager.integration';

    /** @var ChannelRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var GoogleTagManagerSettingsProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ChannelRepository::class);

        /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($this->repository);

        /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Channel::class)
            ->willReturn($objectManager);

        $this->configManager = $this->createMock(ConfigManager::class);

        $this->provider = new GoogleTagManagerSettingsProvider($registry, $this->configManager);
    }

    public function testGetGoogleTagManagerSettingsWithEmptyConfig(): void
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, null)
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method($this->anything());

        $this->assertNull($this->provider->getGoogleTagManagerSettings());
    }

    public function testGetGoogleTagManagerSettingsWhenChannelNotFound(): void
    {
        $website = new Website();
        $integrationId = 42;

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects($this->once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn(null);

        $this->assertNull($this->provider->getGoogleTagManagerSettings($website));
    }

    public function testGetGoogleTagManagerSettingsWhenChannelDisabled(): void
    {
        $website = new Website();
        $integrationId = 42;

        $channel = new Channel();
        $channel->setEnabled(false);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects($this->once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn($channel);

        $this->assertNull($this->provider->getGoogleTagManagerSettings($website));
    }

    public function testGetGoogleTagManagerSettingsWhenChannelWithoutTransport(): void
    {
        $website = new Website();
        $integrationId = 42;

        $channel = new Channel();
        $channel->setEnabled(true);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects($this->once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn($channel);

        $this->assertNull($this->provider->getGoogleTagManagerSettings($website));
    }

    public function testGetGoogleTagManagerSettingsWhenChannelWithTransport(): void
    {
        $website = new Website();
        $integrationId = 42;
        $transport = new GoogleTagManagerSettings();

        $channel = new Channel();
        $channel->setEnabled(true);
        $channel->setTransport($transport);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_KEY, false, false, $website)
            ->willReturn($integrationId);

        $this->repository->expects($this->once())
            ->method('getOrLoadById')
            ->with($integrationId)
            ->willReturn($channel);

        $this->assertSame($transport, $this->provider->getGoogleTagManagerSettings($website));
    }
}
