<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\RequestProductItemEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\RFPBundle\Entity\RequestProduct;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Oro\Component\Testing\Unit\EntityTrait;

class RequestProductItemEventListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DataLayerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $dataLayerManager;

    /** @var ProductDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productDetailProvider;

    /** @var ProductPriceDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productPriceDetailProvider;

    /** @var Transport|\PHPUnit\Framework\MockObject\MockObject */
    private $transport;

    /** @var GoogleTagManagerSettingsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var RequestProductItemEventListener */
    private $listener;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->transport = $this->createMock(Transport::class);
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new RequestProductItemEventListener(
            $this->frontendHelper,
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productPriceDetailProvider,
            $this->settingsProvider,
            1
        );
    }

    public function testPrePersistWithoutEnabledIntegration(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->productPriceDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->prePersist($this->getRequestProductItem(1001));
    }

    public function testPrePersistWithoutItem(): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->frontendHelper->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->productPriceDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->prePersist(null);
    }

    public function testPrePersistWithoutProduct(): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->frontendHelper->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->productPriceDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->prePersist(new RequestProductItem());
    }

    public function testPrePersistForBackend(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->frontendHelper->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->prePersist($this->getRequestProductItem(1001));
    }

    public function testPrePersist(): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->frontendHelper->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $item1 = $this->getRequestProductItem(1001);
        $item2 = $this->getRequestProductItem(2002, 'set');

        $this->productDetailProvider->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    [
                        $item1->getProduct(),
                        null,
                        ['id' => 'sku123', 'name' => 'Product 1', 'category' => 'Category 1', 'brand' => 'Brand 1']
                    ],
                    [
                        $item2->getProduct(),
                        null,
                        ['id' => 'sku456', 'name' => 'Product 2', 'category' => 'Category 2', 'brand' => 'Brand 2']
                    ],
                ]
            );

        $this->productPriceDetailProvider->expects($this->any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    [$item1->getProduct(), $item1->getProductUnit(), $item1->getQuantity(), Price::create(10.1, 'USD')],
                    [$item2->getProduct(), $item2->getProductUnit(), $item2->getQuantity(), Price::create(50.5, 'USD')],
                ]
            );

        $this->dataLayerManager->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [
                    [
                        'event' => 'addToCart',
                        'ecommerce' => [
                            'currencyCode' => 'USD',
                            'add' => [
                                'products' => [
                                    [
                                        'id' => 'sku123',
                                        'name' => 'Product 1',
                                        'category' => 'Category 1',
                                        'brand' => 'Brand 1',
                                        'variant' => 'item',
                                        'quantity' => 5.5,
                                        'price' => 10.1
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'event' => 'addToCart',
                        'ecommerce' => [
                            'currencyCode' => 'USD',
                            'add' => [
                                'products' => [
                                    [
                                        'id' => 'sku456',
                                        'name' => 'Product 2',
                                        'category' => 'Category 2',
                                        'brand' => 'Brand 2',
                                        'variant' => 'set',
                                        'quantity' => 5.5,
                                        'price' => 50.5
                                    ]
                                ]
                            ]
                        ]
                    ]
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
