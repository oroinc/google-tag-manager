<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Integration;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Form\Type\GoogleTagManagerSettingsType;
use Oro\Bundle\GoogleTagManagerBundle\Integration\GoogleTagManagerTransport;

class GoogleTagManagerTransportTest extends \PHPUnit\Framework\TestCase
{
    /** @var GoogleTagManagerTransport */
    private $transport;

    #[\Override]
    protected function setUp(): void
    {
        $this->transport = new GoogleTagManagerTransport();
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('oro.google_tag_manager.integration.transport.label', $this->transport->getLabel());
    }

    public function testGetSettingsFormType(): void
    {
        $this->assertEquals(GoogleTagManagerSettingsType::class, $this->transport->getSettingsFormType());
    }

    public function testGetSettingsEntityFQCN(): void
    {
        $this->assertEquals(GoogleTagManagerSettings::class, $this->transport->getSettingsEntityFQCN());
    }
}
