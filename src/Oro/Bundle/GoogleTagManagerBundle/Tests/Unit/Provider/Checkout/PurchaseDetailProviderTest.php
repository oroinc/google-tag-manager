<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Checkout;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\GoogleTagManagerBundle\Formatter\NumberFormatter;
use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\OrderBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Entity\Repository\PromotionRepository;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;
use Oro\Bundle\PromotionBundle\Tests\Unit\Entity\Stub\Order as OrderStub;
use Oro\Bundle\PromotionBundle\Tests\Unit\Entity\Stub\PromotionStub;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\TaxBundle\Exception\TaxationDisabledException;
use Oro\Bundle\TaxBundle\Model\Result;
use Oro\Bundle\TaxBundle\Model\ResultElement;
use Oro\Bundle\TaxBundle\Provider\TaxProviderInterface;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class PurchaseDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;
    use LoggerAwareTraitTestTrait;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private ProductDetailProvider $productDetailProvider;

    /** @var TaxProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private TaxProviderInterface $taxProvider;

    /** @var EntityCouponsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private EntityCouponsProviderInterface $entityCouponsProvider;

    private PurchaseDetailProvider $provider;

    /** @var OrderRepository|\PHPUnit\Framework\MockObject\MockObject */
    private OrderRepository $orderRepository;

    /** @var PaymentTransactionRepository|\PHPUnit\Framework\MockObject\MockObject */
    private PaymentTransactionRepository $paymentTransactionRepository;

    /** @var PromotionRepository|\PHPUnit\Framework\MockObject\MockObject */
    private PromotionRepository $promotionRepository;

    /** @var AppliedPromotionsNamesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private AppliedPromotionsNamesProvider $appliedPromotionsNamesProvider;
    
    /** @var NumberFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private NumberFormatter $numberFormatter;
    
    protected function setUp(): void
    {
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->paymentTransactionRepository = $this->createMock(PaymentTransactionRepository::class);
        $this->promotionRepository = $this->createMock(PromotionRepository::class);
        $doctrineHelper
            ->expects(self::any())
            ->method('getEntityRepositoryForClass')
            ->willReturnMap(
                [
                    [Order::class, $this->orderRepository],
                    [PaymentTransaction::class, $this->paymentTransactionRepository],
                    [Promotion::class, $this->promotionRepository],
                ]
            );

        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        $this->taxProvider = $this->createMock(TaxProviderInterface::class);

        /** @var TaxProviderRegistry|\PHPUnit\Framework\MockObject\MockObject $taxProviderRegistry */
        $taxProviderRegistry = $this->createMock(TaxProviderRegistry::class);
        $taxProviderRegistry->expects(self::any())
            ->method('getEnabledProvider')
            ->willReturn($this->taxProvider);

        $this->entityCouponsProvider = $this->createMock(EntityCouponsProviderInterface::class);

        $this->shippingMethodLabelFormatter = $this->createMock(ShippingMethodLabelFormatter::class);
        $this->shippingMethodLabelFormatter->expects(self::any())
            ->method('formatShippingMethodWithTypeLabel')
            ->willReturnCallback(
                function (string $shippingMethod, string $shippingType) {
                    return $shippingMethod . ShippingMethodLabelFormatter::DELIMITER . $shippingType . '_formatted';
                }
            );

        $this->paymentMethodLabelFormatter = $this->createMock(PaymentMethodLabelFormatter::class);
        $this->paymentMethodLabelFormatter->expects(self::any())
            ->method('formatPaymentMethodLabel')
            ->willReturnCallback(
                function (string $paymentMethod) {
                    return $paymentMethod . '_formatted';
                }
            );

        $this->provider = new PurchaseDetailProvider(
            $doctrineHelper,
            $this->productDetailProvider,
            $taxProviderRegistry,
            $this->entityCouponsProvider,
            $this->shippingMethodLabelFormatter,
            $this->paymentMethodLabelFormatter,
            1
        );

        $this->numberFormatter = $this->createMock(NumberFormatter::class);
        $this->numberFormatter
            ->expects(self::any())
            ->method('formatPriceValue')
            ->willReturnCallback(static function (float $value) {
                return round($value, 2);
            });

        $this->provider->setNumberFormatter($this->numberFormatter);

        $this->appliedPromotionsNamesProvider = $this->createMock(AppliedPromotionsNamesProvider::class);
        $this->provider->setAppliedPromotionsNamesProvider($this->appliedPromotionsNamesProvider);

        $this->setUpLoggerMock($this->provider);
    }

    public function testGetPurchaseDataWithoutOrder(): void
    {
        $id = 42;

        $this->orderRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $id])
            ->willReturn(null);

        $checkout = new Checkout();
        $checkout->getCompletedData()
            ->offsetSet('orders', [['entityId' => ['id' => $id]]]);

        $this->productDetailProvider->expects(self::never())
            ->method('getData');

        self::assertEquals([], $this->provider->getData($checkout));
    }


    /**
     * @dataProvider getPurchaseDataProvider
     */
    public function testGetPurchaseData(
        Order $order,
        OrderLineItem $lineItem1,
        OrderLineItem $lineItem2,
        OrderLineItem $lineItem3,
        OrderLineItem $lineItem4,
        ?float $taxAmount,
        array $promotionsNames,
        ?string $paymentMethod,
        array $expected
    ): void {
        $this->orderRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $order->getId()])
            ->willReturn($order);

        $this->paymentTransactionRepository->expects(self::once())
            ->method('getPaymentMethods')
            ->with(Order::class, [$order->getId()])
            ->willReturn([$order->getId() => [$paymentMethod]]);

        $checkout = new Checkout();
        $checkout->getCompletedData()
            ->offsetSet('orders', [['entityId' => ['id' => $order->getId()]]]);

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
                    'id' => 'sku2',
                    'name' => 'Product 2',
                    'brand' => 'Brand 2',
                    'category' => 'Category 2',
                ],
                [
                    'id' => 'sku3',
                    'name' => 'Product 3',
                    'brand' => 'Brand 3',
                    'category' => 'Category 3',
                ]
            );

        $this->assertTaxProviderCalled($order, $taxAmount);

        $this->appliedPromotionsNamesProvider
            ->expects(self::once())
            ->method('getAppliedPromotionsNames')
            ->with($order)
            ->willReturn($promotionsNames);

        self::assertEquals($expected, $this->provider->getData($checkout));
    }

    /**
     * @dataProvider getPurchaseDataProvider
     */
    public function testGetPurchaseDataWhenTaxationError(
        Order $order,
        OrderLineItem $lineItem1,
        OrderLineItem $lineItem2,
        OrderLineItem $lineItem3,
        OrderLineItem $lineItem4,
        ?float $taxAmount,
        array $promotionsNames,
        ?string $paymentMethod,
        array $expected
    ): void {
        $this->orderRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $order->getId()])
            ->willReturn($order);

        $this->paymentTransactionRepository->expects(self::once())
            ->method('getPaymentMethods')
            ->with(Order::class, [$order->getId()])
            ->willReturn([$order->getId() => [$paymentMethod]]);

        $checkout = new Checkout();
        $checkout->getCompletedData()
            ->offsetSet('orders', [['entityId' => ['id' => $order->getId()]]]);

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
                    'id' => 'sku2',
                    'name' => 'Product 2',
                    'brand' => 'Brand 2',
                    'category' => 'Category 2',
                ],
                [
                    'id' => 'sku3',
                    'name' => 'Product 3',
                    'brand' => 'Brand 3',
                    'category' => 'Category 3',
                ]
            );

        $throwable = new \RuntimeException();
        $this->taxProvider->expects(self::once())
            ->method('loadTax')
            ->with($order)
            ->willThrowException($throwable);

        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(
                'Skipped adding tax to the GTM data layer due to an unexpected error: {message}',
                ['order' => $order, 'throwable' => $throwable, 'message' => $throwable->getMessage()]
            );

        $this->appliedPromotionsNamesProvider
            ->expects(self::once())
            ->method('getAppliedPromotionsNames')
            ->with($order)
            ->willReturn($promotionsNames);

        unset($expected[0]['ecommerce']['purchase']['actionField']['tax']);

        self::assertEquals($expected, $this->provider->getData($checkout));
    }

    /**
     * @dataProvider getPurchaseDataProvider
     */
    public function testGetPurchaseDataWithoutAppliedPromotionsNamesProvider(
        Order $order,
        OrderLineItem $lineItem1,
        OrderLineItem $lineItem2,
        OrderLineItem $lineItem3,
        OrderLineItem $lineItem4,
        ?float $taxAmount,
        array $coupons,
        ?string $paymentMethod,
        array $expected
    ): void {
        $this->orderRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $order->getId()])
            ->willReturn($order);

        $this->paymentTransactionRepository->expects(self::once())
            ->method('getPaymentMethods')
            ->with(Order::class, [$order->getId()])
            ->willReturn([$order->getId() => [$paymentMethod]]);

        $checkout = new Checkout();
        $checkout->getCompletedData()
            ->offsetSet('orders', [['entityId' => ['id' => $order->getId()]]]);

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
                    'id' => 'sku2',
                    'name' => 'Product 2',
                    'brand' => 'Brand 2',
                    'category' => 'Category 2',
                ],
                [
                    'id' => 'sku3',
                    'name' => 'Product 3',
                    'brand' => 'Brand 3',
                    'category' => 'Category 3',
                ]
            );

        $this->assertTaxProviderCalled($order, $taxAmount);
        $this->assertCouponsProviderCalled($order, $coupons);

        $this->provider->setAppliedPromotionsNamesProvider(null);
        self::assertEquals($expected, $this->provider->getData($checkout));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPurchaseDataProvider(): array
    {
        /** @var Order $order1 */
        [$order1, $lineItem11, $lineItem12, $lineItem13, $lineItem14] = $this->prepareOrder();

        $website = new Website();
        $website->setName('Test Website');

        $order1->setEstimatedShippingCostAmount(20.20)
            ->setShippingMethod('shipping_method')
            ->setShippingMethodType('shipping_type')
            ->setWebsite($website);

        [$order2, $lineItem21, $lineItem22, $lineItem23, $lineItem24] = $this->prepareOrder();

        return [
            'full data' => [
                'order' => $order1,
                'lineItem1' => $lineItem11,
                'lineItem2' => $lineItem12,
                'lineItem3' => $lineItem13,
                'lineItem4' => $lineItem14,
                'tax' => 11.8,
                'coupons' => ['CODE1', 'CODE2'],
                'paymentMethod' => 'payment_method',
                'expected' => [
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'purchase' => [
                                'actionField' => [
                                    'id' => 42,
                                    'revenue' => 1500.57,
                                    'tax' => 11.8,
                                    'shipping' => 20.20,
                                    'affiliation' => 'Test Website',
                                    'coupon' => 'CODE1,CODE2',
                                ],
                                'products' => [
                                    [
                                        'id' => 'sku2',
                                        'name' => 'Product 2',
                                        'price' => 1.1,
                                        'brand' => 'Brand 2',
                                        'category' => 'Category 2',
                                        'quantity' => 5.5,
                                        'position' => 2,
                                        'variant' => 'item',
                                    ],
                                ],
                            ],
                            'currencyCode' => 'USD',
                            'shippingMethod' => 'shipping_method, shipping_type_formatted',
                            'paymentMethod' => 'payment_method_formatted',
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'purchase' => [
                                'actionField' => [
                                    'id' => 42,
                                ],
                                'products' => [
                                    [
                                        'id' => 'sku3',
                                        'name' => 'Product 3',
                                        'price' => 5.56,
                                        'brand' => 'Brand 3',
                                        'category' => 'Category 3',
                                        'quantity' => 10.22,
                                        'position' => 3,
                                        'variant' => 'set',
                                    ],
                                ],
                            ],
                            'currencyCode' => 'USD',
                            'shippingMethod' => 'shipping_method, shipping_type_formatted',
                            'paymentMethod' => 'payment_method_formatted',
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'purchase' => [
                                'actionField' => [
                                    'id' => 42,
                                ],
                                'products' => [
                                    [
                                        'id' => 'FREE-FORM-SKU',
                                        'name' => 'Free Form Product',
                                        'price' => 22.33,
                                        'quantity' => 11,
                                        'position' => 4,
                                        'variant' => 'set',
                                    ],
                                ],
                            ],
                            'currencyCode' => 'USD',
                            'shippingMethod' => 'shipping_method, shipping_type_formatted',
                            'paymentMethod' => 'payment_method_formatted',
                        ],
                    ],
                ],
            ],
            'without additional data' => [
                'order' => $order2,
                'lineItem1' => $lineItem21,
                'lineItem2' => $lineItem22,
                'lineItem3' => $lineItem23,
                'lineItem4' => $lineItem24,
                'tax' => null,
                'coupons' => [],
                'paymentMethod' => null,
                'expected' => [
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'purchase' => [
                                'actionField' => [
                                    'id' => 42,
                                    'revenue' => 1500.57,
                                ],
                                'products' => [
                                    [
                                        'id' => 'sku2',
                                        'name' => 'Product 2',
                                        'price' => 1.1,
                                        'brand' => 'Brand 2',
                                        'category' => 'Category 2',
                                        'quantity' => 5.5,
                                        'position' => 2,
                                        'variant' => 'item',
                                    ],
                                ],
                            ],
                            'currencyCode' => 'USD',
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'purchase' => [
                                'actionField' => [
                                    'id' => 42,
                                ],
                                'products' => [
                                    [
                                        'id' => 'sku3',
                                        'name' => 'Product 3',
                                        'price' => 5.56,
                                        'brand' => 'Brand 3',
                                        'category' => 'Category 3',
                                        'quantity' => 10.22,
                                        'position' => 3,
                                        'variant' => 'set',
                                    ],
                                ],
                            ],
                            'currencyCode' => 'USD',
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'purchase' => [
                                'actionField' => [
                                    'id' => 42,
                                ],
                                'products' => [
                                    [
                                        'id' => 'FREE-FORM-SKU',
                                        'name' => 'Free Form Product',
                                        'price' => 22.33,
                                        'quantity' => 11,
                                        'position' => 4,
                                        'variant' => 'set',
                                    ],
                                ],
                            ],
                            'currencyCode' => 'USD',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function assertTaxProviderCalled(Order $order, ?float $amount): void
    {
        $total = new ResultElement();
        $total->offsetSet(ResultElement::TAX_AMOUNT, $amount);

        $taxResult = new Result();
        $taxResult->offsetSet(Result::TOTAL, $total);

        if ($amount === null) {
            $exception = new TaxationDisabledException();
            $this->taxProvider->expects(self::once())
                ->method('loadTax')
                ->with($order)
                ->willThrowException($exception);

            $this->loggerMock
                ->expects(self::once())
                ->method('debug')
                ->with(
                    'Skipped adding tax to the GTM data layer: taxation is disabled',
                    ['order' => $order, 'exception' => $exception]
                );
        } else {
            $this->taxProvider->expects(self::once())
                ->method('loadTax')
                ->with($order)
                ->willReturn($taxResult);
        }
    }

    private function assertCouponsProviderCalled(Order $order, array $codes): void
    {
        $coupons = new ArrayCollection();
        foreach (array_merge($codes, ['', null]) as $i => $code) {
            $promotion = new PromotionStub(++$i);
            $coupon = new Coupon();
            $coupon->setPromotion($promotion);

            $coupons->add($coupon);
        }

        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($order)
            ->willReturn($coupons);

        $this->promotionRepository
            ->expects(self::once())
            ->method('getPromotionsNamesByIds')
            ->willReturnCallback(function (array $ids) use ($codes) {
                return $codes;
            });
    }

    private function prepareOrder(): array
    {
        /** @var Product $product1 */
        $product1 = $this->getEntity(Product::class, ['id' => 1001]);
        /** @var Product $product2 */
        $product2 = $this->getEntity(Product::class, ['id' => 2002]);
        /** @var Product $product3 */
        $product3 = $this->getEntity(Product::class, ['id' => 3003]);

        $lineItem1 = new OrderLineItem();
        $lineItem1->setProduct($product1)
            ->preSave();

        $productUnit2 = new ProductUnit();
        $productUnit2->setCode('item');

        $lineItem2 = new OrderLineItem();
        $lineItem2->setProduct($product2)
            ->setProductUnit($productUnit2)
            ->setQuantity(5.5)
            ->setPrice(Price::create(1.1, 'USD'))
            ->preSave();

        $productUnit3 = new ProductUnit();
        $productUnit3->setCode('set');

        $lineItem3 = new OrderLineItem();
        $lineItem3->setProduct($product3)
            ->setProductUnit($productUnit3)
            ->setQuantity(10.22)
            ->setPrice(Price::create(5.5555, 'USD'))
            ->preSave();

        $lineItem4 = new OrderLineItem();
        $lineItem4
            ->setFreeFormProduct('Free Form Product')
            ->setProductSku('FREE-FORM-SKU')
            ->setProductUnit($productUnit3)
            ->setQuantity(11)
            ->setPrice(Price::create(22.3344, 'USD'))
            ->preSave();

        /** @var Order $order */
        $order = $this->getEntity(OrderStub::class, ['id' => 42]);
        $order->setTotal(1500.5678)
            ->setCurrency('USD')
            ->addLineItem($lineItem1)
            ->addLineItem($lineItem2)
            ->addLineItem($lineItem3)
            ->addLineItem($lineItem4);

        return [$order, $lineItem1, $lineItem2, $lineItem3, $lineItem4];
    }
}
