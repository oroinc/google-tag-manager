<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;
use Oro\Bundle\PromotionBundle\Tests\Unit\Stub\AppliedCouponsAwareStub;
use Oro\Component\Testing\ReflectionUtil;

class AppliedPromotionsNamesProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var EntityCouponsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityCouponsProvider;

    /** @var AppliedPromotionsNamesProvider */
    private $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityCouponsProvider = $this->createMock(EntityCouponsProviderInterface::class);

        $this->provider = new AppliedPromotionsNamesProvider($this->doctrine, $this->entityCouponsProvider);
    }

    private function getCoupon(int $promotionId): Coupon
    {
        $promotion = new Promotion();
        ReflectionUtil::setId($promotion, $promotionId);

        $coupon = new Coupon();
        $coupon->setPromotion($promotion);

        return $coupon;
    }

    private function expectLoadPromotionNames(array $ids, array $rows): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(Promotion::class)
            ->willReturn($em);
        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);
        $em->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($qb);
        $qb->expects(self::once())
            ->method('select')
            ->with('rule.name')
            ->willReturnSelf();
        $qb->expects(self::once())
            ->method('from')
            ->with(Promotion::class, 'p')
            ->willReturnSelf();
        $qb->expects(self::once())
            ->method('innerJoin')
            ->with('p.rule', 'rule')
            ->willReturnSelf();
        $qb->expects(self::once())
            ->method('where')
            ->with('p.id IN (:ids)')
            ->willReturnSelf();
        $qb->expects(self::once())
            ->method('setParameter')
            ->with('ids', $ids)
            ->willReturnSelf();
        $qb->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects(self::once())
            ->method('getArrayResult')
            ->willReturn($rows);
    }

    private function expectLoadPromotionNamesNotCalled(): void
    {
        $this->doctrine->expects(self::never())
            ->method('getManagerForClass');
    }

    public function testGetAppliedPromotionsNamesWhenNoCoupons(): void
    {
        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection());

        $this->expectLoadPromotionNamesNotCalled();

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

        $this->expectLoadPromotionNamesNotCalled();

        self::assertSame([], $this->provider->getAppliedPromotionsNames($entity));
    }

    public function testGetAppliedPromotionsNamesWhenNewPromotion(): void
    {
        $coupon = new Coupon();
        $coupon->setPromotion(new Promotion());

        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection([$coupon]));

        $this->expectLoadPromotionNamesNotCalled();

        self::assertSame([], $this->provider->getAppliedPromotionsNames($entity));
    }

    /**
     * @dataProvider getAppliedPromotionsNamesDataProvider
     */
    public function testGetAppliedPromotionsNames(array $rows, array $expected): void
    {
        $coupon1 = $this->getCoupon(1);
        $coupon2 = $this->getCoupon(2);
        $coupon3 = $this->getCoupon(3);

        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection([$coupon1, $coupon2, $coupon3]));

        $this->expectLoadPromotionNames(
            [$coupon1->getPromotion()->getId(), $coupon2->getPromotion()->getId(), $coupon3->getPromotion()->getId()],
            $rows
        );

        self::assertSame($expected, $this->provider->getAppliedPromotionsNames($entity));
    }

    public function getAppliedPromotionsNamesDataProvider(): array
    {
        return [
            'promotions not exist' => [
                'rows' => [],
                'expected' => []
            ],
            '1st promotion not exists' => [
                'rows' => [['name' => 'promo2'], ['name' => 'promo3']],
                'expected' => ['promo2', 'promo3']
            ],
            '1st promotion name is empty' => [
                'rows' => [['name' => ''], ['name' => 'promo2'], ['name' => 'promo3']],
                'expected' => ['promo2', 'promo3']
            ],
            '1st promotion name is same as 3nd' => [
                'rows' => [['name' => 'promo3'], ['name' => 'promo2'], ['name' => 'promo3']],
                'expected' => ['promo2', 'promo3']
            ],
            'all promotions exist' => [
                'rows' => [['name' => 'promo1'], ['name' => 'promo2'], ['name' => 'promo3']],
                'expected' => ['promo1', 'promo2', 'promo3']
            ],
            'all promotions exist and sorted' => [
                'rows' => [['name' => 'promo2'], ['name' => 'promo1'], ['name' => 'promo3']],
                'expected' => ['promo1', 'promo2', 'promo3']
            ]
        ];
    }

    public function testGetAppliedPromotionsNamesWhenSeveralCouponsHaveSamePromotion(): void
    {
        $coupon1 = $this->getCoupon(1);
        $coupon2 = $this->getCoupon(2);
        $coupon3 = $this->getCoupon(1);

        $entity = $this->createMock(AppliedCouponsAwareStub::class);
        $this->entityCouponsProvider->expects(self::once())
            ->method('getCoupons')
            ->with($entity)
            ->willReturn(new ArrayCollection([$coupon1, $coupon2, $coupon3]));

        $this->expectLoadPromotionNames(
            [1, 2],
            [['name' => 'promo1'], ['name' => 'promo2']]
        );

        self::assertSame(['promo1', 'promo2'], $this->provider->getAppliedPromotionsNames($entity));
    }
}
