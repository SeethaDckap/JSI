<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class SaveCustomer extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Does any custom saving of a customer after save action in admin
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $event = $observer->getEvent();
        /* @var $event Varien_Event */
        $customer = $event->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        $request = $event->getRequest();
        /* @var $request Mage_Core_Controller_Request_Http */

        $group = $request->getParam('in_group');

        if (!is_null($group)) {

            $data = $request->getPost();

            $groupIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['groups']));
            $this->commonAccessGroupCustomerFactory->create()->updateCustomerAccessGroups($customer->getId(), $groupIds);
        }
    }

}