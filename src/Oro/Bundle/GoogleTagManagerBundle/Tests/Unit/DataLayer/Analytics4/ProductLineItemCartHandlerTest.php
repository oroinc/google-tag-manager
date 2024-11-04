<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Analytics4;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\PricingBundle\Manager\UserCurrencyManager;
use Oro\Bundle\PricingBundle\Model\ProductLineItemPrice\ProductLineItemPrice;
use Oro\Bundle\PricingBundle\Provider\ProductLineItemPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Model\ProductLineItem;
use Oro\Bundle\ProductBundle\Tests\Unit\Stub\ProductStub;
use PHPUnit\Framework\Constraint\IsType;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProductLineItemCartHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataLayerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $dataLayerManager;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productDetailProvider;

    /** @var ProductLineItemPriceProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $productLineItemPriceProvider;

    /** @var UserCurrencyManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userCurrencyManager;

    /** @var ProductLineItemCartHandler */
    private $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productLineItemPriceProvider = $this->createMock(ProductLineItemPriceProviderInterface::class);
        $this->userCurrencyManager = $this->createMock(UserCurrencyManager::class);

        $this->handler = new ProductLineItemCartHandler(
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productLineItemPriceProvider,
            $this->userCurrencyManager
        );
    }

    public function testFlushWhenNoProduct(): void
    {
        $lineItem = new ProductLineItem(42);

        $this->productLineItemPriceProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->handler->removeFromCart($lineItem);
        $this->handler->addToCart($lineItem);
        $this->handler->flush();
    }

    public function testFlushWhenNoQuantity(): void
    {
        $lineItem = (new ProductLineItem(42))
            ->setProduct(new Product());

        $this->productLineItemPriceProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
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

        $this->productLineItemPriceProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn(null);

        $this->userCurrencyManager->expects(self::once())
            ->method('getDefaultCurrency')
            ->willReturn($currency);

        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem) {
                self::assertEquals([spl_object_hash(reset($lineItems)) => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [];
            });

        $this->dataLayerManager->expects(self::once())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem) {
                self::assertEquals([spl_object_hash(reset($lineItems)) => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [];
            });

        $this->dataLayerManager->expects(self::once())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem) {
                self::assertEquals([spl_object_hash(reset($lineItems)) => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [];
            });

        $this->dataLayerManager->expects(self::once())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem, $lineItemPrice) {
                $hash = spl_object_hash(reset($lineItems));
                self::assertEquals([$hash => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [$hash => $lineItemPrice];
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem, $explicitQuantity, $lineItemPrice) {
                $hash = spl_object_hash(reset($lineItems));
                self::assertEquals([$hash => (clone $lineItem)->setQuantity($explicitQuantity)], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [$hash => $lineItemPrice];
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(
                static function ($lineItems) use ($lineItem, $explicitQuantity, $explicitProductUnit, $lineItemPrice) {
                    $hash = spl_object_hash(reset($lineItems));
                    $expectedLineItem = (clone $lineItem)
                        ->setQuantity($explicitQuantity)
                        ->setProductUnit($explicitProductUnit);

                    self::assertEquals([$hash => $expectedLineItem], $lineItems);
                    self::assertNotSame($lineItem, reset($lineItems));

                    return [$hash => $lineItemPrice];
                }
            );

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::never())
            ->method(self::anything());

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $explicitCurrency)
            ->willReturnCallback(
                static function ($lineItems) use ($lineItem, $explicitQuantity, $explicitProductUnit, $lineItemPrice) {
                    $hash = spl_object_hash(reset($lineItems));
                    $expectedLineItem = (clone $lineItem)
                        ->setQuantity($explicitQuantity)
                        ->setProductUnit($explicitProductUnit);

                    self::assertEquals([$hash => $expectedLineItem], $lineItems);
                    self::assertNotSame($lineItem, reset($lineItems));

                    return [$hash => $lineItemPrice];
                }
            );

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->productLineItemPriceProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem, $lineItemPrice) {
                $hash = spl_object_hash(reset($lineItems));
                self::assertEquals([$hash => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [$hash => $lineItemPrice];
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::exactly(2))
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $product1Details = ['sample_key1' => 'sample_value1'];
        $product2Details = ['sample_key2' => 'sample_value2'];
        $this->productDetailProvider->expects(self::exactly(2))
            ->method('getData')
            ->willReturnMap([
                [$lineItem1->getProduct(), null, $product1Details],
                [$lineItem2->getProduct(), null, $product2Details],
            ]);

        $lineItem1Price = new ProductLineItemPrice(
            $lineItem1,
            Price::create(123.4567, $currency),
            1524.15
        );

        $lineItem2Price = new ProductLineItemPrice(
            $lineItem2,
            Price::create(234.5678, $currency),
            5502.19
        );

        $expectedLineItems = [$lineItem1, $lineItem2];
        $expectedLineItemsPrices = [$lineItem1Price, $lineItem2Price];

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($expectedLineItems, $expectedLineItemsPrices) {
                $lineItemPrices = [];
                while ($expectedLineItem = array_shift($expectedLineItems)) {
                    $expectedLineItemPrice = array_shift($expectedLineItemsPrices);
                    $lineItem = array_shift($lineItems);

                    $hash = spl_object_hash($lineItem);
                    self::assertEquals($expectedLineItem, $lineItem);
                    self::assertNotSame($expectedLineItem, $lineItem);

                    $lineItemPrices[$hash] = $expectedLineItemPrice;
                }

                return $lineItemPrices;
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem1->getProductUnit()->getCode(),
                            'quantity' => $lineItem1->getQuantity(),
                            'price' => $lineItem1Price->getPrice()->getValue(),
                        ] + $product1Details,
                        [
                            'item_variant' => $lineItem2->getProductUnit()->getCode(),
                            'quantity' => $lineItem2->getQuantity(),
                            'price' => $lineItem2Price->getPrice()->getValue(),
                        ] + $product2Details,
                    ],
                    'value' => $lineItem1Price->getSubtotal() + $lineItem2Price->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn(null);

        $this->userCurrencyManager->expects(self::once())
            ->method('getDefaultCurrency')
            ->willReturn($currency);

        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem) {
                self::assertEquals([spl_object_hash(reset($lineItems)) => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [];
            });

        $this->dataLayerManager->expects(self::once())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn([]);

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem) {
                self::assertEquals([spl_object_hash(reset($lineItems)) => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [];
            });

        $this->dataLayerManager->expects(self::once())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem) {
                self::assertEquals([spl_object_hash(reset($lineItems)) => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [];
            });

        $this->dataLayerManager->expects(self::once())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem, $lineItemPrice) {
                $hash = spl_object_hash(reset($lineItems));
                self::assertEquals([$hash => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [$hash => $lineItemPrice];
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem, $explicitQuantity, $lineItemPrice) {
                $hash = spl_object_hash(reset($lineItems));
                self::assertEquals([$hash => (clone $lineItem)->setQuantity($explicitQuantity)], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [$hash => $lineItemPrice];
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(
                static function ($lineItems) use ($lineItem, $explicitQuantity, $explicitProductUnit, $lineItemPrice) {
                    $hash = spl_object_hash(reset($lineItems));
                    $expectedLineItem = (clone $lineItem)
                        ->setQuantity($explicitQuantity)
                        ->setProductUnit($explicitProductUnit);

                    self::assertEquals([$hash => $expectedLineItem], $lineItems);
                    self::assertNotSame($lineItem, reset($lineItems));

                    return [$hash => $lineItemPrice];
                }
            );

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::never())
            ->method(self::anything());

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $explicitCurrency)
            ->willReturnCallback(
                static function ($lineItems) use ($lineItem, $explicitQuantity, $explicitProductUnit, $lineItemPrice) {
                    $hash = spl_object_hash(reset($lineItems));
                    $expectedLineItem = (clone $lineItem)
                        ->setQuantity($explicitQuantity)
                        ->setProductUnit($explicitProductUnit);

                    self::assertEquals([$hash => $expectedLineItem], $lineItems);
                    self::assertNotSame($lineItem, reset($lineItems));

                    return [$hash => $lineItemPrice];
                }
            );

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $explicitProductUnit->getCode(),
                            'quantity' => $explicitQuantity,
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $this->productDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->productLineItemPriceProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
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
        $this->userCurrencyManager->expects(self::once())
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $productDetails = ['sample_key' => 'sample_value'];
        $this->productDetailProvider->expects(self::once())
            ->method('getData')
            ->with($lineItem->getProduct())
            ->willReturn($productDetails);

        $lineItemPrice = new ProductLineItemPrice(
            $lineItem,
            Price::create(123.4567, $currency),
            1524.15
        );

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->with(self::isType(IsType::TYPE_ITERABLE), null, $currency)
            ->willReturnCallback(static function ($lineItems) use ($lineItem, $lineItemPrice) {
                $hash = spl_object_hash(reset($lineItems));
                self::assertEquals([$hash => $lineItem], $lineItems);
                self::assertNotSame($lineItem, reset($lineItems));

                return [$hash => $lineItemPrice];
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem->getProductUnit()->getCode(),
                            'quantity' => $lineItem->getQuantity(),
                            'price' => $lineItemPrice->getPrice()->getValue(),
                        ] + $productDetails,
                    ],
                    'value' => $lineItemPrice->getSubtotal(),
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
        $this->userCurrencyManager->expects(self::exactly(2))
            ->method('getUserCurrency')
            ->willReturn($currency);

        $this->userCurrencyManager->expects(self::never())
            ->method('getDefaultCurrency');

        $product1Details = ['sample_key1' => 'sample_value1'];
        $product2Details = ['sample_key2' => 'sample_value2'];
        $this->productDetailProvider->expects(self::exactly(2))
            ->method('getData')
            ->willReturnMap([
                [$lineItem1->getProduct(), null, $product1Details],
                [$lineItem2->getProduct(), null, $product2Details],
            ]);

        $lineItem1Price = new ProductLineItemPrice(
            $lineItem1,
            Price::create(123.4567, $currency),
            1524.15
        );

        $lineItem2Price = new ProductLineItemPrice(
            $lineItem2,
            Price::create(234.5678, $currency),
            5502.19
        );

        $expectedLineItems = [$lineItem1, $lineItem2];
        $expectedLineItemsPrices = [$lineItem1Price, $lineItem2Price];

        $this->productLineItemPriceProvider->expects(self::once())
            ->method('getProductLineItemsPrices')
            ->willReturnCallback(static function ($lineItems) use ($expectedLineItems, $expectedLineItemsPrices) {
                $lineItemPrices = [];
                while ($expectedLineItem = array_shift($expectedLineItems)) {
                    $expectedLineItemPrice = array_shift($expectedLineItemsPrices);
                    $lineItem = array_shift($lineItems);

                    $hash = spl_object_hash($lineItem);
                    self::assertEquals($expectedLineItem, $lineItem);
                    self::assertNotSame($expectedLineItem, $lineItem);

                    $lineItemPrices[$hash] = $expectedLineItemPrice;
                }

                return $lineItemPrices;
            });

        $this->dataLayerManager->expects(self::once())
            ->method('append')
            ->with([
                'event' => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [
                        [
                            'item_variant' => $lineItem1->getProductUnit()->getCode(),
                            'quantity' => $lineItem1->getQuantity(),
                            'price' => $lineItem1Price->getPrice()->getValue(),
                        ] + $product1Details,
                        [
                            'item_variant' => $lineItem2->getProductUnit()->getCode(),
                            'quantity' => $lineItem2->getQuantity(),
                            'price' => $lineItem2Price->getPrice()->getValue(),
                        ] + $product2Details,
                    ],
                    'value' => $lineItem1Price->getSubtotal() + $lineItem2Price->getSubtotal(),
                    'currency' => $currency,
                ],
            ]);

        $this->handler->removeFromCart($lineItem1);
        $this->handler->removeFromCart($lineItem2);
        $this->handler->flush();
    }
}
