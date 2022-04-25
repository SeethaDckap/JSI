<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Changeshippingaddressajax extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;
    
    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;             

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper
    ) {
        parent::__construct(
            $context
        );
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->branchPickupHelper = $branchPickupHelper;
    }
    /**
     * Change address Action in delivery address grid
     * 
     * return json
     */
    public function execute()
    {
        $frontendHelper = $this->listsFrontendRestrictedHelper;
        /* @var $frontendHelper Epicor_Lists_Helper_Frontend_Restricted */
        $addressId = $this->getRequest()->getParams();
        $addressId['address_id'] = '';
        $branchselect= $this->getRequest()->getParam('branchpickupvisible');
        $removeProducts = $frontendHelper->checkProductAddressNew($addressId, 'shipping');
        if(isset($branchselect)) {
            $helperBranch = $this->branchPickupHelper;
            /* @var $helper Epicor_BranchPickup_Helper_Data */
            $helperBranch->emptyBranchPickup();
            $helperBranch->resetBranchLocationFilter();              
        }         
        $this->sendAjaxResponse($removeProducts, $addressId);
    }
    
    protected function sendAjaxResponse($values, $addressId)
    {
        $frontendHelper = $this->listsFrontendRestrictedHelper;
        /* @var $frontendHelper Epicor_Lists_Helper_Frontend_Restricted */

        $result = array(
            'type' => 'success',
            'values' => !empty($values) ? $values : array(),
            'addressid' => $addressId,
            'details' => $frontendHelper->getShippingAddress($addressId['address_id'])
        );

        //Mage::App()->getResponse()->setHeader('Content-type', 'application/json');
        //Mage::App()->getResponse()->setBody(json_encode($result));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }

}
