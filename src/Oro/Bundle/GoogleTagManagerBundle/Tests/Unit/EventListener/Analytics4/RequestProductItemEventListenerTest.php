<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\EventListener\Analytics4;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Analytics4\ProductLineItemCartHandler;
use Oro\Bundle\GoogleTagManagerBundle\EventListener\Analytics4\RequestProductItemEventListener;
use Oro\Bundle\GoogleTagManagerBundle\Provider\DataCollectionStateProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\RFPBundle\Entity\RequestProduct;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\TestContainerBuilder;

class RequestProductItemEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DataCollectionStateProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dataCollectionStateProvider;

    /** @var ProductLineItemCartHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $productLineItemCartHandler;

    /** @var RequestProductItemEventListener */
    private $listener;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->dataCollectionStateProvider = $this->createMock(DataCollectionStateProviderInterface::class);
        $this->productLineItemCartHandler = $this->createMock(ProductLineItemCartHandler::class);

        $container = TestContainerBuilder::create()
            ->add(DataCollectionStateProviderInterface::class, $this->dataCollectionStateProvider)
            ->add(ProductLineItemCartHandler::class, $this->productLineItemCartHandler)
            ->getContainer($this);

        $this->listener = new RequestProductItemEventListener($this->frontendHelper, $container);
    }

    private function getRequestProductItem(int $id, string $unitCode = 'item'): RequestProductItem
    {
        $product = new Product();
        ReflectionUtil::setId($product, $id);

        $requestProduct = new RequestProduct();
        $requestProduct->setProduct($product);

        $unit = new ProductUnit();
        $unit->setCode($unitCode);

        $item = new RequestProductItem();
        $item->setRequestProduct($requestProduct);
        $item->setProductUnit($unit);
        $item->setQuantity(5.5);

        return $item;
    }

    public function testPrePersistWithoutEnabledIntegration(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->productLineItemCartHandler->expects(self::never())
            ->method('addToCart');

        $this->productLineItemCartHandler->expects(self::once())
            ->method('flush');

        $this->listener->prePersist($this->getRequestProductItem(1001));
        $this->listener->postFlush();
    }

    public function testPrePersistWithoutProduct(): void
    {
        $item = new RequestProductItem();

        $this->dataCollectionStateProvider->expects(self::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->productLineItemCartHandler->expects(self::once())
            ->method('addToCart')
            ->with($item);

        $this->productLineItemCartHandler->expects(self::once())
            ->method('flush');

        $this->listener->prePersist($item);
        $this->listener->postFlush();
    }

    public function testPrePersistWhenNotFrontend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->productLineItemCartHandler->expects(self::never())
            ->method('addToCart');

        $this->productLineItemCartHandler->expects(self::once())
            ->method('flush');

        $this->listener->prePersist($this->getRequestProductItem(1001));
        $this->listener->postFlush();
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

        $this->productLineItemCartHandler->expects(self::exactly(2))
            ->method('addToCart')
            ->withConsecutive([$item1], [$item2]);

        $this->productLineItemCartHandler->expects(self::once())
            ->method('flush');

        $this->listener->prePersist($item1);
        $this->listener->prePersist($item2);
        $this->listener->postFlush();
    }
}
