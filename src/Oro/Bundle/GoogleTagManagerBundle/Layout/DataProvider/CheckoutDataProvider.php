<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Layout\DataProvider;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider;

/**
 * Layout data provider for checkout data.
 *
 * Component added back for theme layout BC from version 5.0
 */
class CheckoutDataProvider
{
    /**
     * @var CheckoutDetailProvider
     */
    private $checkoutDetailProvider;

    public function __construct(CheckoutDetailProvider $checkoutDetailProvider)
    {
        $this->checkoutDetailProvider = $checkoutDetailProvider;
    }

    public function getData(Checkout $checkout): array
    {
        return $this->checkoutDetailProvider->getData($checkout);
    }
}
