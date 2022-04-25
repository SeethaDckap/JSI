<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Contracts;

class ContractSelect extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Checks active lists, and whether one can be selected
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setSkipLists(false);
        $this->eventManager->dispatch('epicor_lists_login_check_before', array('transport' => $transportObject));
        $skipLists = $transportObject->getSkipLists();

        if ($skipLists) {
            return;
        }

        $activeContracts = $helper->getActiveContracts();
        if (empty($activeContracts)) {
            return;
        }

        $helper->autoSelectContract();

        $this->eventManager->dispatch('epicor_lists_login_check_after', array('transport' => $transportObject));
    }

}