<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 */
class CheckoutDetailProvider
{
    private ProductDetailProvider $productDetailProvider;

    private ProductPriceProviderInterface $productPriceProvider;

    private ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory;

    private ShippingMethodLabelFormatter $shippingMethodLabelFormatter;

    private PaymentMethodLabelFormatter $paymentMethodLabelFormatter;

    private int $batchSize;

    private ProductPriceCriteriaFactoryInterface $productPriceCriteriaFactory;

    public function __construct(
        ProductDetailProvider $productDataProvider,
        ProductPriceProviderInterface $productPriceProvider,
        ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter,
        ProductPriceCriteriaFactoryInterface $productPriceCriteriaFactory,
        int $batchSize = 30
    ) {
        $this->productDetailProvider = $productDataProvider;
        $this->productPriceProvider = $productPriceProvider;
        $this->priceScopeCriteriaFactory = $priceScopeCriteriaFactory;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
        $this->productPriceCriteriaFactory = $productPriceCriteriaFactory;
        $this->batchSize = $batchSize;
    }

    public function getBeginCheckoutData(Checkout $checkout): array
    {
        $data = [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'currency' => $checkout->getCurrency(),
                'items' => $this->getItems($checkout),
            ],
        ];

        return $this->splitInChunks($data);
    }

    public function getShippingInfoData(Checkout $checkout): array
    {
        $data = [
            'event' => 'add_shipping_info',
            'ecommerce' => [
                'currency' => $checkout->getCurrency(),
                'items' => $this->getItems($checkout),
            ],
        ];

        if ($checkout->getShippingMethod() && $checkout->getShippingMethodType()) {
            $data['ecommerce']['shipping_tier'] = $this->shippingMethodLabelFormatter
                ->formatShippingMethodWithTypeLabel($checkout->getShippingMethod(), $checkout->getShippingMethodType());
        }

        return $this->splitInChunks($data);
    }

    public function getPaymentInfoData(Checkout $checkout): array
    {
        $data = [
            'event' => 'add_payment_info',
            'ecommerce' => [
                'currency' => $checkout->getCurrency(),
                'items' => $this->getItems($checkout),
            ],
        ];

        if ($checkout->getPaymentMethod()) {
            $data['ecommerce']['payment_type'] = $this->paymentMethodLabelFormatter
                ->formatPaymentMethodLabel($checkout->getPaymentMethod());
        }

        return $this->splitInChunks($data);
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

    private function getItems(Checkout $checkout): array
    {
        $searchScope = $this->priceScopeCriteriaFactory->createByContext($checkout);

        $products = [];
        foreach ($checkout->getLineItems() as $key => $lineItem) {
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

            $products[] = array_merge(
                $productData,
                [
                    'item_variant' => $lineItem->getProductUnitCode(),
                    'price' => $this->getItemPrice($lineItem, $searchScope, $checkout->getCurrency()),
                    'quantity' => $lineItem->getQuantity(),
                    'index' => $key + 1,
                ]
            );
        }

        return $products;
    }

    private function getItemPrice(
        CheckoutLineItem $item,
        ProductPriceScopeCriteriaInterface $scope,
        string $currency
    ): float {
        if ($item->isPriceFixed()) {
            return $item->getPrice() ? (float)$item->getPrice()->getValue() : 0.0;
        }

        $productCriteria = $this->productPriceCriteriaFactory->createFromProductLineItem(
            $item,
            $item->getCurrency() ?: $currency
        );

        if (!$productCriteria) {
            return 0.0;
        }

        $prices = $this->productPriceProvider->getMatchedPrices([$productCriteria], $scope);

        return isset($prices[$productCriteria->getIdentifier()])
            ? (float)$prices[$productCriteria->getIdentifier()]->getValue()
            : 0.0;
    }
}
