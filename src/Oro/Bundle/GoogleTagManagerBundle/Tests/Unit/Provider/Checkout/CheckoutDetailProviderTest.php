<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Entity\CheckoutLineItem;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutStepProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\PricingBundle\Model\ProductPriceCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteria;
use Oro\Bundle\PricingBundle\Model\ProductPriceScopeCriteriaFactoryInterface;
use Oro\Bundle\PricingBundle\Provider\ProductPriceProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Component added back for theme layout BC from version 5.0
 */
class CheckoutDetailProviderTest extends TestCase
{
    use EntityTrait;

    private ProductDetailProvider&MockObject $productDetailProvider;
    private CheckoutStepProvider&MockObject $checkoutStepProvider;
    private ProductPriceProviderInterface&MockObject $productPriceProvider;
    private ProductPriceScopeCriteriaFactoryInterface&MockObject $priceScopeCriteriaFactory;
    private CheckoutDetailProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->checkoutStepProvider = $this->createMock(CheckoutStepProvider::class);
        $this->productPriceProvider = $this->createMock(ProductPriceProviderInterface::class);
        $this->priceScopeCriteriaFactory = $this->createMock(ProductPriceScopeCriteriaFactoryInterface::class);

        $this->provider = new CheckoutDetailProvider(
            $this->productDetailProvider,
            $this->checkoutStepProvider,
            $this->productPriceProvider,
            $this->priceScopeCriteriaFactory,
            1
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCheckoutData(): void
    {
        [$checkout, $lineItem1, $lineItem2, $lineItem3] = $this->prepareCheckout();
        [$step, $stepPosition] = $this->prepareWorkflowStep();

        $this->checkoutStepProvider->expects($this->once())
            ->method('getData')
            ->with($checkout)
            ->willReturn([$step, $stepPosition]);

        $scopeCriteria = new ProductPriceScopeCriteria();

        $this->priceScopeCriteriaFactory->expects($this->once())
            ->method('createByContext')
            ->with($checkout)
            ->willReturn($scopeCriteria);

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

        $priceCriteria = new ProductPriceCriteria(
            $lineItem2->getProduct(),
            $lineItem2->getProductUnit(),
            $lineItem2->getQuantity(),
            $lineItem2->getCurrency()
        );

        $this->productPriceProvider->expects($this->once())
            ->method('getMatchedPrices')
            ->with([$priceCriteria], $scopeCriteria)
            ->willReturn(
                [
                    '2002-item-5.5-USD' => Price::create(10.10, 'USD'),
                    'test' => Price::create(11.11, 'USD')
                ]
            );

        $this->assertEquals(
            [
                [
                    'event' => 'checkout',
                    'ecommerce' => [
                        'checkout' => [
                            'actionField' => [
                                'step' => 3,
                                'option' => 'enter_shipping_method',
                            ],
                            'products' => [
                                [
                                    'id' => 'sku2',
                                    'name' => 'Product 2',
                                    'price' => 10.10,
                                    'brand' => 'Brand 2',
                                    'category' => 'Category 2',
                                    'quantity' => 5.5,
                                    'position' => 2,
                                    'variant' => 'item',
                                ],
                            ],
                        ],
                        'currencyCode' => 'USD',
                    ],
                ],
                [
                    'event' => 'checkout',
                    'ecommerce' => [
                        'checkout' => [
                            'actionField' => [
                                'step' => 3,
                                'option' => 'enter_shipping_method',
                            ],
                            'products' => [
                                [
                                    'id' => 'sku3',
                                    'name' => 'Product 3',
                                    'price' => 100.10,
                                    'brand' => 'Brand 3',
                                    'category' => 'Category 3',
                                    'quantity' => 15.15,
                                    'position' => 3,
                                    'variant' => 'set',
                                ],
                            ],
                        ],
                        'currencyCode' => 'USD',
                    ],
                ],
                [
                    'event' => 'checkout',
                    'ecommerce' => [
                        'checkout' => [
                            'actionField' => [
                                'step' => 3,
                                'option' => 'enter_shipping_method',
                            ],
                            'products' => [
                                [
                                    'id' => 'free-form-sku',
                                    'name' => 'Free Form Product',
                                    'price' => 4.2,
                                    'quantity' => 3.14,
                                    'position' => 4,
                                    'variant' => 'set',
                                ],
                            ],
                        ],
                        'currencyCode' => 'USD',
                    ],
                ]
            ],
            $this->provider->getData($checkout)
        );
    }

    private function prepareCheckout(): array
    {
        $product1 = $this->getEntity(Product::class, ['id' => 1001]);
        $product2 = $this->getEntity(Product::class, ['id' => 2002]);
        $product3 = $this->getEntity(Product::class, ['id' => 3003]);

        $lineItem1 = new CheckoutLineItem();
        $lineItem1->setProduct($product1)
            ->preSave();

        $productUnit2 = new ProductUnit();
        $productUnit2->setCode('item');

        $lineItem2 = new CheckoutLineItem();
        $lineItem2->setProduct($product2)
            ->setProductUnit($productUnit2)
            ->setQuantity(5.5)
            ->setPrice(Price::create(1.1, 'USD'))
            ->preSave();

        $productUnit3 = new ProductUnit();
        $productUnit3->setCode('set');

        $lineItem3 = new CheckoutLineItem();
        $lineItem3->setProduct($product3)
            ->setProductUnit($productUnit3)
            ->setQuantity(15.15)
            ->setPriceFixed(true)
            ->setPrice(Price::create(100.1, 'USD'))
            ->preSave();

        $lineItem4 = new CheckoutLineItem();
        $lineItem4->setProductSku('free-form-sku')
            ->setFreeFormProduct('Free Form Product')
            ->setProductUnit($productUnit3)
            ->setQuantity(3.14)
            ->setPriceFixed(true)
            ->setPrice(Price::create(4.2, 'USD'))
            ->preSave();

        $checkout = new Checkout();
        $checkout->setCurrency('USD')
            ->addLineItem($lineItem1)
            ->addLineItem($lineItem2)
            ->addLineItem($lineItem3)
            ->addLineItem($lineItem4);

        return [$checkout, $lineItem1, $lineItem2, $lineItem3, $lineItem4];
    }

    private function prepareWorkflowStep(): array
    {
        $definition = new WorkflowDefinition();
        $definition->setName('test_workflow');

        $step = new WorkflowStep();
        $step->setName('enter_shipping_method')
            ->setDefinition($definition);

        $stepPosition = 3;

        return [$step, $stepPosition];
    }
}
