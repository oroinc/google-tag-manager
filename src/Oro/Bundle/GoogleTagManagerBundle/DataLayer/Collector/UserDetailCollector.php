<?php

namespace Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Adds customer user data for data layer.
 */
class UserDetailCollector implements CollectorInterface
{
    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Collection $data): void
    {
        $user = $this->tokenAccessor->getUser();

        $data->add($user instanceof CustomerUser ? $this->getDataForCustomer($user) : $this->getDataForVisitor());
    }

    private function getDataForCustomer(CustomerUser $customerUser): array
    {
        $data = [
            DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
            DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => (string) $customerUser->getId(),
        ];

        $customer = $customerUser->getCustomer();
        if ($customer) {
            $data[DataLayerAttributeBag::KEY_CUSTOMER_ID] = (string) $customer->getId();

            if ($customer->getGroup()) {
                $data[DataLayerAttributeBag::KEY_CUSTOMER_GROUP] = $customer->getGroup()->getName();
            }
        }

        return $data;
    }

    private function getDataForVisitor(): array
    {
        return [
            DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_VISITOR,
        ];
    }
}
