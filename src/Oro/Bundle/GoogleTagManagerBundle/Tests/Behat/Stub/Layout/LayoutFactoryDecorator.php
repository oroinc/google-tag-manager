<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Stub\Layout;

use Oro\Component\Layout\BlockViewCache;
use Oro\Component\Layout\DeferredLayoutManipulatorInterface;
use Oro\Component\Layout\ExpressionLanguage\ExpressionProcessor;
use Oro\Component\Layout\LayoutFactoryInterface;
use Oro\Component\Layout\RawLayoutBuilderInterface;

/**
 * Layout factory mock for replace LayoutBuilder object class
 */
class LayoutFactoryDecorator implements LayoutFactoryInterface
{
    /** @var LayoutFactoryInterface */
    private $inner;

    /** @var ExpressionProcessor */
    private $expressionProcessor;

    /** @var BlockViewCache */
    private $blockViewCache;

    public function __construct(
        LayoutFactoryInterface $inner,
        ExpressionProcessor $expressionProcessor,
        BlockViewCache $blockViewCache = null
    ) {
        $this->inner = $inner;
        $this->expressionProcessor = $expressionProcessor;
        $this->blockViewCache = $blockViewCache;
    }

    /**
     * {@inheritDoc}
     */
    public function getRegistry()
    {
        return $this->inner->getRegistry();
    }

    /**
     * {@inheritDoc}
     */
    public function getRendererRegistry()
    {
        return $this->inner->getRendererRegistry();
    }

    /**
     * {@inheritDoc}
     */
    public function getType($name)
    {
        return $this->inner->getRendererRegistry();
    }

    /**
     * {@inheritDoc}
     */
    public function createRawLayoutBuilder()
    {
        return $this->inner->createRawLayoutBuilder();
    }

    /**
     * {@inheritDoc}
     */
    public function createLayoutManipulator(RawLayoutBuilderInterface $rawLayoutBuilder)
    {
        return $this->inner->createLayoutManipulator($rawLayoutBuilder);
    }

    /**
     * {@inheritDoc}
     */
    public function createBlockFactory(DeferredLayoutManipulatorInterface $layoutManipulator)
    {
        return $this->inner->createBlockFactory($layoutManipulator);
    }

    /**
     * {@inheritDoc}
     */
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
            $this->blockViewCache
        );
    }
}
