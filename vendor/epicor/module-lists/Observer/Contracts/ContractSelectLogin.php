<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Observer\Contracts;

class ContractSelectLogin extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    protected $listsFrontendContractHelper;
    protected $registry;
    protected $eventManager;
    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    public function __construct(
    \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\Event\Manager $eventManager,
    \Epicor\Lists\Helper\Session $listsSessionHelper) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->listsSessionHelper = $listsSessionHelper;
    }

    /**
     * Checks lists to see if any need selecting on login
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->registry->unregister('ecc_contract_allow_change_shipto');
        $this->registry->register('ecc_contract_allow_change_shipto', true);
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        if ($helper->contractsDisabled()) {
            return $this;
        }
        
        $this->eventManager->dispatch('epicor_contract_select');
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_contract_selection_started', true);
        return $this;
    }

}
