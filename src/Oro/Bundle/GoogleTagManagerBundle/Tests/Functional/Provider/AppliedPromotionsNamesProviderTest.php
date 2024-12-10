<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Functional\Provider;

use Oro\Bundle\GoogleTagManagerBundle\Provider\AppliedPromotionsNamesProvider;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Model\AppliedPromotionData;
use Oro\Bundle\PromotionBundle\Tests\Functional\DataFixtures\LoadCouponData;
use Oro\Bundle\PromotionBundle\Tests\Functional\DataFixtures\LoadPromotionData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AppliedPromotionsNamesProviderTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCouponData::class]);
    }

    private function getCoupon(string $reference): Coupon
    {
        return $this->getReference($reference);
    }

    private function getPromotion(string $reference): Promotion
    {
        return $this->getReference($reference);
    }

    public function testGetAppliedPromotionsNames(): void
    {
        $entity = new AppliedPromotionData();
        $entity->addCoupon($this->getCoupon(LoadCouponData::COUPON_WITH_PROMO_AND_WITHOUT_VALID_UNTIL));
        $entity->addCoupon($this->getCoupon(LoadCouponData::COUPON_WITH_SHIPPING_PROMO_AND_VALID_UNTIL));

        $promotion1 = $this->getPromotion(LoadPromotionData::ORDER_PERCENT_PROMOTION);
        $promotion2 = $this->getPromotion(LoadPromotionData::SHIPPING_PROMOTION);

        /** @var AppliedPromotionsNamesProvider $provider */
        $provider = self::getContainer()->get('oro_google_tag_manager.provider.applied_promotions_names');
        $appliedPromotionsNames = $provider->getAppliedPromotionsNames($entity);

        self::assertEquals(
            [$promotion1->getRule()->getName(), $promotion2->getRule()->getName()],
            $appliedPromotionsNames
        );
    }
}
