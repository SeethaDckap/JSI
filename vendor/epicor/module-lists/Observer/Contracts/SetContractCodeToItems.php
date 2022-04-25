<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Contracts;

class SetContractCodeToItems extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Adding contract code to the quoteitem table whether the user selects/changes the contract
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->setContractCodeToItems();
    }

}