<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\RequestProductItemEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\RFPBundle\Entity\RequestProduct;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Oro\Component\Testing\Unit\EntityTrait;

class RequestProductItemEventListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private FrontendHelper $frontendHelper;

    /** @var DataLayerManager|\PHPUnit\Framework\MockObject\MockObject */
    private DataLayerManager $dataLayerManager;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private ProductDetailProvider $productDetailProvider;

    /** @var ProductPriceDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private ProductPriceDetailProvider $productPriceDetailProvider;

    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private RequestProductItemEventListener $listener;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

        $this->listener = new RequestProductItemEventListener(
            $this->frontendHelper,
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productPriceDetailProvider,
            $this->dataCollectionStateProvider,
            1
        );
    }

    public function testPrePersistWithoutEnabledIntegration(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->prePersist($this->getRequestProductItem(1001));
    }

    public function testPrePersistWithoutProduct(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->prePersist(new RequestProductItem());
    }

    public function testPrePersistWhenNotFrontend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->prePersist($this->getRequestProductItem(1001));
    }

    public function testPrePersist(): void
    {
        $this->dataCollectionStateProvider->expects(self::exactly(2))
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);

        $item1 = $this->getRequestProductItem(1001);
        $item2 = $this->getRequestProductItem(2002, 'set');

        $this->productDetailProvider->expects(self::exactly(2))
            ->method('getData')
            ->willReturnMap(
                [
                    [
                        $item1->getProduct(),
                        null,
                        [
                            'item_id' => 'sku123',
                            'item_name' => 'Product 1',
                            'item_category' => 'Category 1',
                            'item_brand' => 'Brand 1',
                        ],
                    ],
                    [
                        $item2->getProduct(),
                        null,
                        [
                            'item_id' => 'sku456',
                            'item_name' => 'Product 2',
                            'item_category' => 'Category 2',
                            'item_brand' => 'Brand 2',
                        ],
                    ],
                ]
            );

        $this->productPriceDetailProvider->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturnMap(
                [
                    [$item1->getProduct(), $item1->getProductUnit(), $item1->getQuantity(), Price::create(10.1, 'USD')],
                    [$item2->getProduct(), $item2->getProductUnit(), $item2->getQuantity(), Price::create(50.5, 'USD')],
                ]
            );

        $this->dataLayerManager->expects(self::exactly(2))
            ->method('append')
            ->withConsecutive(
                [
                    [
                        'event' => 'add_to_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Product 1',
                                    'item_category' => 'Category 1',
                                    'item_brand' => 'Brand 1',
                                    'item_variant' => 'item',
                                    'quantity' => 5.5,
                                    'price' => 10.1,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'event' => 'add_to_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku456',
                                    'item_name' => 'Product 2',
                                    'item_category' => 'Category 2',
                                    'item_brand' => 'Brand 2',
                                    'item_variant' => 'set',
                                    'quantity' => 5.5,
                                    'price' => 50.5,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->prePersist($item1);
        $this->listener->prePersist($item2);
        $this->listener->postFlush();
    }

    private function getRequestProductItem(int $id, string $unitCode = 'item'): RequestProductItem
    {
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => $id]);

        $unit = new ProductUnit();
        $unit->setCode($unitCode);

        $requestProduct = new RequestProduct();
        $requestProduct->setProduct($product);

        $item = new RequestProductItem();
        $item->setRequestProduct($requestProduct)
            ->setProductUnit($unit)
            ->setQuantity(5.5);

        return $item;
    }
}
