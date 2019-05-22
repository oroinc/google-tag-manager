<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\PaymentBundle\Formatter\PaymentMethodLabelFormatter;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ShippingBundle\Formatter\ShippingMethodLabelFormatter;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 */
class CheckoutDetailProvider
{
    /**
     * @var ProductDetailProvider
     */
    private $productDetailProvider;

    /**
     * @var CheckoutStepProvider
     */
    private $checkoutStepProvider;

    /**
     * @var ProductPriceProviderInterface
     */
    private $productPriceProvider;

    /**
     * @var ProductPriceScopeCriteriaFactoryInterface
     */
    private $priceScopeCriteriaFactory;

    /**
     * @var ShippingMethodLabelFormatter
     */
    private $shippingMethodLabelFormatter;

    /**
     * @var PaymentMethodLabelFormatter
     */
    private $paymentMethodLabelFormatter;

    /**
     * @param ProductDetailProvider $productDataProvider
     * @param CheckoutStepProvider $checkoutStepProvider
     * @param ProductPriceProviderInterface $productPriceProvider
     * @param ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory
     * @param ShippingMethodLabelFormatter $shippingMethodLabelFormatter
     * @param PaymentMethodLabelFormatter $paymentMethodLabelFormatter
     */
    public function __construct(
        ProductDetailProvider $productDataProvider,
        CheckoutStepProvider $checkoutStepProvider,
        ProductPriceProviderInterface $productPriceProvider,
        ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory,
        ShippingMethodLabelFormatter $shippingMethodLabelFormatter,
        PaymentMethodLabelFormatter $paymentMethodLabelFormatter
    ) {
        $this->productDetailProvider = $productDataProvider;
        $this->checkoutStepProvider = $checkoutStepProvider;
        $this->productPriceProvider = $productPriceProvider;
        $this->priceScopeCriteriaFactory = $priceScopeCriteriaFactory;
        $this->shippingMethodLabelFormatter = $shippingMethodLabelFormatter;
        $this->paymentMethodLabelFormatter = $paymentMethodLabelFormatter;
    }

    /**
     * @param Checkout $checkout
     * @return array
     */
    public function getData(Checkout $checkout): array
    {
        /** @var WorkflowStep $step */
        [$step, $position] = $this->checkoutStepProvider->getData($checkout);
        if (!$position) {
            return [];
        }

        $searchScope = $this->priceScopeCriteriaFactory->createByContext($checkout);

        $products = [];
        foreach ($checkout->getLineItems() as $key => $lineItem) {
            $productData = $this->productDetailProvider->getData($lineItem->getProduct());
            if (!$productData) {
                continue;
            }

            $products[] = array_merge(
                $productData,
                [
                    'variant' => $lineItem->getProductUnitCode(),
                    'price' => $this->getItemPrice($lineItem, $searchScope, $checkout->getCurrency()),
                    'quantity' => $lineItem->getQuantity(),
                    'position' => $key + 1,
                ]
            );
        }

        $data = [
            'event' => 'checkout',
            'ecommerce' => [
                'currencyCode' => $checkout->getCurrency(),
                'checkout' => [
                    'actionField' => [
                        'step' => $position,
                        'option' => $step->getName(),
                        'affiliation' => $step->getDefinition()->getName(),
                    ],
                    'products' => $products,
                ],
            ]
        ];

        if ($checkout->getShippingMethod()) {
            $data['ecommerce']['shippingMethod'] = $this->shippingMethodLabelFormatter
                ->formatShippingMethodWithTypeLabel($checkout->getShippingMethod(), $checkout->getShippingMethodType());
        }

        if ($checkout->getPaymentMethod()) {
            $data['ecommerce']['paymentMethod'] = $this->paymentMethodLabelFormatter
                ->formatPaymentMethodLabel($checkout->getPaymentMethod());
        }

        return $data;
    }

    /**
     * @param CheckoutLineItem $item
     * @param ProductPriceScopeCriteriaInterface $scope
     * @param string $currency
     * @return float
     */
    private function getItemPrice(
        CheckoutLineItem $item,
        ProductPriceScopeCriteriaInterface $scope,
        string $currency
    ): float {
        if ($item->isPriceFixed()) {
            return $item->getPrice() ? (float) $item->getPrice()->getValue() : 0.0;
        }

        $criteria = $this->prepareProductsPriceCriteria($item, $currency);
        if (!$criteria) {
            return 0.0;
        }

        $prices = $this->productPriceProvider->getMatchedPrices([$criteria], $scope);

        return isset($prices[$criteria->getIdentifier()])
            ? (float) $prices[$criteria->getIdentifier()]->getValue()
            : 0.0;
    }

    /**
     * @param CheckoutLineItem $item
     * @param string $currency
     * @return ProductPriceCriteria
     */
    private function prepareProductsPriceCriteria(CheckoutLineItem $item, string $currency): ?ProductPriceCriteria
    {
        if (!$item->getProduct() || !$item->getProductUnit() || !$item->getQuantity()) {
            return null;
        }

        return new ProductPriceCriteria(
            $item->getProduct(),
            $item->getProductUnit(),
            (float) $item->getQuantity(),
            $item->getCurrency() ?: $currency
        );
    }
}
