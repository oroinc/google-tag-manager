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
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 */
class PurchaseDetailProvider
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var ProductDetailProvider */
    private $productDetailProvider;

    /** @var TaxProviderRegistry */
    private $taxProviderRegistry;

    /** @var EntityCouponsProviderInterface */
    private $entityCouponsProvider;

    /** @var ShippingMethodLabelFormatter */
    private $shippingMethodLabelFormatter;

    /** @var PaymentMethodLabelFormatter */
    private $paymentMethodLabelFormatter;

    /** @var int */
    private $batchSize = 30;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ProductDetailProvider $productDataProvider
     * @param TaxProviderRegistry $taxProviderRegistry
     * @param EntityCouponsProviderInterface $entityCouponsProvider
     * @param ShippingMethodLabelFormatter $shippingMethodLabelFormatter
     * @param PaymentMethodLabelFormatter $paymentMethodLabelFormatter
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ProductDetailProvider $productDataProvider,
        TaxProviderRegistry $taxProviderRegistry,
        EntityCouponsProviderInterface $entityCouponsProvider,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->productDetailProvider = $productDataProvider;
        $this->taxProviderRegistry = $taxProviderRegistry;
        $this->entityCouponsProvider = $entityCouponsProvider;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
    }

    /**
     * @param int $batchSize
     */
    public function setBatchSize(int $batchSize): void
    {
        if ($batchSize < 1) {
            throw new \InvalidArgumentException(sprintf('Batch size must be greater than zero, %d given.', $batchSize));
        }

        $this->batchSize = $batchSize;
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

        $data = $this->addAdditionalData(
            $order,
            [
                'event' => 'purchase',
                'ecommerce' => [
                    'currencyCode' => $order->getCurrency(),
                    'purchase' => [
                        'actionField' => [
                            'id' => $order->getId(),
                            'revenue' => (float) $order->getTotal(),
                        ],
                        'products' => $products,
                    ],
                ]
            ]
        );

        $result = [];
        foreach (array_chunk($products, $this->batchSize) as $key => $chunk) {
            $data['ecommerce']['purchase']['products'] = $chunk;

            $result[] = $data;

            if ($key === 0) {
                $data['ecommerce']['purchase']['actionField'] = ['id' => $order->getId()];
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param array $data
     * @return array
     */
    private function addAdditionalData(Order $order, array $data): array
    {
        $actionField = &$data['ecommerce']['purchase']['actionField'];

        try {
            $result = $this->taxProviderRegistry
                ->getEnabledProvider()
                ->loadTax($order);

            $taxAmount = (float) $result->getTotal()
                ->getTaxAmount();

            if (abs($taxAmount) <= 1e-6) {
                $taxAmount = 0;
            }

            $actionField['tax'] = $taxAmount;
        } catch (\Exception $e) {
        }

        $coupons = $this->entityCouponsProvider->getCoupons($order)->toArray();
        if ($coupons) {
            $coupons = array_map(
                static function (Coupon $coupon) {
                    return $coupon->getCode();
                },
                $coupons
            );

            sort($coupons);

            $actionField['coupon'] = implode(',', $coupons);
        }

        if ($order->getShippingCost()) {
            $actionField['shipping'] = $this->formatPrice($order->getShippingCost());
        }

        if ($order->getWebsite()) {
            $actionField['affiliation'] = $order->getWebsite()->getName();
        }

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
