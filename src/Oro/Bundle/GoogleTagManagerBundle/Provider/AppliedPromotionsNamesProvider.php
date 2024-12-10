<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;

/**
 * Provides the list of names of the applied promotions for a specific entity.
 */
class AppliedPromotionsNamesProvider
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly EntityCouponsProviderInterface $entityCouponsProvider
    ) {
    }

    /**
     * @return string[]
     */
    public function getAppliedPromotionsNames(object $entity): array
    {
        $promotionIds = [];
        $coupons = $this->entityCouponsProvider->getCoupons($entity);
        foreach ($coupons as $coupon) {
            $promotion = $coupon->getPromotion();
            if (null !== $promotion && $promotion->getId()) {
                $promotionIds[] = $promotion->getId();
            }
        }
        $promotionIds = array_unique($promotionIds);

        $promotionsNames = [];
        if ($promotionIds) {
            /** @var EntityManagerInterface $em */
            $em = $this->doctrine->getManagerForClass(Promotion::class);
            $rows = $em->createQueryBuilder()
                ->select('rule.name')
                ->from(Promotion::class, 'p')
                ->innerJoin('p.rule', 'rule')
                ->where('p.id IN (:ids)')
                ->setParameter('ids', $promotionIds)
                ->getQuery()
                ->getArrayResult();
            foreach ($rows as $row) {
                $name = $row['name'];
                if ($name) {
                    $promotionsNames[] = $name;
                }
            }
            $promotionsNames = array_unique($promotionsNames);
            sort($promotionsNames);
        }

        return $promotionsNames;
    }
}
