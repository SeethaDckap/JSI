<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Onepage;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Progress
 *
 * @author David.Wylie
 */
class Progress extends  \Magento\Checkout\Block\Onepage
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

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
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

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }
    //M1 > M2 Translation End

}
