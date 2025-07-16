<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Entity;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class GoogleTagManagerSettingsTest extends TestCase
{
    use EntityTestCaseTrait;

    private GoogleTagManagerSettings $entity;

    #[\Override]
    protected function setUp(): void
    {
        $this->entity = new GoogleTagManagerSettings();
    }

    public function testAccessors(): void
    {
        $this->assertPropertyAccessors(
            $this->entity,
            [
                ['containerId', 'test_container'],
            ]
        );
    }

    public function testGetSettingsBag(): void
    {
        $this->entity->setContainerId('test_container');

        $this->assertEquals(new ParameterBag(['container_id' => 'test_container']), $this->entity->getSettingsBag());
    }
}
