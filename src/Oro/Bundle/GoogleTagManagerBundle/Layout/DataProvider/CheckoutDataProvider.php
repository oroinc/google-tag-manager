<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider;

/**
 * Layout data provider for checkout data.
 */
class CheckoutDataProvider
{
    /**
     * @var CheckoutDetailProvider
     */
    private $checkoutDetailProvider;

    /**
     * @param CheckoutDetailProvider $checkoutDetailProvider
     */
    public function __construct(CheckoutDetailProvider $checkoutDetailProvider)
    {
        $this->checkoutDetailProvider = $checkoutDetailProvider;
    }

    /**
     * @param Checkout $checkout
     * @return array
     */
    public function getData(Checkout $checkout): array
    {
        return $this->checkoutDetailProvider->getData($checkout);
    }
}
