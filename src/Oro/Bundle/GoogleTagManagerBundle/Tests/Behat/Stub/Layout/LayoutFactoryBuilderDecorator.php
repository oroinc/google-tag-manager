<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Stub\Layout;

use Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider\IntegrationSettingsProvider;
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

    private ?BlockViewCache $blockViewCache;

    private bool $installed;

    public function __construct(
        LayoutFactoryBuilderInterface $inner,
        IntegrationSettingsProvider $gtmSettingsProvider,
        ExpressionProcessor $expressionProcessor,
        BlockViewCache $blockViewCache = null,
        $installed = false
    ) {
        $this->inner = $inner;
        $this->gtmSettingsProvider = $gtmSettingsProvider;
        $this->expressionProcessor = $expressionProcessor;
        $this->blockViewCache = $blockViewCache;
        $this->installed = (bool) $installed;
    }

    /**
     * {@inheritDoc}
     */
    public function addExtension(ExtensionInterface $extension)
    {
        return $this->inner->addExtension($extension);
    }

    /**
     * {@inheritDoc}
     */
    public function addType(BlockTypeInterface $type)
    {
        return $this->inner->addType($type);
    }

    /**
     * {@inheritDoc}
     */
    public function addTypeExtension(BlockTypeExtensionInterface $typeExtension)
    {
        return $this->inner->addTypeExtension($typeExtension);
    }

    /**
     * {@inheritDoc}
     */
    public function addLayoutUpdate($id, LayoutUpdateInterface $layoutUpdate)
    {
        return $this->inner->addLayoutUpdate($id, $layoutUpdate);
    }

    /**
     * {@inheritDoc}
     */
    public function addRenderer($name, LayoutRendererInterface $renderer)
    {
        return $this->inner->addRenderer($name, $renderer);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultRenderer($name)
    {
        return $this->inner->setDefaultRenderer($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getLayoutFactory()
    {
        if ($this->installed && $this->gtmSettingsProvider->isReady()) {
            return new LayoutFactoryDecorator(
                $this->inner->getLayoutFactory(),
                $this->expressionProcessor,
                $this->blockViewCache
            );
        }

        return $this->inner->getLayoutFactory();
    }
}
