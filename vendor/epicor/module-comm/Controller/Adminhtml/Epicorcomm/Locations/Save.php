<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations;

class Save extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations
{

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->commLocationFactory = $commLocationFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->backendJsHelper = $backendJsHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $location = $this->commLocationFactory->create()->load($this->getRequest()->getParam('id'));
            /* @var $location \Epicor\Comm\Model\Location */
            if (isset($data['county_id'])) {
                $region = $this->directoryRegionFactory->create()->load($data['county_id']);
                /* @var $region \Magento\Directory\Model\Region */
                $data['county_code'] = $region->getCode();
            }
            $data=(array)$data;
            $location->addData($data);

            if (!$location->getSource()) {
                $location->setSource('web');
                $location->setDummy(0);
            }


            // Handle Stores Tab
            $saveStores = $this->getRequest()->getParam('selected_store');
            if (!is_null($saveStores)) {
                $stores = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['stores']));
                $location->setFullStores($stores);
            }


            // Handle ErpAccounts Tab
            $saveErpAccounts = $this->getRequest()->getParam('selected_erpaccount');

            if (!is_null($saveErpAccounts)) {
                $erpAccounts = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
                /* @var $helper Epicor_Comm_Helper_Locations */
                $helper = $this->commLocationsHelper;

                $helper->syncErpAccountsToLocation($location->getCode(), $erpAccounts);
            }


            // Handle Customers Tab
            $saveCustomers = $this->getRequest()->getParam('selected_customer');

            if (!is_null($saveCustomers)) {
                $customers = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers']));
                /* @var $helper Epicor_Comm_Helper_Locations */
                $helper = $this->commLocationsHelper;

                $helper->syncCustomersToLocation($location->getCode(), $customers);
            }


            // Handle Products Tab
            $saveProducts = $this->getRequest()->getParam('selected_product');

            if (!is_null($saveProducts)) {
                $products = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['products']));
                /* @var $helper Epicor_Comm_Helper_Locations */
                $helper = $this->commLocationsHelper;

                $helper->syncProductsToLocation($location->getCode(), $products);
            }

            // Handle Related Locations Tab
            $saveRelatedlocations = $this->getRequest()->getParam('selected_relatedlocations');
            if (!is_null($saveRelatedlocations)) {
                $relatedlocations = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['relatedlocations']));
                $helper = $this->commLocationsHelper;
                /* @var $helper \Epicor\Comm\Helper\Locations */
                
                $helper->syncRelatedLocations($location->getId(), $relatedlocations);
            }
            
            // Handle Groups Tab
            $saveGroups = $this->getRequest()->getParam('selected_groups');
            if (!is_null($saveGroups)) {
                $groups = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['groups']));
                $helper = $this->commLocationsHelper;
                /* @var $helper \Epicor\Comm\Helper\Locations */
                
                $helper->syncGroups($location->getId(), $groups);
            }            
            
            $location->save();
            /* @var $helper Epicor_Comm_Helper_Data */
            //M1 > M2 Translation Begin (Rule 55)
            //$session->addSuccess($this->__('Location %s Saved Successfully', $location->getErpCode()));
            $this->messageManager->addSuccessMessage(__('Location %1 Saved Successfully', $location->getErpCode()));
            //M1 > M2 Translation End

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $location->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    }
