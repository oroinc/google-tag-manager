<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Analytics4;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Model\ProductLineItem;
use Oro\Bundle\ProductBundle\Tests\Unit\Stub\ProductStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProductLineItemCartHandlerTest extends TestCase
{
    private DataLayerManager|MockObject $dataLayerManager;

    private ProductDetailProvider|MockObject $productDetailProvider;

    private ProductPriceDetailProvider|MockObject $productPriceDetailProvider;

    private UserCurrencyManager|MockObject $userCurrencyManager;

    private ProductLineItemCartHandler $handler;

    protected function setUp(): void
    {
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->userCurrencyManager = $this->createMock(UserCurrencyManager::class);

        $roundingService = $this->createMock(RoundingServiceInterface::class);
        $roundingService
            ->method('round')
            ->willReturnCallback(static fn ($value) => round($value, 2));

        $this->handler = new ProductLineItemCartHandler(
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productPriceDetailProvider,
            $this->userCurrencyManager,
            $roundingService
        );
    }

    public function testFlushWhenNoProduct(): void
    {
        $lineItem = new ProductLineItem(42);

        $this->productPriceDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager
            ->expects(self::never())
            ->method(self::anything());

        $this->handler->removeFromCart($lineItem);
        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testFlushWhenNoQuantity(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product());

        $this->productPriceDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager
            ->expects(self::never())
            ->method(self::anything());

        $this->handler->removeFromCart($lineItem);
        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testFlushWhenNoProductUnit(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456);

        $this->productPriceDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager
            ->expects(self::never())
            ->method(self::anything());

        $this->handler->removeFromCart($lineItem);
        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testAddToCartWhenNoProductDetailsNoPricesNoUserCurrency(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn(null);

        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getDefaultCurrency')
            ->willReturn($currency);

        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn(null);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                        ],
                    ],
                    'value' => 0.0,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testAddToCartWhenNoProductDetailsNoPrices(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn(null);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                        ],
                    ],
                    'value' => 0.0,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testAddToCartWhenNoPrices(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn(null);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                        ] + $productDetails,
                    ],
                    'value' => 0.0,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testAddToCart(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 1524.15,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testAddToCartWithExplicitQuantity(): void
    {
        $explicitQuantity = 23.4567;

        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $explicitQuantity)
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 2895.89,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem, null, $explicitQuantity);
        $this->handler->flush();
    }

    public function testAddToCartWithExplicitProductUnit(): void
    {
        $explicitQuantity = 23.4567;
        $explicitProductUnit = (new ProductUnit())->setCode('each');

        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $explicitProductUnit, $explicitQuantity)
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 2895.89,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem, $explicitProductUnit, $explicitQuantity);
        $this->handler->flush();
    }

    public function testAddToCartWithExplicitCurrency(): void
    {
        $explicitQuantity = 23.4567;
        $explicitProductUnit = (new ProductUnit())->setCode('each');
        $explicitCurrency = 'EUR';

        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::never())
            ->method(self::anything());

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $explicitProductUnit, $explicitQuantity)
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 2895.89,
                    'currency' => $explicitCurrency,
                ],
            ]);

        $this->handler->addToCart($lineItem, $explicitProductUnit, $explicitQuantity, $explicitCurrency);
        $this->handler->flush();
    }

    public function testAddToCartWhenReset(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->productPriceDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager
            ->expects(self::never())
            ->method(self::anything());

        $this->handler->addToCart($lineItem);
        $this->handler->reset();
        $this->handler->flush();
    }

    public function testAddToCartWhenSecondFlush(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 1524.15,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem);
        $this->handler->flush();

        // Checks that a GTM data layer is not modified anymore.
        $this->handler->flush();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddToCartWhenMultipleLineItems(): void
    {
        $lineItem1 = (new ProductLineItem(42))
            ->setProduct((new ProductStub())->setId(12))
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $lineItem2 = (new ProductLineItem(43))
            ->setProduct((new ProductStub())->setId(23))
            ->setQuantity(23.4567)
            ->setUnit((new ProductUnit())->setCode('each'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::exactly(2))
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $product1Details = ['sample_key1' => 'sample_value1'];
        $product2Details = ['sample_key2' => 'sample_value2'];
        $this->productDetailProvider
            ->expects(self::exactly(2))
            ->method('getData')
            ->willReturnMap([
                [$lineItem1->getProduct(), null, $product1Details],
                [$lineItem2->getProduct(), null, $product2Details],
            ]);

        $price1 = Price::create(123.4567, $currency);
        $price2 = Price::create(234.5678, $currency);

        $this->productPriceDetailProvider
            ->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturnMap([
                [$lineItem1->getProduct(), $lineItem1->getProductUnit(), $lineItem1->getQuantity(), $price1],
                [$lineItem2->getProduct(), $lineItem2->getProductUnit(), $lineItem2->getQuantity(), $price2],
            ]);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem1->getProductUnit()->getCode(),
                            'quantity' => $lineItem1->getQuantity(),
                            'price' => $price1->getValue(),
                        ] + $product1Details,
                        [
                            'item_variant' => $lineItem2->getProductUnit()->getCode(),
                            'quantity' => $lineItem2->getQuantity(),
                            'price' => $price2->getValue(),
                        ] + $product2Details,
                    ],
                    'value' => 7026.34,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->addToCart($lineItem1);
        $this->handler->addToCart($lineItem2);
        $this->handler->flush();
    }

    public function testRemoveFromCartWhenNoProductDetailsNoPricesNoUserCurrency(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn(null);

        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getDefaultCurrency')
            ->willReturn($currency);

        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn(null);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                        ],
                    ],
                    'value' => 0.0,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem);
        $this->handler->flush();
    }

    public function testRemoveFromCartWhenNoProductDetailsNoPrices(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn(null);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                        ],
                    ],
                    'value' => 0.0,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem);
        $this->handler->flush();
    }

    public function testRemoveFromCartWhenNoPrices(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn(null);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                        ] + $productDetails,
                    ],
                    'value' => 0.0,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem);
        $this->handler->flush();
    }

    public function testRemoveFromCart(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 1524.15,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem);
        $this->handler->flush();
    }

    public function testRemoveFromCartWithExplicitQuantity(): void
    {
        $explicitQuantity = 23.4567;

        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $explicitQuantity)
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 2895.89,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem, null, $explicitQuantity);
        $this->handler->flush();
    }

    public function testRemoveFromCartWithExplicitProductUnit(): void
    {
        $explicitQuantity = 23.4567;
        $explicitProductUnit = (new ProductUnit())->setCode('each');

        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $explicitProductUnit, $explicitQuantity)
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 2895.89,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem, $explicitProductUnit, $explicitQuantity);
        $this->handler->flush();
    }

    public function testRemoveFromCartWithExplicitCurrency(): void
    {
        $explicitQuantity = 23.4567;
        $explicitProductUnit = (new ProductUnit())->setCode('each');
        $explicitCurrency = 'EUR';

        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::never())
            ->method(self::anything());

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $explicitProductUnit, $explicitQuantity)
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 2895.89,
                    'currency' => $explicitCurrency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem, $explicitProductUnit, $explicitQuantity, $explicitCurrency);
        $this->handler->flush();
    }

    public function testRemoveFromCartWhenReset(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->productPriceDetailProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager
            ->expects(self::never())
            ->method(self::anything());

        $this->handler->removeFromCart($lineItem);
        $this->handler->reset();
        $this->handler->flush();
    }

    public function testRemoveFromCartWhenSecondFlush(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product())
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider
            ->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $price = Price::create(123.4567, $currency);

        $this->productPriceDetailProvider
            ->expects(self::once())
            ->method('getPrice')
            ->with($lineItem->getProduct(), $lineItem->getProductUnit(), $lineItem->getQuantity())
            ->willReturn($price);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $price->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => 1524.15,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem);
        $this->handler->flush();

        // Checks that a GTM data layer is not modified anymore.
        $this->handler->flush();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRemoveFromCartWhenMultipleLineItems(): void
    {
        $lineItem1 = (new ProductLineItem(42))
            ->setProduct((new ProductStub())->setId(12))
            ->setQuantity(12.3456)
            ->setUnit((new ProductUnit())->setCode('item'));

        $lineItem2 = (new ProductLineItem(43))
            ->setProduct((new ProductStub())->setId(23))
            ->setQuantity(23.4567)
            ->setUnit((new ProductUnit())->setCode('each'));

        $currency = 'USD';
        $this->userCurrencyManager
            ->expects(self::exactly(2))
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager
            ->expects(self::never())
            ->method('getDefaultCurrency');

        $product1Details = ['sample_key1' => 'sample_value1'];
        $product2Details = ['sample_key2' => 'sample_value2'];
        $this->productDetailProvider
            ->expects(self::exactly(2))
            ->method('getData')
            ->willReturnMap([
                [$lineItem1->getProduct(), null, $product1Details],
                [$lineItem2->getProduct(), null, $product2Details],
            ]);

        $price1 = Price::create(123.4567, $currency);
        $price2 = Price::create(234.5678, $currency);

        $this->productPriceDetailProvider
            ->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturnMap([
                [$lineItem1->getProduct(), $lineItem1->getProductUnit(), $lineItem1->getQuantity(), $price1],
                [$lineItem2->getProduct(), $lineItem2->getProductUnit(), $lineItem2->getQuantity(), $price2],
            ]);

        $this->dataLayerManager
            ->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem1->getProductUnit()->getCode(),
                            'quantity' => $lineItem1->getQuantity(),
                            'price' => $price1->getValue(),
                        ] + $product1Details,
                        [
                            'item_variant' => $lineItem2->getProductUnit()->getCode(),
                            'quantity' => $lineItem2->getQuantity(),
                            'price' => $price2->getValue(),
                        ] + $product2Details,
                    ],
                    'value' => 7026.34,
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem1);
        $this->handler->removeFromCart($lineItem2);
        $this->handler->flush();
    }
}
