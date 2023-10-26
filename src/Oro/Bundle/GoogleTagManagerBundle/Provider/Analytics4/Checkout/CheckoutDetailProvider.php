<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\PricingBundle\SubtotalProcessor\Model\SubtotalProviderInterface;
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

    private ?SubtotalProviderInterface $checkoutSubtotalProvider = null;

    public function __construct(
        ProductDetailProvider $productDataProvider,
        ProductPriceProviderInterface $productPriceProvider,
        ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter,
        int $batchSize = 30
    ) {
        $this->productDetailProvider = $productDataProvider;
        $this->productPriceProvider = $productPriceProvider;
        $this->priceScopeCriteriaFactory = $priceScopeCriteriaFactory;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
        $this->batchSize = $batchSize;
    }

    public function setCheckoutSubtotalProvider(?SubtotalProviderInterface $checkoutSubtotalProvider): void
    {
        $this->checkoutSubtotalProvider = $checkoutSubtotalProvider;
    }

    public function getBeginCheckoutData(Checkout $checkout): array
    {
        $data = [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'items' => $this->getItems($checkout),
            ],
        ];

        $chunks = $this->splitInChunks($data);

        // First chunk must contain the most complete event data.
        $this->addAdditionalData($checkout, $chunks[0]);

        return $chunks;
    }

    public function getShippingInfoData(Checkout $checkout): array
    {
        $data = [
            'event' => 'add_shipping_info',
            'ecommerce' => [
                'items' => $this->getItems($checkout),
            ],
        ];

        if ($checkout->getShippingMethod() && $checkout->getShippingMethodType()) {
            $data['ecommerce']['shipping_tier'] = $this->shippingMethodLabelFormatter
                ->formatShippingMethodWithTypeLabel($checkout->getShippingMethod(), $checkout->getShippingMethodType());
        }

        $chunks = $this->splitInChunks($data);

        // First chunk must contain the most complete event data.
        $this->addAdditionalData($checkout, $chunks[0]);

        return $chunks;
    }

    public function getPaymentInfoData(Checkout $checkout): array
    {
        $data = [
            'event' => 'add_payment_info',
            'ecommerce' => [
                'items' => $this->getItems($checkout),
            ],
        ];

        if ($checkout->getPaymentMethod()) {
            $data['ecommerce']['payment_type'] = $this->paymentMethodLabelFormatter
                ->formatPaymentMethodLabel($checkout->getPaymentMethod());
        }

        $chunks = $this->splitInChunks($data);

        // First chunk must contain the most complete event data.
        $this->addAdditionalData($checkout, $chunks[0]);

        return $chunks;
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

        $criteria = $this->prepareProductsPriceCriteria($item, $currency);
        if (!$criteria) {
            return 0.0;
        }

        $prices = $this->productPriceProvider->getMatchedPrices([$criteria], $scope);

        return isset($prices[$criteria->getIdentifier()])
            ? (float)$prices[$criteria->getIdentifier()]->getValue()
            : 0.0;
    }

    private function prepareProductsPriceCriteria(CheckoutLineItem $item, string $currency): ?ProductPriceCriteria
    {
        if (!$item->getProduct() || !$item->getProductUnit() || !$item->getQuantity()) {
            return null;
        }

        return new ProductPriceCriteria(
            $item->getProduct(),
            $item->getProductUnit(),
            (float)$item->getQuantity(),
            $item->getCurrency() ?: $currency
        );
    }

    /**
     * @param Checkout $checkout
     * @param array $data GTM data layer data
     */
    private function addAdditionalData(Checkout $checkout, array &$data): void
    {
        $data['ecommerce']['currency'] = $checkout->getCurrency();
        $data['ecommerce']['value'] = 0.0;

        if ($this->checkoutSubtotalProvider !== null) {
            $subtotal = $this->checkoutSubtotalProvider->getSubtotal($checkout);
            $data['ecommerce']['value'] = (float)$subtotal?->getAmount();
        }
    }
}
