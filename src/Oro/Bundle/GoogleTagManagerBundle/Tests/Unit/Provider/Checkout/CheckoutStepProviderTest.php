<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout\CheckoutStepProvider;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepManager;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

/**
 * Component added back for theme layout BC from version 5.0
 */
class CheckoutStepProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var WorkflowManager|\PHPUnit\Framework\MockObject\MockObject */
    private $workflowManager;

    /** @var CheckoutStepProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->workflowManager = $this->createMock(WorkflowManager::class);

        $this->provider = new CheckoutStepProvider($this->workflowManager, ['skip_this_step']);
    }

    public function testGetData(): void
    {
        $checkout = new Checkout();

        $workflowStep = new WorkflowStep();
        $workflowStep->setName('step3');

        $workflowItem = new WorkflowItem();
        $workflowItem->setCurrentStep($workflowStep);

        $this->workflowManager->expects($this->once())
            ->method('getFirstWorkflowItemByEntity')
            ->with($checkout)
            ->willReturn($workflowItem);

        $step0 = new Step();
        $step0->setName('skip_this_step');

        $step1 = new Step();
        $step1->setName('step1')
            ->setOrder(10);

        $step2 = new Step();
        $step2->setName('step2')
            ->setOrder(20);

        $step3 = new Step();
        $step3->setName('step3')
            ->setOrder(30);

        $stepManager = new StepManager([$step0, $step1, $step2, $step3]);

        $workflow = $this->createMock(Workflow::class);
        $workflow->expects($this->once())
            ->method('getStepManager')
            ->willReturn($stepManager);

        $this->workflowManager->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowItem)
            ->willReturn($workflow);

        $this->assertEquals([$workflowStep, 3], $this->provider->getData($checkout));
    }
}
