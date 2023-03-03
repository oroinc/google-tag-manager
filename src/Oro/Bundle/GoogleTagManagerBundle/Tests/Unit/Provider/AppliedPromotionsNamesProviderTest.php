<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Entity\Repository\PromotionRepository;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;
use Oro\Bundle\PromotionBundle\Tests\Unit\Entity\Stub\PromotionStub;
use Oro\Bundle\PromotionBundle\Tests\Unit\Stub\AppliedCouponsAwareStub;

class AppliedPromotionsNamesProviderTest extends \PHPUnit\Framework\TestCase
{
    private EntityCouponsProviderInterface|\PHPUnit\Framework\MockObject\MockObject $entityCouponsProvider;

    private PromotionRepository|\PHPUnit\Framework\MockObject\MockObject $promotionRepository;

    private AppliedPromotionsNamesProvider $provider;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityCouponsProvider = $this->createMock(EntityCouponsProviderInterface::class);

        $this->promotionRepository = $this->createMock(PromotionRepository::class);
        $this->paymentTransactionRepository = $this->createMock(PaymentTransactionRepository::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getRepository')
            ->with(Promotion::class)
            ->willReturn($this->promotionRepository);

        $this->provider = new AppliedPromotionsNamesProvider($managerRegistry, $this->entityCouponsProvider);
    }

    public function testGetAppliedPromotionsNamesWhenNoCoupons(): void
    {
        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection());

        self::assertSame([], $this->provider->getAppliedPromotionsNames($entity));
    }

    public function testGetAppliedPromotionsNamesWhenNoPromotion(): void
    {
        $coupons = new ArrayCollection();
        $coupons->add(new Coupon());

        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn($coupons);

        $this->promotionRepository
            ->expects(self::once())
            ->method('getPromotionsNamesByIds')
            ->with([])
            ->willReturn([]);

        self::assertSame([], $this->provider->getAppliedPromotionsNames($entity));
    }

    public function testGetAppliedPromotionsNamesWhenNewPromotion(): void
    {
        $promotion = new Promotion();
        $coupon = new Coupon();
        $coupon->setPromotion($promotion);

        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection([$coupon]));

        $this->promotionRepository
            ->expects(self::once())
            ->method('getPromotionsNamesByIds')
            ->with([])
            ->willReturn([]);

        self::assertSame([], $this->provider->getAppliedPromotionsNames($entity));
    }

    /**
     * @dataProvider getAppliedPromotionsNamesDataProvider
     */
    public function testGetAppliedPromotionsNames(array $promotionsNames, array $expected): void
    {
        $promotion1 = new PromotionStub(42);
        $coupon1 = new Coupon();
        $coupon1->setPromotion($promotion1);

        $promotion2 = new PromotionStub(4242);
        $coupon2 = new Coupon();
        $coupon2->setPromotion($promotion2);

        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection([$coupon1, $coupon2]));

        $this->promotionRepository
            ->expects(self::once())
            ->method('getPromotionsNamesByIds')
            ->with([$promotion1->getId(), $promotion2->getId()])
            ->willReturn($promotionsNames);

        self::assertSame($expected, $this->provider->getAppliedPromotionsNames($entity));
    }

    public function getAppliedPromotionsNamesDataProvider(): array
    {
        return [
            'promotions not exist' => [
                'promotionsNames' => [],
                'expected' => [],
            ],
            '1st promotion not exists' => [
                'promotionsNames' => [4242 => 'promo2'],
                'expected' => ['promo2'],
            ],
            '1st promotion name is empty' => [
                'promotionsNames' => [42 => '', 4242 => 'promo2'],
                'expected' => ['promo2'],
            ],
            '1st promotion name is same as 2nd' => [
                'promotionsNames' => [42 => 'promo2', 4242 => 'promo2'],
                'expected' => ['promo2'],
            ],
            'both promotions exist' => [
                'promotionsNames' => [42 => 'promo1', 4242 => 'promo2'],
                'expected' => ['promo1', 'promo2'],
            ],
            'both promotions exist and sorted' => [
                'promotionsNames' => [4242 => 'promo2', 42 => 'promo1'],
                'expected' => ['promo1', 'promo2'],
            ],
        ];
    }
}
