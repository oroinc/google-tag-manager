<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\PromotionBundle\Entity\AppliedCouponsAwareInterface;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Provider\EntityCouponsProviderInterface;

/**
 * Provides the list of names of the applied promotions for the specified entity.
 */
class AppliedPromotionsNamesProvider
{
    private ManagerRegistry $managerRegistry;

    private EntityCouponsProviderInterface $entityCouponsProvider;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EntityCouponsProviderInterface $entityCouponsProvider
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->entityCouponsProvider = $entityCouponsProvider;
    }

    /**
     * @return array
     *  [
     *      42 => 'Sample Promotion Name',
     *      // ...
     *  ]
     */
    public function getAppliedPromotionsNames(AppliedCouponsAwareInterface $entity): array
    {
        /** @var Coupon[] $coupons */
        $coupons = $this->entityCouponsProvider->getCoupons($entity)->toArray();
        if (!$coupons) {
            return [];
        }

        $promotionIds = [];
        foreach ($coupons as $coupon) {
            $promotion = $coupon->getPromotion();
            if (!$promotion || !$promotion->getId()) {
                continue;
            }

            $promotionIds[] = $promotion->getId();
        }

        $promotionsNames = $this->managerRegistry
            ->getRepository(Promotion::class)
            ->getPromotionsNamesByIds($promotionIds);
        $promotionsNames = array_values(array_unique(array_filter($promotionsNames)));

        sort($promotionsNames);

        return $promotionsNames;
    }
}
