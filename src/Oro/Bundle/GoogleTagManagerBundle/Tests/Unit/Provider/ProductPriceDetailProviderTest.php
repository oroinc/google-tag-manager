<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactory;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ProductPriceDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var UserCurrencyManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userCurrencyManager;

    /** @var ProductPriceProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $productPriceProvider;

    /** @var ProductPriceScopeCriteriaFactoryInterface */
    private $priceScopeCriteriaFactory;

    /** @var ProductPriceDetailProvider */
    private $provider;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->userCurrencyManager = $this->createMock(UserCurrencyManager::class);
        $this->productPriceProvider = $this->createMock(ProductPriceProviderInterface::class);
        $this->priceScopeCriteriaFactory = new ProductPriceScopeCriteriaFactory();

        $this->provider = new ProductPriceDetailProvider(
            $this->tokenStorage,
            $this->websiteManager,
            $this->userCurrencyManager,
            $this->productPriceProvider,
            $this->priceScopeCriteriaFactory
        );
    }

    public function testGetPrice(): void
    {
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 42]);

        $productUnit = new ProductUnit();
        $productUnit->setCode('unit');

        $qty = 5.5;
        $currency = 'USD';
        $website = new Website();

        $customerUser = new CustomerUser();
        $customerUser->setCustomer(new Customer());

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->userCurrencyManager->expects($this->any())
            ->method('getUserCurrency')
            ->with($website)
            ->willReturn($currency);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new UsernamePasswordToken($customerUser, '', 'test'));

        $priceCriteria = new ProductPriceCriteria($product, $productUnit, $qty, $currency);

        $scopeCriteria = new ProductPriceScopeCriteria();
        $scopeCriteria->setWebsite($website);
        $scopeCriteria->setCustomer($customerUser->getCustomer());

        $this->productPriceProvider->expects($this->once())
            ->method('getMatchedPrices')
            ->with(
                [$priceCriteria],
                $scopeCriteria
            )
            ->willReturn(
                [
                    'no_data' => null,
                    $priceCriteria->getIdentifier() => Price::create(1.1, 'USD'),
                    'price1' => Price::create(2.2, 'USD')
                ]
            );

        $this->assertEquals(
            Price::create(1.1, 'USD'),
            $this->provider->getPrice($product, $productUnit, $qty)
        );
    }

    public function testGetPriceWithoutPrice(): void
    {
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 42]);

        $productUnit = new ProductUnit();
        $productUnit->setCode('unit');

        $qty = 5.5;
        $currency = 'USD';
        $website = new Website();

        $customerUser = new CustomerUser();
        $customerUser->setCustomer(new Customer());

        $customerVisitor = new CustomerVisitor();
        $customerVisitor->setCustomerUser($customerUser);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->userCurrencyManager->expects($this->any())
            ->method('getUserCurrency')
            ->with($website)
            ->willReturn($currency);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new AnonymousCustomerUserToken('', [], $customerVisitor));

        $priceCriteria = new ProductPriceCriteria($product, $productUnit, $qty, $currency);

        $scopeCriteria = new ProductPriceScopeCriteria();
        $scopeCriteria->setWebsite($website);
        $scopeCriteria->setCustomer($customerUser->getCustomer());

        $this->productPriceProvider->expects($this->once())
            ->method('getMatchedPrices')
            ->with(
                [$priceCriteria],
                $scopeCriteria
            )
            ->willReturn(
                [
                    'no_data' => null,
                    'price1' => Price::create(2.2, 'USD')
                ]
            );

        $this->assertNull($this->provider->getPrice($product, $productUnit, $qty));
    }
}
