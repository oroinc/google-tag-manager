<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\PromotionBundle\Entity\AppliedCouponsAwareInterface;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\TaxBundle\Exception\TaxationDisabledException;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 *
 * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
 */
class PurchaseDetailProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
    private $batchSize;

    private ?AppliedPromotionsNamesProvider $appliedPromotionsNamesProvider = null;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        ProductDetailProvider $productDataProvider,
        TaxProviderRegistry $taxProviderRegistry,
        EntityCouponsProviderInterface $entityCouponsProvider,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter,
        int $batchSize = 30
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->productDetailProvider = $productDataProvider;
        $this->taxProviderRegistry = $taxProviderRegistry;
        $this->entityCouponsProvider = $entityCouponsProvider;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
        $this->batchSize = $batchSize;
    }

    public function setAppliedPromotionsNamesProvider(
        ?AppliedPromotionsNamesProvider $appliedPromotionsNamesProvider
    ): void {
        $this->appliedPromotionsNamesProvider = $appliedPromotionsNamesProvider;
    }

    public function getData(Checkout $checkout): array
    {
        $order = $this->getOrder($checkout);
        if (!$order) {
            return [];
        }

        $products = [];
        foreach ($order->getLineItems() as $key => $lineItem) {
            if ($lineItem->getProduct()) {
                $productData = $this->productDetailProvider->getData($lineItem->getProduct());
            } else {
                $productData = array_filter([
                    'id' => $lineItem->getProductSku(),
                    'name' => $lineItem->getFreeFormProduct()
                ]);
            }
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

    private function addAdditionalData(Order $order, array $data): array
    {
        $actionField = &$data['ecommerce']['purchase']['actionField'];

        try {
            $result = $this->taxProviderRegistry
                ->getEnabledProvider()
                ->loadTax($order);

            $taxAmount = (float) $result->getTotal()->getTaxAmount();

            if (abs($taxAmount) <= 1e-6) {
                $taxAmount = 0;
            }

            $actionField['tax'] = $taxAmount;
        } catch (TaxationDisabledException $exception) {
            $this->logger->debug(
                'Skipped adding tax to the GTM data layer: taxation is disabled',
                ['order' => $order, 'exception' => $exception]
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Skipped adding tax to the GTM data layer due to an unexpected error: {message}',
                ['order' => $order, 'throwable' => $throwable, 'message' => $throwable->getMessage()]
            );
        }

        $promotions = $this->getPromotions($order);
        if ($promotions) {
            $actionField['coupon'] = implode(',', $promotions);
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

    private function getPromotions(Order $order): array
    {
        if ($this->appliedPromotionsNamesProvider) {
            /** @var Order|AppliedCouponsAwareInterface $order */
            return $this->appliedPromotionsNamesProvider->getAppliedPromotionsNames($order);
        }

        /** @var Coupon[] $coupons */
        $coupons = $this->entityCouponsProvider->getCoupons($order)->toArray();
        if (!$coupons) {
            return [];
        }

        $promotionIds = [];
        foreach ($coupons as $coupon) {
            $promotion = $coupon->getPromotion();
            if (!$promotion || !$promotion->getId()) {
                continue;
            }

            $promotionIds[] = $promotion->getId();
        }

        $promotionsNames = $this->doctrineHelper
            ->getEntityRepositoryForClass(Promotion::class)
            ->getPromotionsNamesByIds($promotionIds);
        $promotionsNames = array_values(array_unique(array_filter($promotionsNames)));

        sort($promotionsNames);

        return $promotionsNames;
    }

    private function getOrder(Checkout $checkout): ?Order
    {
        $orderData = $checkout->getCompletedData()->getOrderData();

        return $this->doctrineHelper
            ->getEntityRepositoryForClass(Order::class)
            ->findOneBy($orderData['entityId']);
    }

    private function formatPrice(?Price $price): float
    {
        return $price ? (float) $price->getValue() : 0.0;
    }

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
