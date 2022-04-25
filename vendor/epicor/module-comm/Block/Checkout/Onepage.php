<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout;


class Onepage extends \Magento\Checkout\Block\Onepage
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->eventManager = $context->getEventManager();
        parent::__construct(
            $context,
            $formKey,
            $configProvider,
            $layoutProcessors,
            $data
        );
    }


    /**
     * Get checkout steps codes
     *
     * @return array
     */
    protected function _getStepCodes()
    {
        $steps = parent::_getStepCodes();
        if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $index = array_search('shipping_method', $steps);
            array_splice($steps, $index + 1, 0, 'shipping_dates');
        }

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setSteps($steps);
        $this->eventManager->dispatch('epicor_comm_onepage_get_steps', array('block' => $this, 'steps' => $transportObject));
        $steps = $transportObject->getSteps();

        return $steps;
    }

    /**
     * Get active step
     *
     * @return string
     */
    public function getActiveStep()
    {
        $step = parent::getActiveStep();

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setStep($step);
        $this->eventManager->dispatch('epicor_comm_onepage_get_active_step', array('block' => $this, 'step' => $transportObject));
        $step = $transportObject->getStep();

        return $step;
    }

}
