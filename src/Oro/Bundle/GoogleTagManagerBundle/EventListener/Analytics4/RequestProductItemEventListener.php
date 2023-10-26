<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;

/**
 * Adds "add_to_cart" GA4 event to the GTM data layer when line item is added to RFP on a storefront.
 */
class RequestProductItemEventListener
{
    private FrontendHelper $frontendHelper;

    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private ProductLineItemCartHandler $productLineItemCartHandler;

    public function __construct(
        FrontendHelper $frontendHelper,
        DataCollectionStateProviderInterface $dataCollectionStateProvider,
        ProductLineItemCartHandler $productLineItemCartHandler
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->dataCollectionStateProvider = $dataCollectionStateProvider;
        $this->productLineItemCartHandler = $productLineItemCartHandler;
    }

    public function setProductLineItemCartHandler(?ProductLineItemCartHandler $productLineItemCartHandler): void
    {
        $this->productLineItemCartHandler = $productLineItemCartHandler;
    }

    public function prePersist(RequestProductItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->productLineItemCartHandler->addToCart($item);
    }

    public function postFlush(): void
    {
        $this->productLineItemCartHandler->flush();
        $this->onClear();
    }

    public function onClear(): void
    {
        $this->productLineItemCartHandler->reset();
    }

    private function isApplicable(): bool
    {
        return $this->frontendHelper->isFrontendRequest()
            && $this->dataCollectionStateProvider->isEnabled('google_analytics4');
    }
}
