<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Checkout;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;
use Oro\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\TaxBundle\Model\Result;
use Oro\Bundle\TaxBundle\Model\ResultElement;
use Oro\Bundle\TaxBundle\Provider\TaxProviderInterface;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class PurchaseDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productDetailProvider;

    /** @var TaxProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $taxProvider;

    /** @var EntityCouponsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityCouponsProvider;

    /** @var ShippingMethodLabelFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $shippingMethodLabelFormatter;

    /** @var PaymentMethodLabelFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethodLabelFormatter;

    /** @var PurchaseDetailProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);

        $this->taxProvider = $this->createMock(TaxProviderInterface::class);

        /** @var TaxProviderRegistry|\PHPUnit\Framework\MockObject\MockObject $taxProviderRegistry */
        $taxProviderRegistry = $this->createMock(TaxProviderRegistry::class);
        $taxProviderRegistry->expects($this->any())
            ->method('getEnabledProvider')
            ->willReturn($this->taxProvider);

        $this->entityCouponsProvider = $this->createMock(EntityCouponsProviderInterface::class);

        $this->shippingMethodLabelFormatter = $this->createMock(ShippingMethodLabelFormatter::class);
        $this->shippingMethodLabelFormatter->expects($this->any())
            ->method('formatShippingMethodWithTypeLabel')
            ->willReturnCallback(
                function (string $shippingMethod, string $shippingType) {
                    return $shippingMethod . ShippingMethodLabelFormatter::DELIMITER . $shippingType . '_formatted';
                }
            );

        $this->paymentMethodLabelFormatter = $this->createMock(PaymentMethodLabelFormatter::class);
        $this->paymentMethodLabelFormatter->expects($this->any())
            ->method('formatPaymentMethodLabel')
            ->willReturnCallback(
                function (string $paymentMethod) {
                    return $paymentMethod . '_formatted';
                }
            );

        $this->provider = new PurchaseDetailProvider(
            $this->doctrineHelper,
            $this->productDetailProvider,
            $taxProviderRegistry,
            $this->entityCouponsProvider,
            $this->shippingMethodLabelFormatter,
            $this->paymentMethodLabelFormatter,
            1
        );
    }

    public function testGetPurchaseDataWithoutOrder(): void
    {
        $id = 42;

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $id])
            ->willReturn(null);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Order::class)
            ->willReturn($repository);

        $checkout = new Checkout();
        $checkout->getCompletedData()
            ->offsetSet('orders', [['entityId' => ['id' => $id]]]);

        $this->productDetailProvider->expects($this->never())
            ->method('getData');

        $this->assertEquals([], $this->provider->getData($checkout));
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
        array $coupons,
        ?string $paymentMethod,
        array $expected
    ): void {
        $orderRepository = $this->createMock(ObjectRepository::class);
        $orderRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $order->getId()])
            ->willReturn($order);

        $paymentTransactionRepository = $this->createMock(PaymentTransactionRepository::class);
        $paymentTransactionRepository->expects($this->once())
            ->method('getPaymentMethods')
            ->with(Order::class, [$order->getId()])
            ->willReturn([$order->getId() => [$paymentMethod]]);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepositoryForClass')
            ->willReturnMap(
                [
                    [Order::class, $orderRepository],
                    [PaymentTransaction::class, $paymentTransactionRepository],
                ]
            );

        $checkout = new Checkout();
        $checkout->getCompletedData()
            ->offsetSet('orders', [['entityId' => ['id' => $order->getId()]]]);

        $this->productDetailProvider->expects($this->exactly(3))
            ->method('getData')
            ->withConsecutive(
                [$this->identicalTo($lineItem1->getProduct())],
                [$this->identicalTo($lineItem2->getProduct())],
                [$this->identicalTo($lineItem3->getProduct())]
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

        $this->assertEquals($expected, $this->provider->getData($checkout));
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
                                    'revenue' => 1500.15,
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
                                    ]
                                ],
                            ],
                            'currencyCode' => 'USD',
                            'shippingMethod' => 'shipping_method, shipping_type_formatted',
                            'paymentMethod' => 'payment_method_formatted',
                        ]
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
                                        'price' => 5.5,
                                        'brand' => 'Brand 3',
                                        'category' => 'Category 3',
                                        'quantity' => 10.22,
                                        'position' => 3,
                                        'variant' => 'set',
                                    ]
                                ],
                            ],
                            'currencyCode' => 'USD',
                            'shippingMethod' => 'shipping_method, shipping_type_formatted',
                            'paymentMethod' => 'payment_method_formatted',
                        ]
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
                                        'price' => 22.0,
                                        'quantity' => 11,
                                        'position' => 4,
                                        'variant' => 'set'
                                    ]
                                ],
                            ],
                            'currencyCode' => 'USD',
                            'shippingMethod' => 'shipping_method, shipping_type_formatted',
                            'paymentMethod' => 'payment_method_formatted',
                        ]
                    ]
                ]
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
                                    'revenue' => 1500.15,
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
                                    ]
                                ],
                            ],
                            'currencyCode' => 'USD',
                        ]
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
                                        'price' => 5.5,
                                        'brand' => 'Brand 3',
                                        'category' => 'Category 3',
                                        'quantity' => 10.22,
                                        'position' => 3,
                                        'variant' => 'set',
                                    ]
                                ],
                            ],
                            'currencyCode' => 'USD',
                        ]
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
                                        'price' => 22.0,
                                        'quantity' => 11,
                                        'position' => 4,
                                        'variant' => 'set'
                                    ]
                                ],
                            ],
                            'currencyCode' => 'USD'
                        ]
                    ]
                ]
            ]
        ];
    }

    private function assertTaxProviderCalled(Order $order, ?float $amount): void
    {
        $total = new ResultElement();
        $total->offsetSet(ResultElement::TAX_AMOUNT, $amount);

        $taxResult = new Result();
        $taxResult->offsetSet(Result::TOTAL, $total);

        if ($amount === null) {
            $this->taxProvider->expects($this->once())
                ->method('loadTax')
                ->with($order)
                ->willThrowException(new \Exception());
        } else {
            $this->taxProvider->expects($this->once())
                ->method('loadTax')
                ->with($order)
                ->willReturn($taxResult);
        }
    }

    private function assertCouponsProviderCalled(Order $order, array $codes): void
    {
        $coupons = new ArrayCollection();
        foreach (array_merge($codes, ['', null]) as $code) {
            $rule = new Rule();
            $rule->setName($code);

            $promotion = new Promotion();
            $promotion->setRule($rule);

            $coupon = new Coupon();
            $coupon->setPromotion($promotion);

            $coupons->add($coupon);
        }

        $this->entityCouponsProvider->expects($this->once())
            ->method('getCoupons')
            ->with($order)
            ->willReturn($coupons);
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

        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => 42]);
        $order->setTotal(1500.15)
            ->setCurrency('USD')
            ->addLineItem($lineItem1)
            ->addLineItem($lineItem2)
            ->addLineItem($lineItem3)
            ->addLineItem($lineItem4);

        return [$order, $lineItem1, $lineItem2, $lineItem3, $lineItem4];
    }
}
