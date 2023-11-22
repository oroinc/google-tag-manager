<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Adds "add_to_cart" GA4 event to the GTM data layer when line item is added to RFP on a storefront.
 */
class RequestProductItemEventListener implements ServiceSubscriberInterface
{
    private FrontendHelper $frontendHelper;
    private ContainerInterface $container;
    private ?ProductLineItemCartHandler $productLineItemCartHandler = null;

    public function __construct(FrontendHelper $frontendHelper, ContainerInterface $container)
    {
        $this->frontendHelper = $frontendHelper;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            DataCollectionStateProviderInterface::class,
            ProductLineItemCartHandler::class
        ];
    }

    public function prePersist(RequestProductItem $item): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->getProductLineItemCartHandler()->addToCart($item);
    }

    public function postFlush(): void
    {
        $this->getProductLineItemCartHandler()->flush();
        $this->onClear();
    }

    public function onClear(): void
    {
        $this->getProductLineItemCartHandler()->reset();
    }

    private function isApplicable(): bool
    {
        return
            $this->frontendHelper->isFrontendRequest()
            && $this->getDataCollectionStateProvider()->isEnabled('google_analytics4');
    }

    private function getDataCollectionStateProvider(): DataCollectionStateProviderInterface
    {
        return $this->container->get(DataCollectionStateProviderInterface::class);
    }

    private function getProductLineItemCartHandler(): ProductLineItemCartHandler
    {
        // need to store this service in a property because this service is not shared,
        // so, each call of the container::get() created a new instance of it
        if (null === $this->productLineItemCartHandler) {
            $this->productLineItemCartHandler = $this->container->get(ProductLineItemCartHandler::class);
        }

        return $this->productLineItemCartHandler;
    }
}
