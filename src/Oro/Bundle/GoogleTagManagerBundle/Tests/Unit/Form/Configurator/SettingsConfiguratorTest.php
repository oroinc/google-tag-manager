<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Form\Configurator;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Oro\Bundle\GoogleTagManagerBundle\Form\Configurator\SettingsConfigurator;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SettingsConfiguratorTest extends FormIntegrationTestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private ConfigManager $configManager;

    private SettingsConfigurator $configurator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configManager = $this->createMock(ConfigManager::class);

        $configHandler = $this->createMock(ConfigHandler::class);
        $configHandler
            ->method('getConfigManager')
            ->willReturn($this->configManager);

        $this->configurator = new SettingsConfigurator($configHandler);
    }

    public function testBuildFormWhenNoData(): void
    {
        $callable = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->expects(self::once())
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $eventName, callable $listener, $priority) use (&$callable) {
                    $this->assertEquals(FormEvents::PRE_SET_DATA, $eventName);
                    $this->assertEquals(0, $priority);

                    $callable = $listener;
                }
            );

        $this->configurator->buildForm($builder);

        $form = $this->factory->create(FormType::class);
        $form->add('oro_google_tag_manager___enabled_data_collection_types', Select2ChoiceType::class);

        $this->configManager
            ->expects(self::never())
            ->method(self::anything());

        /** @var callable $callable */
        $callable(new FormEvent($form, null));

        self::assertTrue($form->has('oro_google_tag_manager___enabled_data_collection_types'));
    }

    /**
     * @dataProvider buildFormDataProvider
     */
    public function testBuildForm(?int $integrationId, bool $expected): void
    {
        $callable = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->expects(self::once())
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $eventName, callable $listener, $priority) use (&$callable) {
                    $this->assertEquals(FormEvents::PRE_SET_DATA, $eventName);
                    $this->assertEquals(0, $priority);

                    $callable = $listener;
                }
            );

        $this->configurator->buildForm($builder);

        $form = $this->factory->create(FormType::class);
        $form->add('oro_google_tag_manager___enabled_data_collection_types', Select2ChoiceType::class);

        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName('integration'), false, false, null)
            ->willReturn($integrationId);

        /** @var callable $callable */
        $callable(new FormEvent($form, ['sample_key' => 'sample_value']));

        self::assertSame($expected, $form->has('oro_google_tag_manager___enabled_data_collection_types'));
    }

    public function buildFormDataProvider(): array
    {
        return [
            ['integrationId' => null, 'expected' => false],
            ['integrationId' => 42, 'expected' => true],
        ];
    }
}
