<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class UpdateBsvRequestProducts extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Clear Branch Pickup, If the user ends Masquerade 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $bsv = $observer->getEvent()->getMessage();
        /* @var $bsv \Epicor\Comm\Model\Message\Request\Bsv */
        $data = $bsv->getMessageArray();
        $selectedBranch = $this->_helper->getSelectedBranch();
        $branchpickupEnabled = $this->_helper->isBranchPickupAvailable();
        if ($branchpickupEnabled && !empty($selectedBranch)) {
            unset($data['messages']['request']['body']['delivery']['deliveryAddress']);
            $data['messages']['request']['body']['delivery']['deliveryAddress'] = $this->_helper->getOrderFor($selectedBranch, 1);
            $data['messages']['request']['body']['orderFor'] = $this->_helper->getOrderFor($selectedBranch);
            $data['messages']['request']['body']['orderBy'] = $this->_helper->getOrderBy();
            $data['messages']['request']['body']['storeCollect'] = $selectedBranch;
            $bsv->setMessageArray($data);
        }
    }

}