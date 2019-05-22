<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 */
class PurchaseDetailProvider
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var ProductDetailProvider
     */
    private $productDetailProvider;

    /**
     * @var ShippingMethodLabelFormatter
     */
    private $shippingMethodLabelFormatter;

    /**
     * @var PaymentMethodLabelFormatter
     */
    private $paymentMethodLabelFormatter;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ProductDetailProvider $productDataProvider
     * @param ShippingMethodLabelFormatter $shippingMethodLabelFormatter
     * @param PaymentMethodLabelFormatter $paymentMethodLabelFormatter
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ProductDetailProvider $productDataProvider,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->productDetailProvider = $productDataProvider;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
    }

    /**
     * @param Checkout $checkout
     * @return array
     */
    public function getData(Checkout $checkout): array
    {
        $order = $this->getOrder($checkout);
        if (!$order) {
            return [];
        }

        $products = [];
        foreach ($order->getLineItems() as $key => $lineItem) {
            $productData = $this->productDetailProvider->getData($lineItem->getProduct());
            if (!$productData) {
                continue;
            }

            $products[] = array_merge(
                $productData,
                [
                    'variant' => $lineItem->getProductUnitCode(),
                    'price' => $this->formatPrice($lineItem->getPrice()),
                    'quantity' => $lineItem->getQuantity(),
                    'position' => $key + 1,
                ]
            );
        }

        $data = [
            'event' => 'purchase',
            'ecommerce' => [
                'currencyCode' => $order->getCurrency(),
                'purchase' => [
                    'actionField' => [
                        'id' => $order->getId(),
                        'revenue' => (float) $order->getTotal(),
                        'shipping' => $this->formatPrice($order->getShippingCost())
                    ],
                    'products' => $products,
                ],
            ]
        ];

        if ($order->getShippingMethod()) {
            $data['ecommerce']['shippingMethod'] = $this->shippingMethodLabelFormatter
                ->formatShippingMethodWithTypeLabel($order->getShippingMethod(), $order->getShippingMethodType());
        }

        $paymentMethod = $this->getPaymentMethod($order);
        if ($paymentMethod) {
            $data['ecommerce']['paymentMethod'] = $this->paymentMethodLabelFormatter
                ->formatPaymentMethodLabel($paymentMethod);
        }

        return $data;
    }

    /**
     * @param Checkout $checkout
     * @return Order|null
     */
    private function getOrder(Checkout $checkout): ?Order
    {
        $orderData = $checkout->getCompletedData()->getOrderData();

        return $this->doctrineHelper
            ->getEntityRepositoryForClass(Order::class)
            ->findOneBy($orderData['entityId']);
    }

    /**
     * @param Price|null $price
     * @return float
     */
    private function formatPrice(?Price $price): float
    {
        return $price ? (float) $price->getValue() : 0.0;
    }

    /**
     * @param Order $order
     * @return string|null
     */
    private function getPaymentMethod(Order $order): ?string
    {
        $repository = $this->doctrineHelper->getEntityRepositoryForClass(PaymentTransaction::class);

        /** @var PaymentTransactionRepository $repository */
        $methods = $repository->getPaymentMethods(Order::class, [$order->getId()]);
        if (!isset($methods[$order->getId()])) {
            return null;
        }

        return array_shift($methods[$order->getId()]);
    }
}
