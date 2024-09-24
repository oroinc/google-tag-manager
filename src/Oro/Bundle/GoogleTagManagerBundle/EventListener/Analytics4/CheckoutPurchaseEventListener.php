<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Adds to the GTM data layer the data for the Google Analytics 4 event:
 * - purchase
 */
class CheckoutPurchaseEventListener implements ServiceSubscriberInterface
{
    private FrontendHelper $frontendHelper;
    private ContainerInterface $container;
    private array $data = [];

    public function __construct(FrontendHelper $frontendHelper, ContainerInterface $container)
    {
        $this->frontendHelper = $frontendHelper;
        $this->container = $container;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            DataCollectionStateProviderInterface::class,
            DataLayerManager::class,
            PurchaseDetailProvider::class
        ];
    }

    public function preUpdate(Checkout $checkout, PreUpdateEventArgs $args): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        if (!$args->hasChangedField('completed') || !$args->getNewValue('completed')) {
            return;
        }

        $this->data = array_merge($this->data, $this->getPurchaseDetailProvider()->getData($checkout));
    }

    public function postFlush(): void
    {
        $dataLayerManager  = $this->getDataLayerManager();
        foreach ($this->data as $data) {
            $dataLayerManager->append($data);
        }

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->data = [];
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

    private function getDataLayerManager(): DataLayerManager
    {
        return $this->container->get(DataLayerManager::class);
    }

    private function getPurchaseDetailProvider(): PurchaseDetailProvider
    {
        return $this->container->get(PurchaseDetailProvider::class);
    }
}
