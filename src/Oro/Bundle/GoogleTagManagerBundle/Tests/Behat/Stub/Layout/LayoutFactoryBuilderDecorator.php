<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Stub\Layout;

use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\IntegrationSettingsProvider;
use Oro\Bundle\LayoutBundle\Layout\Context\LayoutContextStack;
use Oro\Component\Layout\BlockTypeExtensionInterface;
use Oro\Component\Layout\BlockTypeInterface;
use Oro\Component\Layout\BlockViewCache;
use Oro\Component\Layout\ExpressionLanguage\ExpressionProcessor;
use Oro\Component\Layout\Extension\ExtensionInterface;
use Oro\Component\Layout\LayoutFactoryBuilderInterface;
use Oro\Component\Layout\LayoutRendererInterface;
use Oro\Component\Layout\LayoutUpdateInterface;

class LayoutFactoryBuilderDecorator implements LayoutFactoryBuilderInterface
{
    private LayoutFactoryBuilderInterface $inner;

    private IntegrationSettingsProvider $gtmSettingsProvider;

    private ExpressionProcessor $expressionProcessor;

    private ApplicationState $applicationState;

    private LayoutContextStack $layoutContextStack;

    private ?BlockViewCache $blockViewCache;

    public function __construct(
        LayoutFactoryBuilderInterface $inner,
        IntegrationSettingsProvider $gtmSettingsProvider,
        ExpressionProcessor $expressionProcessor,
        ApplicationState $applicationState,
        LayoutContextStack $layoutContextStack,
        ?BlockViewCache $blockViewCache = null
    ) {
        $this->inner = $inner;
        $this->gtmSettingsProvider = $gtmSettingsProvider;
        $this->expressionProcessor = $expressionProcessor;
        $this->applicationState = $applicationState;
        $this->layoutContextStack = $layoutContextStack;
        $this->blockViewCache = $blockViewCache;
    }

    #[\Override]
    public function addExtension(ExtensionInterface $extension)
    {
        return $this->inner->addExtension($extension);
    }

    #[\Override]
    public function addType(BlockTypeInterface $type)
    {
        return $this->inner->addType($type);
    }

    #[\Override]
    public function addTypeExtension(BlockTypeExtensionInterface $typeExtension)
    {
        return $this->inner->addTypeExtension($typeExtension);
    }

    #[\Override]
    public function addLayoutUpdate($id, LayoutUpdateInterface $layoutUpdate)
    {
        return $this->inner->addLayoutUpdate($id, $layoutUpdate);
    }

    #[\Override]
    public function addRenderer($name, LayoutRendererInterface $renderer)
    {
        return $this->inner->addRenderer($name, $renderer);
    }

    #[\Override]
    public function setDefaultRenderer($name)
    {
        return $this->inner->setDefaultRenderer($name);
    }

    #[\Override]
    public function getLayoutFactory()
    {
        if ($this->applicationState->isInstalled() && $this->gtmSettingsProvider->isReady()) {
            return new LayoutFactoryDecorator(
                $this->inner->getLayoutFactory(),
                $this->expressionProcessor,
                $this->layoutContextStack,
                $this->blockViewCache
            );
        }

        return $this->inner->getLayoutFactory();
    }
}
