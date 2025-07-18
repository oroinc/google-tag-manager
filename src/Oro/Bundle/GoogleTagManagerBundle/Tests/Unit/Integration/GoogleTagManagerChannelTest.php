<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Integration;

use Oro\Bundle\GoogleTagManagerBundle\Integration\GoogleTagManagerChannel;
use PHPUnit\Framework\TestCase;

class GoogleTagManagerChannelTest extends TestCase
{
    private GoogleTagManagerChannel $channel;

    #[\Override]
    protected function setUp(): void
    {
        $this->channel = new GoogleTagManagerChannel();
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('oro.google_tag_manager.integration.channel.label', $this->channel->getLabel());
    }

    public function testGetIcon(): void
    {
        $this->assertEquals('bundles/orogoogletagmanager/img/gtm-icon.png', $this->channel->getIcon());
    }
}
