<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\ShoppingListLineItemEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ShoppingListLineItemEventListenerTest extends \PHPUnit\Framework\TestCase
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

    /** @var ShoppingListLineItemEventListener */
    private $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn(new UsernamePasswordToken(new CustomerUser(), '', 'test'));

        $this->dataLayerManager = $this->createMock(DataLayerManager::class);

        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
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

        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->transport = $this->createMock(Transport::class);
        $this->settingsProvider = $this->createMock(GoogleTagManagerSettingsProviderInterface::class);

        $this->listener = new ShoppingListLineItemEventListener(
            $this->tokenStorage,
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productPriceDetailProvider,
            $this->settingsProvider,
            1
        );
    }

    public function testPrePersistNotApplicable(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->productPriceDetailProvider->expects($this->never())
            ->method('getPrice');

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->prePersist($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPrePersist(): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects($this->any())
            ->method('getPrice')
            ->with(
                $item->getProduct(),
                $item->getUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects($this->exactly(2))
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
                                    'price' => 100.1
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->listener->prePersist($item);
        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     *
     * @param array $changeSet
     */
    public function testPreUpdateNotApplicable(array $changeSet): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $this->productPriceDetailProvider->expects($this->never())
            ->method('getPrice');

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     *
     * @param array $changeSet
     * @param array $expected
     */
    public function testPreUpdate(array $changeSet, array $expected): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects($this->any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    [
                        $item->getProduct(),
                        $item->getProductUnit(),
                        $item->getQuantity(),
                        Price::create(100.1, 'USD')
                    ],
                    [
                        $item->getProduct(),
                        $changeSet['unit'][0] ?? $setUnit,
                        $item->getQuantity(),
                        Price::create(200.2, 'USD')
                    ]
                ]
            );

        foreach ($expected as $key => $expectedItem) {
            $this->dataLayerManager->expects($this->at($key))
                ->method('add')
                ->with($expectedItem);
        }

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function preUpdateDataProvider(): array
    {
        $itemUnit = new ProductUnit();
        $itemUnit->setCode('item');

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        return [
            [
                'changeSet' => ['quantity' => [10, 30]],
                'expected' => [
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
                                        'quantity' => 20,
                                        'price' => 100.1
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'changeSet' => ['quantity' => [30, 10]],
                'expected' => [
                    [
                        'event' => 'removeFromCart',
                        'ecommerce' => [
                            'currencyCode' => 'USD',
                            'remove' => [
                                'products' => [
                                    [
                                        'id' => 'sku123',
                                        'name' => 'Test Product',
                                        'category' => 'Test Category',
                                        'brand' => 'test Brand',
                                        'variant' => 'item',
                                        'quantity' => 20,
                                        'price' => 100.1
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'changeSet' => ['unit' => [$itemUnit, $setUnit]],
                'expected' => [
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
                                        'variant' => 'set',
                                        'quantity' => 5.5,
                                        'price' => 100.1
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'event' => 'removeFromCart',
                        'ecommerce' => [
                            'currencyCode' => 'USD',
                            'remove' => [
                                'products' => [
                                    [
                                        'id' => 'sku123',
                                        'name' => 'Test Product',
                                        'category' => 'Test Category',
                                        'brand' => 'test Brand',
                                        'variant' => 'item',
                                        'quantity' => 5.5,
                                        'price' => 200.2
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            [
                'changeSet' => ['unit' => [$itemUnit, $setUnit], 'quantity' => [10, 30]],
                'expected' => [
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
                                        'variant' => 'set',
                                        'quantity' => 30,
                                        'price' => 100.1
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'event' => 'removeFromCart',
                        'ecommerce' => [
                            'currencyCode' => 'USD',
                            'remove' => [
                                'products' => [
                                    [
                                        'id' => 'sku123',
                                        'name' => 'Test Product',
                                        'category' => 'Test Category',
                                        'brand' => 'test Brand',
                                        'variant' => 'item',
                                        'quantity' => 10,
                                        'price' => 200.2
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    public function testPreRemoveNotApplicable(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->productPriceDetailProvider->expects($this->never())
            ->method('getPrice');

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->preRemove($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPreRemove(): void
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects($this->any())
            ->method('getPrice')
            ->with(
                $item->getProduct(),
                $item->getUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects($this->exactly(2))
            ->method('add')
            ->with(
                [
                    'event' => 'removeFromCart',
                    'ecommerce' => [
                        'currencyCode' => 'USD',
                        'remove' => [
                            'products' => [
                                [
                                    'id' => 'sku123',
                                    'name' => 'Test Product',
                                    'category' => 'Test Category',
                                    'brand' => 'test Brand',
                                    'variant' => 'item',
                                    'quantity' => 5.5,
                                    'price' => 100.1
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->listener->preRemove($item);
        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityHasRemoved()
    {
        $this->settingsProvider->expects($this->any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $shoppingListId = 2;

        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => $shoppingListId]);
        $event = new CheckoutSourceEntityRemoveEvent($shoppingList);
        $this->listener->addShoppingListIdToIgnore($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects($this->never())
            ->method('getPrice');

        $this->dataLayerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    /**
     * @param ProductUnit|null $unit
     * @param int $shoppingListId
     * @return LineItem
     */
    private function getLineItem(?ProductUnit $unit = null, int $shoppingListId = 1): LineItem
    {
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 42]);

        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => $shoppingListId]);

        if (!$unit) {
            $unit = new ProductUnit();
            $unit->setCode('item');
        }

        $qty = 5.5;

        $item = new LineItem();
        $item->setProduct($product)
            ->setShoppingList($shoppingList)
            ->setUnit($unit)
            ->setQuantity($qty);

        return $item;
    }
}
