<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;

/**
 * Adds customer user data for data layer.
 */
class UserDetailCollector implements CollectorInterface
{
    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ArrayCollection $data): void
    {
        $user = $this->tokenAccessor->getUser();

        $data->add($user instanceof CustomerUser ? $this->getDataForCustomer($user) : $this->getDataForNotCustomer());
    }

    /**
     * Gets data for customer.
     *
     * @param CustomerUser $customerUser
     * @return array
     */
    protected function getDataForCustomer(CustomerUser $customerUser): array
    {
        $customer = $customerUser->getCustomer();
        $firstSalesRepresentativeName = $this->getFirstSalesRepresentativeName($customerUser);

        return [
            DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
            DataLayerAttributeBag::KEY_USER_GROUP => $this->getCustomerGroupName($customer),
//            DataLayerAttributeBag::KEY_CUSTOMER_ID => (string) $customer->getId(),
            DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => (string) $customerUser->getId(),
//            LocalizationID
        ];
    }

    /**
     * Gets data for not customer user.
     *
     * @return array
     */
    protected function getDataForNotCustomer(): array
    {
        return [
            DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_VISITOR
        ];
    }

    /**
     * @param Customer|null $customer
     * @return null|string
     */
    private function getCustomerGroupName(?Customer $customer): ?string
    {
        $group = $customer ? $customer->getGroup() : null;

        return $group ? $group->getName() : null;
    }

    /**
     * Gets first from Customer Assigned Sales Representatives.
     *
     * @param CustomerUser $customerUser
     * @return null|string
     */
    private function getFirstSalesRepresentativeName(CustomerUser $customerUser): ?string
    {
        $salesRepresentatives = $customerUser->getSalesRepresentatives();

        if ($salesRepresentatives->isEmpty()) {
            return null;
        }

        /** @var User $firstSalesRepresentative */
        $firstSalesRepresentative = $salesRepresentatives->first();

        return $firstSalesRepresentative->getFullName();
    }
}
