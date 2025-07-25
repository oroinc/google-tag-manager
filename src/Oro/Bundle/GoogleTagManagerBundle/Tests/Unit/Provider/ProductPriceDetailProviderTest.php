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
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactory;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ProductPriceDetailProviderTest extends TestCase
{
    use EntityTrait;

    private TokenStorageInterface&MockObject $tokenStorage;
    private WebsiteManager&MockObject $websiteManager;
    private UserCurrencyManager&MockObject $userCurrencyManager;
    private ProductPriceProviderInterface&MockObject $productPriceProvider;
    private ProductPriceDetailProvider $provider;
    private ProductPriceCriteriaFactoryInterface&MockObject $productPriceCriteriaFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->userCurrencyManager = $this->createMock(UserCurrencyManager::class);
        $this->productPriceProvider = $this->createMock(ProductPriceProviderInterface::class);
        $priceScopeCriteriaFactory = new ProductPriceScopeCriteriaFactory();
        $this->productPriceCriteriaFactory = $this->createMock(ProductPriceCriteriaFactoryInterface::class);

        $this->provider = new ProductPriceDetailProvider(
            $this->tokenStorage,
            $this->websiteManager,
            $this->userCurrencyManager,
            $this->productPriceProvider,
            $priceScopeCriteriaFactory,
            $this->productPriceCriteriaFactory
        );
    }

    public function testGetPrice(): void
    {
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
            ->willReturn(new UsernamePasswordToken($customerUser, 'test'));

        $priceCriteria = new ProductPriceCriteria($product, $productUnit, $qty, $currency);

        $this->productPriceCriteriaFactory->expects($this->once())
            ->method('create')
            ->with($product, $productUnit, $qty, $currency)
            ->willReturn($priceCriteria);

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
                    '42-unit-5.5-USD' => Price::create(1.1, 'USD'),
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
            ->willReturn(new AnonymousCustomerUserToken($customerVisitor));

        $priceCriteria = new ProductPriceCriteria($product, $productUnit, $qty, $currency);

        $this->productPriceCriteriaFactory->expects($this->once())
            ->method('create')
            ->with($product, $productUnit, $qty, $currency)
            ->willReturn($priceCriteria);

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
