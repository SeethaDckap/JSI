<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locationgroups;

class Save extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Epicor\Comm\Model\Location\GroupsFactory
     */
    protected $commLocationGroupFactory;

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
        \Epicor\Comm\Model\Location\GroupsFactory $commLocationGroupFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commLocationGroupFactory = $commLocationGroupFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->backendJsHelper = $backendJsHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $group = $this->commLocationGroupFactory->create()->load($this->getRequest()->getParam('id'));
            /* @var $group \Epicor\Comm\Model\Location\Groups */

            $data=(array)$data;
            $group->addData($data);
            $group->save();
            // Handle Locations Tab
            $saveGrouplocations = $this->getRequest()->getParam('selected_grouplocations');
            if (!is_null($saveGrouplocations)) {
                $grouplocationsResult = $this->backendJsHelper->decodeGridSerializedInput($data['links']['grouplocations']);
                $grouplocations = array_keys($grouplocationsResult);
                $helper = $this->commLocationsHelper;
                /* @var $helper \Epicor\Comm\Helper\Locations */
                
                $helper->syncGroupLocations($group->getId(), $grouplocations, $grouplocationsResult);
            }            
            
            $this->messageManager->addSuccessMessage(__('Group %1 Saved Successfully', $group->getGroupName()));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $group->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    }
