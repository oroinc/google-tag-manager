<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RequestProductItemEventListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

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

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->transport = $this->createMock(Transport::class);
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new RequestProductItemEventListener(
            $this->tokenStorage,
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productPriceDetailProvider,
            $this->settingsProvider
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

        $this->listener->prePersist($this->getRequestProductItem());
    }

    public function testPrePersistWithoutCustomerUser(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->productPriceDetailProvider->expects($this->never())
            ->method($this->anything());

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->prePersist($this->getRequestProductItem());
    }

    public function testPrePersist(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new UsernamePasswordToken(new CustomerUser(), '', 'test'));

        $item = $this->getRequestProductItem();

        $this->productDetailProvider->expects($this->any())
            ->method('getData')
            ->with($this->isInstanceOf(Product::class))
            ->willReturn(
                [
                    'id' => 'sku123',
                    'name' => 'Test Product',
                    'category' => 'Test Category',
                    'brand' => 'test Brand',
                ]
            );

        $this->productPriceDetailProvider->expects($this->once())
            ->method('getPrice')
            ->with(
                $item->getRequestProduct()->getProduct(),
                $item->getProductUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.5, 'USD'));

        $this->dataLayerManager->expects($this->once())
            ->method('add')
            ->with(
                [
                    'event' => 'addToCart',
                    'ecommerce' => [
                        'currencyCode' => 'USD',
                        'add' => [
                            'products' => [
                                [
                                    'id' => 'sku123',
                                    'name' => 'Test Product',
                                    'category' => 'Test Category',
                                    'brand' => 'test Brand',
                                    'variant' => 'item',
                                    'quantity' => 5.5,
                                    'price' => 100.5
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    /**
     * @return RequestProductItem
     */
    private function getRequestProductItem(): RequestProductItem
    {
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 42]);

        $unit = new ProductUnit();
        $unit->setCode('item');

        $requestProduct = new RequestProduct();
        $requestProduct->setProduct($product);

        $item = new RequestProductItem();
        $item->setRequestProduct($requestProduct)
            ->setProductUnit($unit)
            ->setQuantity(5.5);

        return $item;
    }
}
