<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\GoogleTagManagerBundle\Formatter\NumberFormatter;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

/**
 * Returns data for checkout steps (Checkout events) and success page (Purchase event).
 *
 * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
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

    private ?NumberFormatter $numberFormatter = null;

    /**
     * @var int
     */
    private $batchSize;

    public function __construct(
        ProductDetailProvider $productDataProvider,
        CheckoutStepProvider $checkoutStepProvider,
        ProductPriceProviderInterface $productPriceProvider,
        ProductPriceScopeCriteriaFactoryInterface $priceScopeCriteriaFactory,
        int $batchSize = 30
    ) {
        $this->productDetailProvider = $productDataProvider;
        $this->checkoutStepProvider = $checkoutStepProvider;
        $this->productPriceProvider = $productPriceProvider;
        $this->priceScopeCriteriaFactory = $priceScopeCriteriaFactory;
        $this->batchSize = $batchSize;
    }

    public function setNumberFormatter(?NumberFormatter $numberFormatter): void
    {
        $this->numberFormatter = $numberFormatter;
    }

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
                    ]
                ],
            ]
        ];

        return array_map(
            static function (array $chunk) use ($data) {
                $data['ecommerce']['checkout']['products'] = $chunk;

                return $data;
            },
            array_chunk($products, $this->batchSize)
        );
    }

    private function getItemPrice(
        CheckoutLineItem $item,
        ProductPriceScopeCriteriaInterface $scope,
        string $currency
    ): float {
        if ($item->isPriceFixed()) {
            return $item->getPrice() ? $this->formatValue((float) $item->getPrice()->getValue()) : 0.0;
        }

        $criteria = $this->prepareProductsPriceCriteria($item, $currency);
        if (!$criteria) {
            return 0.0;
        }

        $prices = $this->productPriceProvider->getMatchedPrices([$criteria], $scope);

        return isset($prices[$criteria->getIdentifier()])
            ? $this->formatValue((float) $prices[$criteria->getIdentifier()]->getValue())
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

    private function formatValue(float $value): float
    {
        if ($this->numberFormatter) {
            return $this->numberFormatter->formatPriceValue($value);
        }

        return $value;
    }
}
