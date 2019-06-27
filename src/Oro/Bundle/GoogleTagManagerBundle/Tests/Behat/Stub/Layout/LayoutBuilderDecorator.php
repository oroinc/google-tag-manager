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
    /**
     * {@inheritdoc}
     */
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

            // Stop home page slider: random messages in data layer was break test logic
            if ($childView->getId() === 'hero_promo') {
                $childView->vars['attr']['data-page-component-options'] = [
                    'slidesToShow' => 1,
                    'autoplay' => false,
                    'arrows' => false,
                    'dots' => true,
                    'itemSelector' => '.promo-slider__item',
                ];
            }
        }

        parent::processBlockViewData($blockView, $context, $data, $deferred, $encoding);
    }
}
