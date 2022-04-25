<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

class CustomerChanged extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Common\Model\Access\ElementFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //get customer
        $group = $observer->getEvent()->getGroup();
        $this->b2bHelper->setPreregPassword($group);
        $group->save();
    }

}