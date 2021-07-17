<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\DataLayer\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\CollectorInterface;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\Collector\UserDetailCollector;
use Oro\Bundle\GoogleTagManagerBundle\DataLayer\ConstantBag\DataLayerAttributeBag;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;

class UserDetailCollectorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tokenAccessor;

    /**
     * @var CollectorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collector;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->collector = new UserDetailCollector($this->tokenAccessor);
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandleForNotCustomer(?CustomerUser $user, array $expected): void
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $data = new ArrayCollection();

        $this->collector->handle($data);

        $this->assertEquals($expected, $data->toArray());
    }

    public function handleDataProvider(): array
    {
        $customerGroup = new CustomerGroup();
        $customerGroup->setName('Test Group');

        return [
            'no customer user' => [
                'user' => null,
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_VISITOR,
                    ]
                ]
            ],
            'customer user without id' => [
                'user' => new CustomerUser(),
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
                        DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => '',
                    ]
                ]
            ],
            'customer user with id, without customer' => [
                'user' => $this->getEntity(CustomerUser::class, ['id' => 1001]),
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
                        DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => '1001',
                    ]
                ]
            ],
            'customer user with id and customer, without customer id' => [
                'user' => $this->getEntity(CustomerUser::class, ['id' => 1001, 'customer' => new Customer()]),
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
                        DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => '1001',
                        DataLayerAttributeBag::KEY_CUSTOMER_ID => '',
                    ]
                ]
            ],
            'customer user with id, customer and customer group, without customer group name' => [
                'user' => $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 1001,
                        'customer' => $this->getEntity(Customer::class, ['id' => 2002])
                    ]
                ),
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
                        DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => '1001',
                        DataLayerAttributeBag::KEY_CUSTOMER_ID => '2002',
                    ]
                ]
            ],
            'all data' => [
                'user' => $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 1001,
                        'customer' => $this->getEntity(Customer::class, ['id' => 2002, 'group' => $customerGroup])
                    ]
                ),
                'expected' => [
                    [
                        DataLayerAttributeBag::KEY_USER_TYPE => DataLayerAttributeBag::VALUE_USER_TYPE_CUSTOMER_USER,
                        DataLayerAttributeBag::KEY_CUSTOMER_USER_ID => '1001',
                        DataLayerAttributeBag::KEY_CUSTOMER_ID => '2002',
                        DataLayerAttributeBag::KEY_CUSTOMER_GROUP => 'Test Group',
                    ]
                ]
            ]
        ];
    }
}
