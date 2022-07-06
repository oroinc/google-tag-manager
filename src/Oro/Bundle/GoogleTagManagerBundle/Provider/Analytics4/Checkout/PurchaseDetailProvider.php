<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\GoogleTagManagerBundle\Formatter\NumberFormatter;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\PromotionBundle\Entity\AppliedCouponsAwareInterface;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\TaxBundle\Exception\TaxationDisabledException;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 */
class PurchaseDetailProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ManagerRegistry $managerRegistry;

    private ProductDetailProvider $productDetailProvider;

    private TaxProviderRegistry $taxProviderRegistry;

    private AppliedPromotionsNamesProvider $appliedPromotionsNamesProvider;

    private ShippingMethodLabelFormatter $shippingMethodLabelFormatter;

    private PaymentMethodLabelFormatter $paymentMethodLabelFormatter;

    private NumberFormatter $numberFormatter;

    private int $batchSize;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ProductDetailProvider $productDetailProvider,
        TaxProviderRegistry $taxProviderRegistry,
        AppliedPromotionsNamesProvider $appliedPromotionsNamesProvider,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter,
        NumberFormatter $numberFormatter,
        int $batchSize = 30
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->productDetailProvider = $productDetailProvider;
        $this->taxProviderRegistry = $taxProviderRegistry;
        $this->appliedPromotionsNamesProvider = $appliedPromotionsNamesProvider;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
        $this->numberFormatter = $numberFormatter;
        $this->batchSize = $batchSize;

        $this->logger = new NullLogger();
    }

    public function getData(Checkout $checkout): array
    {
        $order = $this->getOrder($checkout);
        if (!$order) {
            return [];
        }

        $data = $this->getMainData($order);

        $chunks = $this->splitInChunks($data);

        // First chunk must contain the most complete event data.
        $this->addAdditionalData($order, $chunks[0]);

        return $chunks;
    }

    private function getMainData(Order $order): array
    {
        $data = [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $order->getIdentifier(),
                'currency' => $order->getCurrency(),
                'items' => $this->getItems($order),
            ],
        ];

        if ($order->getShippingMethod()) {
            $data['ecommerce']['shipping_tier'] = $this->shippingMethodLabelFormatter
                ->formatShippingMethodWithTypeLabel($order->getShippingMethod(), $order->getShippingMethodType());
        }

        $paymentMethod = $this->getPaymentMethod($order);
        if ($paymentMethod) {
            $data['ecommerce']['payment_type'] = $this->paymentMethodLabelFormatter
                ->formatPaymentMethodLabel($paymentMethod);
        }

        return $data;
    }

    private function getItems(Order $order): array
    {
        $items = [];
        foreach ($order->getLineItems() as $key => $lineItem) {
            if ($lineItem->getProduct()) {
                $productData = $this->productDetailProvider->getData($lineItem->getProduct());
            } else {
                $productData = array_filter([
                    'item_id' => $lineItem->getProductSku(),
                    'item_name' => $lineItem->getFreeFormProduct(),
                ]);
            }
            if (!$productData) {
                continue;
            }

            $items[] = array_merge(
                $productData,
                [
                    'item_variant' => $lineItem->getProductUnitCode(),
                    'price' => $this->formatPrice($lineItem->getPrice()),
                    'quantity' => $lineItem->getQuantity(),
                    'index' => $key + 1,
                ]
            );
        }

        return $items;
    }

    private function splitInChunks(array $data): array
    {
        return array_map(
            static function (array $chunk) use ($data) {
                $data['ecommerce']['items'] = $chunk;

                return $data;
            },
            array_chunk($data['ecommerce']['items'], $this->batchSize)
        );
    }

    private function addAdditionalData(Order $order, array &$data): void
    {
        $data['ecommerce']['value'] = $this->formatPriceValue($order->getTotal());

        try {
            $result = $this->taxProviderRegistry
                ->getEnabledProvider()
                ->loadTax($order);

            $taxAmount = (float)$result->getTotal()->getTaxAmount();

            if (abs($taxAmount) <= 1e-6) {
                $taxAmount = 0;
            }

            $data['ecommerce']['tax'] = $this->formatPriceValue($taxAmount);
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

        /** @var Order|AppliedCouponsAwareInterface $order */
        $promotionsNames = $this->appliedPromotionsNamesProvider->getAppliedPromotionsNames($order);
        if ($promotionsNames) {
            $data['ecommerce']['coupon'] = implode(',', $promotionsNames);
        }

        if ($order->getShippingCost()) {
            $data['ecommerce']['shipping'] = $this->formatPrice($order->getShippingCost());
        }

        if ($order->getWebsite()) {
            $data['ecommerce']['affiliation'] = $order->getWebsite()->getName();
        }
    }

    private function getOrder(Checkout $checkout): ?Order
    {
        $orderData = $checkout->getCompletedData()->getOrderData();

        return $this->managerRegistry
            ->getRepository(Order::class)
            ->findOneBy($orderData['entityId']);
    }

    private function formatPrice(?Price $price): float
    {
        return $price ? $this->formatPriceValue($price->getValue()) : 0.0;
    }

    private function formatPriceValue(float $priceValue): float
    {
        return $this->numberFormatter->formatPriceValue($priceValue);
    }

    private function getPaymentMethod(Order $order): ?string
    {
        $repository = $this->managerRegistry->getRepository(PaymentTransaction::class);

        /** @var PaymentTransactionRepository $repository */
        $methods = $repository->getPaymentMethods(Order::class, [$order->getId()]);
        if (!isset($methods[$order->getId()])) {
            return null;
        }

        return array_shift($methods[$order->getId()]);
    }
}
