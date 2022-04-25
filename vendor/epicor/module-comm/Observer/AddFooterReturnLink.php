<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AddFooterReturnLink extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->commReturnsHelper->isReturnsEnabled()) {
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle('add_returns_footer_link');
        }
    }

}