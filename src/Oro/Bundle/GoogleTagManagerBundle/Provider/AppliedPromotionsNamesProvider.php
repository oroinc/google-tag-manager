<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;

/**
 * Provides the list of names of the applied promotions for a specific entity.
 */
class AppliedPromotionsNamesProvider
{
    private ManagerRegistry $doctrine;
    private EntityCouponsProviderInterface $entityCouponsProvider;

    public function __construct(
        ManagerRegistry $doctrine,
        EntityCouponsProviderInterface $entityCouponsProvider
    ) {
        $this->doctrine = $doctrine;
        $this->entityCouponsProvider = $entityCouponsProvider;
    }

    /**
     * @param object $entity
     *
     * @return string[] [promotion id => promotion name, ...]
     */
    public function getAppliedPromotionsNames(object $entity): array
    {
        /** @var Coupon[] $coupons */
        $coupons = $this->entityCouponsProvider->getCoupons($entity)->toArray();
        if (!$coupons) {
            return [];
        }

        $promotionIds = [];
        foreach ($coupons as $coupon) {
            $promotion = $coupon->getPromotion();
            if (null !== $promotion && $promotion->getId()) {
                $promotionIds[] = $promotion->getId();
            }
        }

        $promotionsNames = $this->doctrine
            ->getRepository(Promotion::class)
            ->getPromotionsNamesByIds($promotionIds);
        $promotionsNames = array_values(array_unique(array_filter($promotionsNames)));

        sort($promotionsNames);

        return $promotionsNames;
    }
}
