<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Stub\Layout;

use Oro\Component\Layout\BlockView;
use Oro\Component\Layout\ContextInterface;
use Oro\Component\Layout\DataAccessor;
use Oro\Component\Layout\LayoutBuilder;

/**
 * Remove "google_tag_manager_head" view block for behat tests
 */
class LayoutBuilderDecorator extends LayoutBuilder
{
    #[\Override]
    protected function processBlockViewData(
        BlockView $blockView,
        ContextInterface $context,
        DataAccessor $data,
        $deferred,
        $encoding
    ) {
        foreach ($blockView->children as $key => $childView) {
            // Unset GTM script block
            if ($childView->getId() === 'google_tag_manager_head') {
                unset($blockView->children[$key]);
            }
        }

        parent::processBlockViewData($blockView, $context, $data, $deferred, $encoding);
    }
}
