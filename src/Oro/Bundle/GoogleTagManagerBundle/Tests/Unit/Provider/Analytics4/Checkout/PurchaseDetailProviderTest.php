<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Analytics4\Checkout;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\OrderBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Tests\Unit\Stub\ProductStub;
use Oro\Bundle\PromotionBundle\Tests\Unit\Entity\Stub\Order as OrderStub;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\TaxBundle\Exception\TaxationDisabledException;
use Oro\Bundle\TaxBundle\Model\Result;
use Oro\Bundle\TaxBundle\Model\ResultElement;
use Oro\Bundle\TaxBundle\Provider\TaxProviderInterface;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class PurchaseDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject $productDetailProvider;

    private TaxProviderInterface|\PHPUnit\Framework\MockObject\MockObject $taxProvider;

    private AppliedPromotionsNamesProvider|\PHPUnit\Framework\MockObject\MockObject $appliedPromotionsNamesProvider;

    private PurchaseDetailProvider $provider;

    private OrderRepository|\PHPUnit\Framework\MockObject\MockObject $orderRepository;

    private PaymentTransactionRepository|\PHPUnit\Framework\MockObject\MockObject $paymentTransactionRepository;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->paymentTransactionRepository = $this->createMock(PaymentTransactionRepository::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getRepository')
            ->willReturnMap(
                [
                    [Order::class, null, $this->orderRepository],
                    [PaymentTransaction::class, null, $this->paymentTransactionRepository],
                ]
            );

        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        $this->taxProvider = $this->createMock(TaxProviderInterface::class);

        $taxProviderRegistry = $this->createMock(TaxProviderRegistry::class);
        $taxProviderRegistry->expects(self::any())
            ->method('getEnabledProvider')
            ->willReturn($this->taxProvider);

        $this->appliedPromotionsNamesProvider = $this->createMock(AppliedPromotionsNamesProvider::class);

        $shippingMethodLabelFormatter = $this->createMock(ShippingMethodLabelFormatter::class);
        $shippingMethodLabelFormatter->expects(self::any())
            ->method('formatShippingMethodWithTypeLabel')
            ->willReturnCallback(
                static function (string $shippingMethod, string $shippingType) {
                    return $shippingMethod . ShippingMethodLabelFormatter::DELIMITER . $shippingType . '_formatted';
                }
            );

        $paymentMethodLabelFormatter = $this->createMock(PaymentMethodLabelFormatter::class);
        $paymentMethodLabelFormatter->expects(self::any())
            ->method('formatPaymentMethodLabel')
            ->willReturnCallback(static fn (string $paymentMethod) => $paymentMethod . '_formatted');

        $this->provider = new PurchaseDetailProvider(
            $managerRegistry,
            $this->productDetailProvider,
            $taxProviderRegistry,
            $this->appliedPromotionsNamesProvider,
            $shippingMethodLabelFormatter,
            $paymentMethodLabelFormatter,
            1
        );

        $this->setUpLoggerMock($this->provider);
    }

    public function testGetPurchaseDataWithoutOrder(): void
    {
        $id = 42;

        $this->orderRepository
            ->expects(self::once())
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

        unset($expected[0]['ecommerce']['tax']);

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
                'promotionsNames' => ['CODE1', 'CODE2'],
                'paymentMethod' => 'payment_method',
                'expected' => [
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'transaction_id' => 42,
                            'value' => 1500.15,
                            'currency' => 'USD',
                            'tax' => 11.8,
                            'shipping' => 20.20,
                            'affiliation' => 'Test Website',
                            'coupon' => 'CODE1,CODE2',
                            'items' => [
                                [
                                    'item_id' => 'sku2',
                                    'item_name' => 'Product 2',
                                    'price' => 1.1,
                                    'item_brand' => 'Brand 2',
                                    'item_category' => 'Category 2',
                                    'quantity' => 5.5,
                                    'index' => 2,
                                    'item_variant' => 'item',
                                ],
                            ],
                            'shipping_tier' => 'shipping_method, shipping_type_formatted',
                            'payment_type' => 'payment_method_formatted',
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'transaction_id' => 42,
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku3',
                                    'item_name' => 'Product 3',
                                    'price' => 5.5,
                                    'item_brand' => 'Brand 3',
                                    'item_category' => 'Category 3',
                                    'quantity' => 10.22,
                                    'index' => 3,
                                    'item_variant' => 'set',
                                ],
                            ],
                            'shipping_tier' => 'shipping_method, shipping_type_formatted',
                            'payment_type' => 'payment_method_formatted',
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'transaction_id' => 42,
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'FREE-FORM-SKU',
                                    'item_name' => 'Free Form Product',
                                    'price' => 22.0,
                                    'quantity' => 11,
                                    'index' => 4,
                                    'item_variant' => 'set',
                                ],
                            ],
                            'shipping_tier' => 'shipping_method, shipping_type_formatted',
                            'payment_type' => 'payment_method_formatted',
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
                'promotionsNames' => [],
                'paymentMethod' => null,
                'expected' => [
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'transaction_id' => 42,
                            'value' => 1500.15,
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku2',
                                    'item_name' => 'Product 2',
                                    'price' => 1.1,
                                    'item_brand' => 'Brand 2',
                                    'item_category' => 'Category 2',
                                    'quantity' => 5.5,
                                    'index' => 2,
                                    'item_variant' => 'item',
                                ],
                            ],
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'transaction_id' => 42,
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku3',
                                    'item_name' => 'Product 3',
                                    'price' => 5.5,
                                    'item_brand' => 'Brand 3',
                                    'item_category' => 'Category 3',
                                    'quantity' => 10.22,
                                    'index' => 3,
                                    'item_variant' => 'set',
                                ],
                            ],
                        ],
                    ],
                    [
                        'event' => 'purchase',
                        'ecommerce' => [
                            'transaction_id' => 42,
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'FREE-FORM-SKU',
                                    'item_name' => 'Free Form Product',
                                    'price' => 22.0,
                                    'quantity' => 11,
                                    'index' => 4,
                                    'item_variant' => 'set',
                                ],
                            ],
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

    private function prepareOrder(): array
    {
        $product1 = (new ProductStub())
            ->setId(1001);
        $product2 = (new ProductStub())
            ->setId(2002);
        $product3 = (new ProductStub())
            ->setId(3003);

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
            ->setPrice(Price::create(5.5, 'USD'))
            ->preSave();

        $lineItem4 = new OrderLineItem();
        $lineItem4
            ->setFreeFormProduct('Free Form Product')
            ->setProductSku('FREE-FORM-SKU')
            ->setProductUnit($productUnit3)
            ->setQuantity(11)
            ->setPrice(Price::create(22, 'USD'))
            ->preSave();

        $order = (new OrderStub())
            ->setIdentifier(42)
            ->setTotal(1500.15)
            ->setCurrency('USD')
            ->addLineItem($lineItem1)
            ->addLineItem($lineItem2)
            ->addLineItem($lineItem3)
            ->addLineItem($lineItem4);

        return [$order, $lineItem1, $lineItem2, $lineItem3, $lineItem4];
    }
}
