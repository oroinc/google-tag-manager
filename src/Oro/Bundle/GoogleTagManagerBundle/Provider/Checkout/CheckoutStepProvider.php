<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Provider\Checkout;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

/**
 * Returns information about current checkout step.
 *
 * @deprecated Will be removed in oro/google-tag-manager-bundle:5.1.0.
 */
class CheckoutStepProvider
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var array
     */
    private $excludedSteps;

    public function __construct(WorkflowManager $workflowManager, array $excludedSteps = [])
    {
        $this->workflowManager = $workflowManager;
        $this->excludedSteps = $excludedSteps;
    }

    /**
     * @param Checkout $checkout
     * @return array [$step, $position]
     */
    public function getData(Checkout $checkout): array
    {
        $workflowItem = $this->workflowManager->getFirstWorkflowItemByEntity($checkout);
        $currentStep = $workflowItem->getCurrentStep();

        $position = array_search($currentStep->getName(), $this->getSteps($workflowItem), true);

        return [$currentStep, $position === false ? 0 : $position + 1];
    }

    private function getSteps(WorkflowItem $workflowItem): array
    {
        $workflow = $this->workflowManager->getWorkflow($workflowItem);

        $steps = $workflow->getStepManager()
            ->getOrderedSteps(true, true);

        return array_values(array_diff($steps->toArray(), $this->excludedSteps));
    }
}
