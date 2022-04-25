<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Customer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $backendJsHelper;

    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;


    public function __construct(
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Magento\Customer\Model\Customer $customer,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->backendJsHelper = $backendJsHelper;
        $this->commHelper = $commHelper;
        $this->customer = $customer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


    protected function processLists($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();
        $customer = $this->customer->load($customerId);
        
        /* @var $customer \Epicor\Comm\Model\Customer */
        /* @var $request Varien_Event */
        $request = $observer->getEvent()->getRequest();
        $saveLists = $request->getParam('in_custlist');
        $data = $request->getParam('in_customer_lists_grid');
        parse_str($data, $output);
        $listsdata = array_keys($output);
        
        //$customer->removeLists($customer->getLists());
        //$customer->addLists($listsdata);
        
        $customer->processLists($listsdata);
        return $this;
    }

    protected function processLocations($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        $customerId = $customer->getId();
        $customer = $this->customer->load($customerId);
        
        /* @var $customer \Epicor\Comm\Model\Customer */
        $request = $observer->getEvent()->getRequest();

        $source = $request->getParam('locations_source');

        if (!empty($source)) {
            if ($source == 'erp') {
                $links = $customer->getCustomerLocationLinks();
                if ($links) {
                    foreach ($links as $locationCode => $type) {
                        $customer->deleteLocationLink($locationCode);
                    }
                }
                $customer->setEccLocationLinkType('');
                $customer->save();
            } else {
                    $saveLocations = $request->getParam('in_locations_lists_grid');
                    parse_str($saveLocations, $output);
                    $locations = array_keys($output);                
                    $customer->updateLocations($locations);
                    $customer->save();
                
            }
        }

        return $this;
    }

}

