<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Stub\Layout;

use Oro\Component\Layout\BlockViewCache;
use Oro\Component\Layout\DeferredLayoutManipulatorInterface;
use Oro\Component\Layout\ExpressionLanguage\ExpressionProcessor;
use Oro\Component\Layout\LayoutContextStack;
use Oro\Component\Layout\LayoutFactoryInterface;
use Oro\Component\Layout\RawLayoutBuilderInterface;

/**
 * Layout factory mock for replace LayoutBuilder object class
 */
class LayoutFactoryDecorator implements LayoutFactoryInterface
{
    private LayoutFactoryInterface $inner;

    private ExpressionProcessor $expressionProcessor;

    private LayoutContextStack $layoutContextStack;

    private BlockViewCache $blockViewCache;

    public function __construct(
        LayoutFactoryInterface $inner,
        ExpressionProcessor $expressionProcessor,
        LayoutContextStack $layoutContextStack,
        ?BlockViewCache $blockViewCache = null
    ) {
        $this->inner = $inner;
        $this->expressionProcessor = $expressionProcessor;
        $this->layoutContextStack = $layoutContextStack;
        $this->blockViewCache = $blockViewCache;
    }

    #[\Override]
    public function getRegistry()
    {
        return $this->inner->getRegistry();
    }

    #[\Override]
    public function getRendererRegistry()
    {
        return $this->inner->getRendererRegistry();
    }

    #[\Override]
    public function getType($name)
    {
        return $this->inner->getRendererRegistry();
    }

    #[\Override]
    public function createRawLayoutBuilder()
    {
        return $this->inner->createRawLayoutBuilder();
    }

    #[\Override]
    public function createLayoutManipulator(RawLayoutBuilderInterface $rawLayoutBuilder)
    {
        return $this->inner->createLayoutManipulator($rawLayoutBuilder);
    }

    #[\Override]
    public function createBlockFactory(DeferredLayoutManipulatorInterface $layoutManipulator)
    {
        return $this->inner->createBlockFactory($layoutManipulator);
    }

    #[\Override]
    public function createLayoutBuilder()
    {
        $rawLayoutBuilder = $this->createRawLayoutBuilder();
        $layoutManipulator = $this->createLayoutManipulator($rawLayoutBuilder);
        $blockFactory = $this->createBlockFactory($layoutManipulator);

        return new LayoutBuilderDecorator(
            $this->getRegistry(),
            $rawLayoutBuilder,
            $layoutManipulator,
            $blockFactory,
            $this->getRendererRegistry(),
            $this->expressionProcessor,
            $this->layoutContextStack,
            $this->blockViewCache
        );
    }
}
