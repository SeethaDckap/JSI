<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Contracts;

class LogoutClearSession extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Any actions required after logout
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper \Epicor\Lists\Helper\Session */
        $sessionHelper->clear();
    }

}