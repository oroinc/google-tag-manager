<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\ShoppingListLineItemEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\GoogleTagManagerSettingsProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Component\Testing\Unit\EntityTrait;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ShoppingListLineItemEventListenerTest extends \PHPUnit\Framework\TestCase
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

    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    /** @var ShoppingListLineItemEventListener */
    private $listener;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->frontendHelper->expects(self::any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataLayerManager = $this->createMock(DataLayerManager::class);

        $this->productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $this->productDetailProvider->expects(self::any())
            ->method('getData')
            ->with(self::isInstanceOf(Product::class))
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
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

        $this->listener = new ShoppingListLineItemEventListener(
            $this->frontendHelper,
            $this->dataLayerManager,
            $this->productDetailProvider,
            $this->productPriceDetailProvider,
            $this->settingsProvider,
            1
        );

        $this->listener->setDataCollectionStateProvider($this->dataCollectionStateProvider);
    }

    public function testPrePersistNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->prePersist($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPrePersist(): void
    {
        $this->dataCollectionStateProvider->expects(self::exactly(2))
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects(self::exactly(2))
            ->method('getPrice')
            ->with(
                $item->getProduct(),
                $item->getUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::once())
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
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->prePersist($item);
        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateNotApplicable(array $changeSet): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(false);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdate(array $changeSet, array $expected): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    [
                        $item->getProduct(),
                        $item->getProductUnit(),
                        $item->getQuantity(),
                        Price::create(100.1, 'USD'),
                    ],
                    [
                        $item->getProduct(),
                        $changeSet['unit'][0] ?? $setUnit,
                        $item->getQuantity(),
                        Price::create(200.2, 'USD'),
                    ],
                ]
            );

        foreach ($expected as $key => $expectedItem) {
            $this->dataLayerManager->expects(self::at($key))
                ->method('add')
                ->with($expectedItem);
        }

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
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
                                        'price' => 100.1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
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
                                        'price' => 100.1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
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
                                        'price' => 100.1,
                                    ],
                                ],
                            ],
                        ],
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
                                        'price' => 200.2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
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
                                        'price' => 100.1,
                                    ],
                                ],
                            ],
                        ],
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
                                        'price' => 200.2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'changeSet' => ['notes' => ['old note', 'new note']],
                'expected' => [],
            ],
        ];
    }

    public function testPreRemoveNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->preRemove($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPreRemove(): void
    {
        $this->dataCollectionStateProvider->expects(self::exactly(2))
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects(self::exactly(2))
            ->method('getPrice')
            ->with(
                $item->getProduct(),
                $item->getUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::once())
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
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->preRemove($item);
        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveForDifferentUnits(): void
    {
        $this->dataCollectionStateProvider->expects(self::exactly(2))
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $item = $this->getLineItem();
        $item1 = $this->getLineItem();
        $unit = new ProductUnit();
        $unit->setCode('box');
        $item1->setUnit($unit);

        $this->productPriceDetailProvider->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::at(0))
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
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );
        $this->dataLayerManager->expects(self::at(1))
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
                                    'variant' => 'box',
                                    'quantity' => 5.5,
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->preRemove($item);
        $this->listener->preRemove($item1);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityHasRemoved()
    {
        $this->dataCollectionStateProvider->expects(self::exactly(2))
            ->method('isEnabled')
            ->with('universal_analytics')
            ->willReturn(true);

        $shoppingListId = 2;

        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => $shoppingListId]);
        $event = new CheckoutSourceEntityRemoveEvent($shoppingList);
        $this->listener->addShoppingListIdToIgnore($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPrePersistNotApplicableWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->prePersist($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPrePersistWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects(self::any())
            ->method('getPrice')
            ->with(
                $item->getProduct(),
                $item->getUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::once())
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
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->prePersist($item);
        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateNotApplicableWhenNoDataCollectionStateProvider(array $changeSet): void
    {
        $this->settingsProvider->expects(self::any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateWhenNoDataCollectionStateProvider(array $changeSet, array $expected): void
    {
        $this->settingsProvider->expects(self::any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    [
                        $item->getProduct(),
                        $item->getProductUnit(),
                        $item->getQuantity(),
                        Price::create(100.1, 'USD'),
                    ],
                    [
                        $item->getProduct(),
                        $changeSet['unit'][0] ?? $setUnit,
                        $item->getQuantity(),
                        Price::create(200.2, 'USD'),
                    ],
                ]
            );

        foreach ($expected as $key => $expectedItem) {
            $this->dataLayerManager->expects(self::at($key))
                ->method('add')
                ->with($expectedItem);
        }

        /** @var EntityManagerInterface $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreRemoveNotApplicableWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::once())
            ->method('getGoogleTagManagerSettings')
            ->willReturn(null);

        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preRemove($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPreRemoveWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects(self::any())
            ->method('getPrice')
            ->with(
                $item->getProduct(),
                $item->getUnit(),
                $item->getQuantity()
            )
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::once())
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
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preRemove($item);
        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveForDifferentUnitsWhenNoDataCollectionStateProvider(): void
    {
        $this->settingsProvider->expects(self::any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $item = $this->getLineItem();
        $item1 = $this->getLineItem();
        $unit = new ProductUnit();
        $unit->setCode('box');
        $item1->setUnit($unit);

        $this->productPriceDetailProvider->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::at(0))
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
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );
        $this->dataLayerManager->expects(self::at(1))
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
                                    'variant' => 'box',
                                    'quantity' => 5.5,
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->preRemove($item);
        $this->listener->preRemove($item1);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityHasRemovedWhenNoDataCollectionStateProvider()
    {
        $this->settingsProvider->expects(self::any())
            ->method('getGoogleTagManagerSettings')
            ->willReturn($this->transport);

        $shoppingListId = 2;

        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => $shoppingListId]);
        $event = new CheckoutSourceEntityRemoveEvent($shoppingList);
        $this->listener->setDataCollectionStateProvider(null);
        $this->listener->addShoppingListIdToIgnore($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects(self::never())
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

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
