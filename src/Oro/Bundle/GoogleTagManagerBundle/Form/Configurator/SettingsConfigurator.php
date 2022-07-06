<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Form\Configurator;

use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Removes "enabled_data_collection_types" system config field when GTM integration is not set.
 */
class SettingsConfigurator
{
    private ConfigHandler $configHandler;

    public function __construct(ConfigHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                if ($event->getData() === null) {
                    return;
                }

                $form = $event->getForm();
                $configManager = $this->configHandler->getConfigManager();

                if (!$configManager->get(Configuration::getConfigKeyByName('integration'))) {
                    $form->remove(Configuration::getFieldKeyByName('enabled_data_collection_types'));
                }
            }
        );
    }
}
