<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Checkout;

use Doctrine\Common\Persistence\ObjectRepository;
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
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Component\Testing\Unit\EntityTrait;

class PurchaseDetailProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productDetailProvider;

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
            $this->shippingMethodLabelFormatter,
            $this->paymentMethodLabelFormatter
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

    public function testGetPurchaseData(): void
    {
        [$order, $lineItem1, $lineItem2] = $this->prepareOrder();

        $orderRepository = $this->createMock(ObjectRepository::class);
        $orderRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $order->getId()])
            ->willReturn($order);

        $paymentTransactionRepository = $this->createMock(PaymentTransactionRepository::class);
        $paymentTransactionRepository->expects($this->once())
            ->method('getPaymentMethods')
            ->with(Order::class, [$order->getId()])
            ->willReturn([$order->getId() => ['payment_method']]);

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

        $this->productDetailProvider->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive(
                [$this->identicalTo($lineItem1->getProduct())],
                [$this->identicalTo($lineItem2->getProduct())]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    'id' => 'sku2',
                    'name' => 'Product 2',
                    'brand' => 'Brand 2',
                    'category' => 'Category 2',
                ]
            );

        $this->assertEquals(
            [
                'event' => 'purchase',
                'ecommerce' => [
                    'purchase' => [
                        'actionField' => [
                            'id' => 42,
                            'revenue' => 100500.15,
                            'shipping' => 0.0,
                        ],
                        'products' => [
                            [
                                'id' => 'sku2',
                                'name' => 'Product 2',
                                'price' => 1.10,
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
                ],
            ],
            $this->provider->getData($checkout)
        );
    }

    /**
     * @return array
     */
    private function prepareOrder(): array
    {
        /** @var Product $product1 */
        $product1 = $this->getEntity(Product::class, ['id' => 1001]);
        /** @var Product $product2 */
        $product2 = $this->getEntity(Product::class, ['id' => 2002]);

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

        /** @var Order $order */
        $order = $this->getEntity(Order::class, ['id' => 42]);
        $order->setTotal(100500.15)
            ->setCurrency('USD')
            ->addLineItem($lineItem1)
            ->addLineItem($lineItem2)
            ->setShippingMethod('shipping_method')
            ->setShippingMethodType('shipping_type');

        return [$order, $lineItem1, $lineItem2];
    }
}
