<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Integration;

use Oro\Bundle\GoogleTagManagerBundle\Integration\GoogleTagManagerChannel;

class GoogleTagManagerChannelTest extends \PHPUnit\Framework\TestCase
{
    /** @var GoogleTagManagerChannel */
    private $channel;

    protected function setUp()
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
