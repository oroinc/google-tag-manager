<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\GoogleTagManagerBundle\Provider\PageTypeProvider;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;

/**
 * Adds page related information for data layer.
 */
class PageVariablesCollector implements CollectorInterface
{
    /**
     * @var PageTypeProvider
     */
    private $pageTypeProvider;

    /**
     * @var LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @param PageTypeProvider $pageTypeProvider
     * @param LocalizationHelper $localizationHelper
     */
    public function __construct(PageTypeProvider $pageTypeProvider, LocalizationHelper $localizationHelper)
    {
        $this->pageTypeProvider = $pageTypeProvider;
        $this->localizationHelper = $localizationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Collection $data): void
    {
        $localization = $this->localizationHelper->getCurrentLocalization();

        $data->add(
            [
                DataLayerAttributeBag::KEY_PAGE_TYPE => $this->pageTypeProvider->getType(),
                DataLayerAttributeBag::KEY_LOCALIZATION_ID => $localization ? (string) $localization->getId() : null
            ]
        );
    }
}
