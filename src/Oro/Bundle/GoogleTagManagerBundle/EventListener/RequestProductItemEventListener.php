<?php

namespace Oro\Bundle\GoogleTagManagerBundle\EventListener;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Handles changes of RequestProductItem entities.
 */
class RequestProductItemEventListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var DataLayerManager */
    private $dataLayerManager;

    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /** @var ProductPriceDetailProvider */
    private $productPriceDetailProvider;

    /** @var GoogleTagManagerSettingsProviderInterface */
    private $settingsProvider;

    /** @var int */
    private $batchSize;

    /** @var array */
    private $items = [];

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param DataLayerManager $dataLayerManager
     * @param ProductDetailProvider $productDetailProvider
     * @param ProductPriceDetailProvider $productPriceDetailProvider
     * @param GoogleTagManagerSettingsProviderInterface $settingsProvider
     * @param int $batchSize
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        DataLayerManager $dataLayerManager,
        ProductDetailProvider $productDetailProvider,
        ProductPriceDetailProvider $productPriceDetailProvider,
        GoogleTagManagerSettingsProviderInterface $settingsProvider,
        int $batchSize
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dataLayerManager = $dataLayerManager;
        $this->productDetailProvider = $productDetailProvider;
        $this->productPriceDetailProvider = $productPriceDetailProvider;
        $this->settingsProvider = $settingsProvider;
        $this->batchSize = $batchSize;
    }

    /**
     * @param RequestProductItem $item
     */
    public function prePersist(RequestProductItem $item = null): void
    {
        if (!$this->isApplicable()) {
            return;
        }

        $data = $this->productDetailProvider->getData($item->getRequestProduct()->getProduct());

        $unit = $item->getProductUnit();

        $data['variant'] = $unit->getCode();
        $data['quantity'] = $item->getQuantity();

        $price = $this->productPriceDetailProvider->getPrice($item->getProduct(), $unit, $item->getQuantity());

        $currency = null;
        if ($price instanceof Price) {
            $data['price'] = $price->getValue();
            $currency = $price->getCurrency();
        }

        $this->items[$currency][] = $data;
    }

    public function postFlush(): void
    {
        foreach ($this->items as $currency => $products) {
            foreach (array_chunk($products, $this->batchSize) as $chunk) {
                $this->dataLayerManager->add(
                    [
                        'event' => 'addToCart',
                        'ecommerce' => [
                            'currencyCode' => $currency,
                            'add' => [
                                'products' => $chunk
                            ]
                        ]
                    ]
                );
            }
        }

        $this->onClear();
    }

    public function onClear(): void
    {
        $this->items = [];
    }

    /**
     * @return bool
     */
    private function isApplicable(): bool
    {
        if (!$this->settingsProvider->getGoogleTagManagerSettings()) {
            return false;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        return $token instanceof AnonymousCustomerUserToken || $token->getUser() instanceof CustomerUser;
    }
}
