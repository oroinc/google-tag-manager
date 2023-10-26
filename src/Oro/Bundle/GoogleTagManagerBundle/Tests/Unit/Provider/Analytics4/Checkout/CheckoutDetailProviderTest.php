<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Analytics4\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\PricingBundle\SubtotalProcessor\Model\Subtotal;
use Oro\Bundle\PricingBundle\SubtotalProcessor\Model\SubtotalProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class CheckoutDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const ITEM_SKU2 = [
        'item_id' => 'sku2',
        'item_name' => 'Product 2',
        'price' => 10.10,
        'item_brand' => 'Brand 2',
        'item_category' => 'Category 2',
        'quantity' => 5.5,
        'index' => 2,
        'item_variant' => 'item',
    ];

    private const ITEM_SKU3 = [
        'item_id' => 'sku3',
        'item_name' => 'Product 3',
        'price' => 100.10,
        'item_brand' => 'Brand 3',
        'item_category' => 'Category 3',
        'quantity' => 15.15,
        'index' => 3,
        'item_variant' => 'set',
    ];

    private const ITEM_FREE_FORM = [
        'item_id' => 'free-form-sku',
        'item_name' => 'Free Form Product',
        'price' => 4.2,
        'quantity' => 3.14,
        'index' => 4,
        'item_variant' => 'set',
    ];

    private ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject $productDetailProvider;

    private ProductPriceProviderInterface|\PHPUnit\Framework\MockObject\MockObject $productPriceProvider;

    private ProductPriceScopeCriteriaFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
        $priceScopeCriteriaFactory;

    private ShippingMethodLabelFormatter|\PHPUnit\Framework\MockObject\MockObject $shippingMethodLabelFormatter;

    private PaymentMethodLabelFormatter|\PHPUnit\Framework\MockObject\MockObject $paymentMethodLabelFormatter;

    private CheckoutDetailProvider $provider;

    private ProductPriceCriteriaFactoryInterface|MockObject $productPriceCriteriaFactory;

    private SubtotalProviderInterface|MockObject $checkoutSubtotalProvider;

    protected function setUp(): void
    {
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productPriceProvider = $this->createMock(ProductPriceProviderInterface::class);
        $this->priceScopeCriteriaFactory = $this->createMock(ProductPriceScopeCriteriaFactoryInterface::class);
        $this->shippingMethodLabelFormatter = $this->createMock(ShippingMethodLabelFormatter::class);
        $this->paymentMethodLabelFormatter = $this->createMock(PaymentMethodLabelFormatter::class);
        $this->productPriceCriteriaFactory = $this->createMock(ProductPriceCriteriaFactoryInterface::class);
        $this->checkoutSubtotalProvider = $this->createMock(SubtotalProviderInterface::class);

        $this->provider = new CheckoutDetailProvider(
            $this->productDetailProvider,
            $this->productPriceProvider,
            $this->priceScopeCriteriaFactory,
            $this->shippingMethodLabelFormatter,
            $this->paymentMethodLabelFormatter,
            $this->productPriceCriteriaFactory,
            $this->checkoutSubtotalProvider,
            1
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetBeginCheckoutData(): void
    {
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects(self::once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

        $this->productDetailProvider->expects(self::exactly(3))
            ->method('getData')
            ->withConsecutive(
                [self::identicalTo($lineItem1->getProduct())],
                [self::identicalTo($lineItem2->getProduct())],
                [self::identicalTo($lineItem3->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'item_id' => 'sku2',
                    'item_name' => 'Product 2',
                    'item_brand' => 'Brand 2',
                    'item_category' => 'Category 2',
                ],
                [
                    'item_id' => 'sku3',
                    'item_name' => 'Product 3',
                    'item_brand' => 'Brand 3',
                    'item_category' => 'Category 3',
                ]
            );

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceCriteriaFactory
            ->expects(self::once())
            ->method('createFromProductLineItem')
            ->with($lineItem2, $priceCriteria->getCurrency())
            ->willReturn($priceCriteria);

        $this->productPriceProvider->expects(self::once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD'),
                ]
            );

        $subtotal = (new Subtotal())->setAmount(123.4567);
        $this->checkoutSubtotalProvider
            ->expects(self::once())
            ->method('getSubtotal')
            ->with($checkout)
            ->willReturn($subtotal);

        self::assertEquals(
            [
                [
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU2],
                        'currency' => 'USD',
                        'value' => $subtotal->getAmount(),
                    ],
                ],
                [
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU3],
                    ],
                ],
                [
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => [self::ITEM_FREE_FORM],
                    ],
                ],
            ],
            $this->provider->getBeginCheckoutData($checkout)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetBeginCheckoutDataWhenNotEnoughData(): void
    {
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects(self::once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

        $this->productDetailProvider->expects(self::exactly(3))
            ->method('getData')
            ->withConsecutive(
                [self::identicalTo($lineItem1->getProduct())],
                [self::identicalTo($lineItem2->getProduct())],
                [self::identicalTo($lineItem3->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'item_id' => 'sku2',
                    'item_name' => 'Product 2',
                    'item_brand' => 'Brand 2',
                    'item_category' => 'Category 2',
                ],
                [
                    'item_id' => 'sku3',
                    'item_name' => 'Product 3',
                    'item_brand' => 'Brand 3',
                    'item_category' => 'Category 3',
                ]
            );

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceCriteriaFactory
            ->expects(self::once())
            ->method('createFromProductLineItem')
            ->with($lineItem2, $priceCriteria->getCurrency())
            ->willReturn($priceCriteria);

        $this->productPriceProvider->expects(self::once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD'),
                ]
            );

        $this->checkoutSubtotalProvider
            ->expects(self::once())
            ->method('getSubtotal')
            ->with($checkout)
            ->willReturn(null);

        self::assertEquals(
            [
                [
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU2],
                        'currency' => 'USD',
                        'value' => 0.0,
                    ],
                ],
                [
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU3],
                    ],
                ],
                [
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => [self::ITEM_FREE_FORM],
                    ],
                ],
            ],
            $this->provider->getBeginCheckoutData($checkout)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetShippingInfoDataWhenNotEnoughData(): void
    {
        /** @var Checkout $checkout */
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();

        $this->shippingMethodLabelFormatter
            ->expects(self::never())
            ->method(self::anything());

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects(self::once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

        $this->productDetailProvider->expects(self::exactly(3))
            ->method('getData')
            ->withConsecutive(
                [self::identicalTo($lineItem1->getProduct())],
                [self::identicalTo($lineItem2->getProduct())],
                [self::identicalTo($lineItem3->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'item_id' => 'sku2',
                    'item_name' => 'Product 2',
                    'item_brand' => 'Brand 2',
                    'item_category' => 'Category 2',
                ],
                [
                    'item_id' => 'sku3',
                    'item_name' => 'Product 3',
                    'item_brand' => 'Brand 3',
                    'item_category' => 'Category 3',
                ]
            );

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceCriteriaFactory
            ->expects(self::once())
            ->method('createFromProductLineItem')
            ->with($lineItem2, $priceCriteria->getCurrency())
            ->willReturn($priceCriteria);

        $this->productPriceProvider->expects(self::once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD'),
                ]
            );

        $this->checkoutSubtotalProvider
            ->expects(self::once())
            ->method('getSubtotal')
            ->with($checkout)
            ->willReturn(null);

        self::assertEquals(
            [
                [
                    'event' => 'add_shipping_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU2],
                        'currency' => 'USD',
                        'value' => 0.0,
                    ],
                ],
                [
                    'event' => 'add_shipping_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU3],
                    ],
                ],
                [
                    'event' => 'add_shipping_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_FREE_FORM],
                    ],
                ],
            ],
            $this->provider->getShippingInfoData($checkout)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetShippingInfoData(): void
    {
        /** @var Checkout $checkout */
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();

        $shippingMethod = 'sample_method';
        $shippingMethodType = 'sample_method_type';
        $checkout
            ->setShippingMethod($shippingMethod)
            ->setShippingMethodType($shippingMethodType);

        $shippingMethodLabel = 'Sample Method';
        $this->shippingMethodLabelFormatter
            ->expects(self::once())
            ->method('formatShippingMethodWithTypeLabel')
            ->with($shippingMethod, $shippingMethodType)
            ->willReturn($shippingMethodLabel);

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects(self::once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

        $this->productDetailProvider->expects(self::exactly(3))
            ->method('getData')
            ->withConsecutive(
                [self::identicalTo($lineItem1->getProduct())],
                [self::identicalTo($lineItem2->getProduct())],
                [self::identicalTo($lineItem3->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'item_id' => 'sku2',
                    'item_name' => 'Product 2',
                    'item_brand' => 'Brand 2',
                    'item_category' => 'Category 2',
                ],
                [
                    'item_id' => 'sku3',
                    'item_name' => 'Product 3',
                    'item_brand' => 'Brand 3',
                    'item_category' => 'Category 3',
                ]
            );

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceCriteriaFactory
            ->expects(self::once())
            ->method('createFromProductLineItem')
            ->with($lineItem2, $priceCriteria->getCurrency())
            ->willReturn($priceCriteria);

        $this->productPriceProvider->expects(self::once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD'),
                ]
            );

        $subtotal = (new Subtotal())->setAmount(123.4567);
        $this->checkoutSubtotalProvider
            ->expects(self::once())
            ->method('getSubtotal')
            ->with($checkout)
            ->willReturn($subtotal);

        self::assertEquals(
            [
                [
                    'event' => 'add_shipping_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU2],
                        'currency' => 'USD',
                        'value' => $subtotal->getAmount(),
                        'shipping_tier' => $shippingMethodLabel,
                    ],
                ],
                [
                    'event' => 'add_shipping_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU3],
                        'shipping_tier' => $shippingMethodLabel,
                    ],
                ],
                [
                    'event' => 'add_shipping_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_FREE_FORM],
                        'shipping_tier' => $shippingMethodLabel,
                    ],
                ],
            ],
            $this->provider->getShippingInfoData($checkout)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetPaymentInfoDataWhenNotEnoughData(): void
    {
        /** @var Checkout $checkout */
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();

        $this->paymentMethodLabelFormatter
            ->expects(self::never())
            ->method(self::anything());

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects(self::once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

        $this->productDetailProvider->expects(self::exactly(3))
            ->method('getData')
            ->withConsecutive(
                [self::identicalTo($lineItem1->getProduct())],
                [self::identicalTo($lineItem2->getProduct())],
                [self::identicalTo($lineItem3->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'item_id' => 'sku2',
                    'item_name' => 'Product 2',
                    'item_brand' => 'Brand 2',
                    'item_category' => 'Category 2',
                ],
                [
                    'item_id' => 'sku3',
                    'item_name' => 'Product 3',
                    'item_brand' => 'Brand 3',
                    'item_category' => 'Category 3',
                ]
            );

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceCriteriaFactory
            ->expects(self::once())
            ->method('createFromProductLineItem')
            ->with($lineItem2, $priceCriteria->getCurrency())
            ->willReturn($priceCriteria);

        $this->productPriceProvider->expects(self::once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD'),
                ]
            );

        $this->checkoutSubtotalProvider
            ->expects(self::once())
            ->method('getSubtotal')
            ->with($checkout)
            ->willReturn(null);

        self::assertEquals(
            [
                [
                    'event' => 'add_payment_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU2],
                        'currency' => 'USD',
                        'value' => 0.0,
                    ],
                ],
                [
                    'event' => 'add_payment_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU3],
                    ],
                ],
                [
                    'event' => 'add_payment_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_FREE_FORM],
                    ],
                ],
            ],
            $this->provider->getPaymentInfoData($checkout)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetPaymentInfoData(): void
    {
        /** @var Checkout $checkout */
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();

        $paymentMethod = 'sample_method';
        $checkout->setPaymentMethod($paymentMethod);

        $paymentMethodLabel = 'Sample Method';
        $this->paymentMethodLabelFormatter
            ->expects(self::once())
            ->method('formatPaymentMethodLabel')
            ->with($paymentMethod)
            ->willReturn($paymentMethodLabel);

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects(self::once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

        $this->productDetailProvider->expects(self::exactly(3))
            ->method('getData')
            ->withConsecutive(
                [self::identicalTo($lineItem1->getProduct())],
                [self::identicalTo($lineItem2->getProduct())],
                [self::identicalTo($lineItem3->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'item_id' => 'sku2',
                    'item_name' => 'Product 2',
                    'item_brand' => 'Brand 2',
                    'item_category' => 'Category 2',
                ],
                [
                    'item_id' => 'sku3',
                    'item_name' => 'Product 3',
                    'item_brand' => 'Brand 3',
                    'item_category' => 'Category 3',
                ]
            );

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceCriteriaFactory
            ->expects(self::once())
            ->method('createFromProductLineItem')
            ->with($lineItem2, $priceCriteria->getCurrency())
            ->willReturn($priceCriteria);

        $this->productPriceProvider->expects(self::once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD'),
                ]
            );

        $subtotal = (new Subtotal())->setAmount(123.4567);
        $this->checkoutSubtotalProvider
            ->expects(self::once())
            ->method('getSubtotal')
            ->with($checkout)
            ->willReturn($subtotal);

        self::assertEquals(
            [
                [
                    'event' => 'add_payment_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU2],
                        'currency' => 'USD',
                        'value' => $subtotal->getAmount(),
                        'payment_type' => $paymentMethodLabel,
                    ],
                ],
                [
                    'event' => 'add_payment_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_SKU3],
                        'payment_type' => $paymentMethodLabel,
                    ],
                ],
                [
                    'event' => 'add_payment_info',
                    'ecommerce' => [
                        'items' => [self::ITEM_FREE_FORM],
                        'payment_type' => $paymentMethodLabel,
                    ],
                ],
            ],
            $this->provider->getPaymentInfoData($checkout)
        );
    }

    private function prepareCheckout(): array
    {
        /** @var Product $product1 */
        $product1 = $this->getEntity(Product::class, ['id' => 1001]);
        /** @var Product $product2 */
        $product2 = $this->getEntity(Product::class, ['id' => 2002]);
        /** @var Product $product3 */
        $product3 = $this->getEntity(Product::class, ['id' => 3003]);

        $lineItem1 = new CheckoutLineItem();
        $lineItem1->setProduct($product1)
            ->preSave();

        $productUnit2 = new ProductUnit();
        $productUnit2->setCode('item');

        $lineItem2 = new CheckoutLineItem();
        $lineItem2->setProduct($product2)
            ->setProductUnit($productUnit2)
            ->setQuantity(5.5)
            ->setPrice(Price::create(1.1, 'USD'))
            ->preSave();

        $productUnit3 = new ProductUnit();
        $productUnit3->setCode('set');

        $lineItem3 = new CheckoutLineItem();
        $lineItem3->setProduct($product3)
            ->setProductUnit($productUnit3)
            ->setQuantity(15.15)
            ->setPriceFixed(true)
            ->setPrice(Price::create(100.1, 'USD'))
            ->preSave();

        $lineItem4 = new CheckoutLineItem();
        $lineItem4->setProductSku('free-form-sku')
            ->setFreeFormProduct('Free Form Product')
            ->setProductUnit($productUnit3)
            ->setQuantity(3.14)
            ->setPriceFixed(true)
            ->setPrice(Price::create(4.2, 'USD'))
            ->preSave();

        $checkout = new Checkout();
        $checkout->setCurrency('USD')
            ->addLineItem($lineItem1)
            ->addLineItem($lineItem2)
            ->addLineItem($lineItem3)
            ->addLineItem($lineItem4);

        return [$checkout, $lineItem1, $lineItem2, $lineItem3, $lineItem4];
    }
}
