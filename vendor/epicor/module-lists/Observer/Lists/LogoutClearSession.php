<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Lists;

class LogoutClearSession extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Any actions required after logout
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->clear();
    }

}