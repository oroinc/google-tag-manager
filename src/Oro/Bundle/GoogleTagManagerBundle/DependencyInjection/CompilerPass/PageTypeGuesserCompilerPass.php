<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers all page type guessers.
 */
class PageTypeGuesserCompilerPass implements CompilerPassInterface
{
    private const SERVICE_ID = 'oro_google_tag_manager.provider.page_type';
    private const TAG_NAME = 'oro_google_tag_manager.page_type_guesser';

    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_ID)) {
            return;
        }

        $container->getDefinition(self::SERVICE_ID)
            ->replaceArgument(0, $this->findAndSortTaggedServices(self::TAG_NAME, $container));
    }
}
