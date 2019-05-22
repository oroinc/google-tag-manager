<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutDetailProvider;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\PurchaseDetailProvider;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds checkout steps (Checkout events) and success information (Purchase event) for data layer.
 */
class CheckoutDetailCollector implements CollectorInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CheckoutDetailProvider
     */
    private $checkoutDetailProvider;

    /**
     * @var PurchaseDetailProvider
     */
    private $purchaseDetailProvider;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @param RequestStack $requestStack
     * @param CheckoutDetailProvider $checkoutDetailProvider
     * @param PurchaseDetailProvider $purchaseDetailProvider
     * @param string $routeName
     */
    public function __construct(
        RequestStack $requestStack,
        CheckoutDetailProvider $checkoutDetailProvider,
        PurchaseDetailProvider $purchaseDetailProvider,
        string $routeName
    ) {
        $this->requestStack = $requestStack;
        $this->checkoutDetailProvider = $checkoutDetailProvider;
        $this->purchaseDetailProvider = $purchaseDetailProvider;
        $this->routeName = $routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Collection $data): void
    {
        $checkout = $this->getCheckout();
        if (!$checkout || !$this->supports()) {
            return;
        }

        $result = $checkout->isCompleted()
            ? $this->purchaseDetailProvider->getData($checkout)
            : $this->checkoutDetailProvider->getData($checkout);

        $data->add($result);
    }

    /**
     * @return Checkout|null
     */
    private function getCheckout(): ?Checkout
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $checkout = $request->attributes->get('checkout');

        return $checkout instanceof Checkout ? $checkout : null;
    }

    /**
     * @return bool
     */
    private function supports(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        return $request->attributes->get('_route') === $this->routeName;
    }
}
