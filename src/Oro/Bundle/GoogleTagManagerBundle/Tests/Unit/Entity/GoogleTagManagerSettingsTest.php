<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Entity;

use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class GoogleTagManagerSettingsTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    /** @var GoogleTagManagerSettings */
    private $entity;

    protected function setUp()
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
