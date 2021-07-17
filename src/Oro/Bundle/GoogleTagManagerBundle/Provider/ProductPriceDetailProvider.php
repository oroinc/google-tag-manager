<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provide product price detail for GTM data layer
 */
class ProductPriceDetailProvider
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var UserCurrencyManager */
    private $userCurrencyManager;

    /** @var ProductPriceProviderInterface */
    private $productPriceProvider;

    /** @var ProductPriceScopeCriteriaFactoryInterface */
    private $priceScopeCriteriaFactory;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        WebsiteManager $websiteManager,
        UserCurrencyManager $userCurrencyManager,
        ProductPriceProviderInterface $productPriceProvider,
        ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->websiteManager = $websiteManager;
        $this->userCurrencyManager = $userCurrencyManager;
        $this->productPriceProvider = $productPriceProvider;
        $this->priceScopeCriteriaFactory = $priceScopeCriteriaFactory;
    }

    public function getPrice(Product $product, ProductUnit $productUnit, float $qty): ?Price
    {
        $website = $this->websiteManager->getCurrentWebsite();
        $currency = $this->userCurrencyManager->getUserCurrency($website);

        $priceCriteria = new ProductPriceCriteria($product, $productUnit, $qty, $currency);
        $scopeCriteria = $this->priceScopeCriteriaFactory->create($website, $this->getCustomer());

        $prices = $this->productPriceProvider->getMatchedPrices([$priceCriteria], $scopeCriteria);

        return $prices[$priceCriteria->getIdentifier()] ?? null;
    }

    private function getCustomer(): ?Customer
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if ($token instanceof AnonymousCustomerUserToken && $token->getVisitor()) {
            $user = $token->getVisitor()->getCustomerUser();
        }

        return $user instanceof CustomerUser ? $user->getCustomer() : null;
    }
}
