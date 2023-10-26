<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityClearEvent;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\DataLayerManager;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\ShoppingListLineItemEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Analytics4\ProductDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\GoogleTagManagerBundle\Provider\ProductPriceDetailProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ShoppingListLineItemEventListenerTest extends TestCase
{
    private DataLayerManager|MockObject $dataLayerManager;

    private ProductPriceDetailProvider|MockObject $productPriceDetailProvider;

    private DataCollectionStateProviderInterface|MockObject $dataCollectionStateProvider;

    private ProductLineItemCartHandler|MockObject $productLineItemCartHandler;

    private ShoppingListLineItemEventListener $listener;

    protected function setUp(): void
    {
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->productLineItemCartHandler = $this->createMock(ProductLineItemCartHandler::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects(self::any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $productDetailProvider = $this->createMock(ProductDetailProvider::class);
        $productDetailProvider->expects(self::any())
            ->method('getData')
            ->with(self::isInstanceOf(Product::class))
            ->willReturn(
                [
                    'item_id' => 'sku123',
                    'item_name' => 'Test Product',
                    'item_category' => 'Test Category',
                    'item_brand' => 'test Brand',
                ]
            );

        $this->listener = new ShoppingListLineItemEventListener(
            $frontendHelper,
            $this->dataLayerManager,
            $productDetailProvider,
            $this->productPriceDetailProvider,
            $this->dataCollectionStateProvider,
            1
        );

        $this->listener->setProductLineItemCartHandler($this->productLineItemCartHandler);
    }

    public function testPrePersistNotApplicableWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->prePersist($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPrePersistNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::never())
            ->method('addToCart');

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $this->listener->prePersist($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPrePersistWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

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
            ->method('append')
            ->with(
                [
                    'event' => 'add_to_cart',
                    'ecommerce' => [
                        'currency' => 'USD',
                        'items' => [
                            [
                                'item_id' => 'sku123',
                                'item_name' => 'Test Product',
                                'item_category' => 'Test Category',
                                'item_brand' => 'test Brand',
                                'item_variant' => 'item',
                                'quantity' => 5.5,
                                'price' => 100.1,
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->prePersist($item);
        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    public function testPrePersist(): void
    {
        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('addToCart')
            ->with($item, $item->getUnit(), $item->getQuantity());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateNotApplicableWhenNoProductLineItemCartHandler(array $changeSet): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(false);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateNotApplicable(array $changeSet): void
    {
        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(false);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::never())
            ->method('addToCart');

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateWhenNoProductLineItemCartHandler(array $changeSet, array $expected): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::any())
            ->method('getPrice')
            ->willReturnMap([
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
            ]);

        if ($expected) {
            $this->dataLayerManager->expects(self::exactly(count($expected)))
                ->method('append')
                ->withConsecutive(
                    ...array_map(
                        static function ($item) {
                            return [$item];
                        },
                        $expected
                    )
                );
        } else {
            $this->dataLayerManager->expects(self::never())
                ->method('append');
        }

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
                        'event' => 'add_to_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'item',
                                    'quantity' => 20,
                                    'price' => 100.1,
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
                        'event' => 'remove_from_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'item',
                                    'quantity' => 20,
                                    'price' => 100.1,
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
                        'event' => 'add_to_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'set',
                                    'quantity' => 5.5,
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                    [
                        'event' => 'remove_from_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'item',
                                    'quantity' => 5.5,
                                    'price' => 200.2,
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
                        'event' => 'add_to_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'set',
                                    'quantity' => 30,
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                    [
                        'event' => 'remove_from_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'item',
                                    'quantity' => 10,
                                    'price' => 200.2,
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

    public function testPreUpdateWhenQuantityIncreased(): void
    {
        $changeSet = ['quantity' => [10, 30]];

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('addToCart')
            ->with($item, $item->getUnit(), 20);

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWhenQuantityDecreased(): void
    {
        $changeSet = ['quantity' => [30, 10]];

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('removeFromCart')
            ->with($item, $item->getUnit(), 20);

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWhenUnitChanged(): void
    {
        $itemUnit = (new ProductUnit())->setCode('item');
        $setUnit = (new ProductUnit())->setCode('set');

        $changeSet = ['unit' => [$itemUnit, $setUnit]];

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('removeFromCart')
            ->with($item, $itemUnit, $item->getQuantity());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('addToCart')
            ->with($item, $setUnit, $item->getQuantity());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWhenUnitAndQuantityChanged(): void
    {
        $itemUnit = (new ProductUnit())->setCode('item');
        $setUnit = (new ProductUnit())->setCode('set');

        $changeSet = ['unit' => [$itemUnit, $setUnit], 'quantity' => [10, 30]];

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem($changeSet['unit'][1] ?? null);

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('removeFromCart')
            ->with($item, $itemUnit, 10);

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('addToCart')
            ->with($item, $setUnit, 30);

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreUpdateWhenUnitAndQuantityNotChanged(): void
    {
        $changeSet = [];

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem();

        $setUnit = new ProductUnit();
        $setUnit->setCode('set');

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::never())
            ->method('removeFromCart');

        $this->productLineItemCartHandler
            ->expects(self::never())
            ->method('addToCart');

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    public function testPreRemoveNotApplicableWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->preRemove($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPreRemoveNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::never())
            ->method('addToCart');

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $this->listener->preRemove($this->getLineItem());
        $this->listener->postFlush();
    }

    public function testPreRemoveWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

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
            ->method('append')
            ->with(
                [
                    'event' => 'remove_from_cart',
                    'ecommerce' => [
                        'currency' => 'USD',
                        'items' => [
                            [
                                'item_id' => 'sku123',
                                'item_name' => 'Test Product',
                                'item_category' => 'Test Category',
                                'item_brand' => 'test Brand',
                                'item_variant' => 'item',
                                'quantity' => 5.5,
                                'price' => 100.1,
                            ],
                        ],
                    ],
                ]
            );

        $this->listener->preRemove($item);
        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemove(): void
    {
        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem();

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('removeFromCart')
            ->with($item, $item->getUnit(), $item->getQuantity());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveForDifferentUnitsWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem();
        $item1 = $this->getLineItem();
        $unit = new ProductUnit();
        $unit->setCode('box');
        $item1->setUnit($unit);

        $this->productPriceDetailProvider->expects(self::exactly(2))
            ->method('getPrice')
            ->willReturn(Price::create(100.1, 'USD'));

        $this->dataLayerManager->expects(self::exactly(2))
            ->method('append')
            ->withConsecutive(
                [
                    [
                        'event' => 'remove_from_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'item',
                                    'quantity' => 5.5,
                                    'price' => 100.1,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'event' => 'remove_from_cart',
                        'ecommerce' => [
                            'currency' => 'USD',
                            'items' => [
                                [
                                    'item_id' => 'sku123',
                                    'item_name' => 'Test Product',
                                    'item_category' => 'Test Category',
                                    'item_brand' => 'test Brand',
                                    'item_variant' => 'box',
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

    public function testPreRemoveForDifferentUnits(): void
    {
        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $item = $this->getLineItem();
        $item1 = $this->getLineItem();
        $item1->setUnit((new ProductUnit())->setCode('box'));

        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::exactly(2))
            ->method('removeFromCart')
            ->withConsecutive(
                [$item, $item->getUnit(), $item->getQuantity()],
                [$item1, $item1->getUnit(), $item1->getQuantity()],
            );

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('flush');

        $this->listener->preRemove($item);
        $this->listener->preRemove($item1);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityIsRemovedWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $shoppingListId = 2;

        $shoppingList = $this->getShoppingList($shoppingListId);
        $event = new CheckoutSourceEntityRemoveEvent($shoppingList);
        $this->listener->onCheckoutSourceEntityClearOrRemove($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityIsRemoved(): void
    {
        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $shoppingListId = 2;

        $shoppingList = $this->getShoppingList($shoppingListId);
        $event = new CheckoutSourceEntityRemoveEvent($shoppingList);
        $this->listener->onCheckoutSourceEntityClearOrRemove($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('removeFromCart')
            ->with($item);

        $this->productLineItemCartHandler
            ->expects(self::exactly(2))
            ->method('flush');

        $this->listener->preRemove($item);
        $this->listener->postFlush();

        // Checks that event "remove_from_cart" is triggered after listener is reset after postFlush.
        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityIsClearedWhenNoProductLineItemCartHandler(): void
    {
        $this->listener->setProductLineItemCartHandler(null);

        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $shoppingListId = 2;

        $shoppingList = $this->getShoppingList($shoppingListId);
        $event = new CheckoutSourceEntityClearEvent($shoppingList);
        $this->listener->onCheckoutSourceEntityClearOrRemove($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    public function testPreRemoveAfterCheckoutSourceEntityIsCleared(): void
    {
        $this->dataCollectionStateProvider->expects(self::any())
            ->method('isEnabled')
            ->willReturn(true);

        $shoppingListId = 2;

        $shoppingList = $this->getShoppingList($shoppingListId);
        $event = new CheckoutSourceEntityClearEvent($shoppingList);
        $this->listener->onCheckoutSourceEntityClearOrRemove($event);

        $item = $this->getLineItem(null, $shoppingListId);
        $this->productPriceDetailProvider->expects(self::never())
            ->method(self::anything());

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $this->productLineItemCartHandler
            ->expects(self::once())
            ->method('removeFromCart')
            ->with($item);

        $this->productLineItemCartHandler
            ->expects(self::exactly(2))
            ->method('flush');

        $this->listener->preRemove($item);
        $this->listener->postFlush();

        // Checks that event "remove_from_cart" is triggered after listener is reset after postFlush.
        $this->listener->preRemove($item);
        $this->listener->postFlush();
    }

    private function getProduct(int $id): Product
    {
        $product = new Product();
        ReflectionUtil::setId($product, $id);

        return $product;
    }

    private function getShoppingList(int $id): ShoppingList
    {
        $shoppingList = new ShoppingList();
        ReflectionUtil::setId($shoppingList, $id);

        return $shoppingList;
    }

    private function getLineItem(?ProductUnit $unit = null, int $shoppingListId = 1): LineItem
    {
        $product = $this->getProduct(42);

        $shoppingList = $this->getShoppingList($shoppingListId);

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
