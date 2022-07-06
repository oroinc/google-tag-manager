<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityClearEvent;
use Oro\Bundle\CheckoutBundle\Event\CheckoutSourceEntityRemoveEvent;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
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

class ShoppingListLineItemEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataLayerManager|\PHPUnit\Framework\MockObject\MockObject */
    private DataLayerManager $dataLayerManager;

    /** @var ProductPriceDetailProvider|\PHPUnit\Framework\MockObject\MockObject */
    private ProductPriceDetailProvider $productPriceDetailProvider;

    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataCollectionStateProviderInterface $dataCollectionStateProvider;

    private ShoppingListLineItemEventListener $listener;

    protected function setUp(): void
    {
        $this->dataLayerManager = $this->createMock(DataLayerManager::class);
        $this->productPriceDetailProvider = $this->createMock(ProductPriceDetailProvider::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);

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
    }

    public function testPrePersistNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
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
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->listener->preUpdate($item, new PreUpdateEventArgs($item, $objectManager, $changeSet));
        $this->listener->postFlush();
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdate(array $changeSet, array $expected): void
    {
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

    public function testPreRemoveNotApplicable(): void
    {
        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
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

    public function testPreRemoveForDifferentUnits(): void
    {
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
            ->method('getPrice');

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
            ->method('getPrice');

        $this->dataLayerManager->expects(self::never())
            ->method(self::anything());

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
