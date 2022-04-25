<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;

use Epicor\Comm\Model\MinOrderAmountFlag;

class Erpinfo extends \Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\AbstractBlock
{

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var \Epicor\Comm\Model\Config\Source\Yesnonulloption
     */
    protected $yesnonulloption;

    /**
     * @var \Epicor\BranchPickup\Model\Config\Source\Branchoptions
     */
    protected $branchoptions;

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $locationFactory;
    /*
     * @var \Epicor\Dealerconnect\Model\Config\Source\ErpLoginModeType ,
     */
    protected $loginmodeTypeOptions;
    /*
     * @var \Epicor\Dealerconnect\Model\Config\Source\ErpToggleAllowed 
     */
    protected $toggleOptions;
    /*
     * @var \Epicor\Dealerconnect\Model\Config\Source\showCusPriceOptions 
     */
    protected $showCusPriceOptions;
    /*
     * @var \Epicor\Dealerconnect\Model\Config\Source\showMarginOptions 
     */
    protected $showMarginOptions;
    /*
     * @var \Epicor\Customerconnect\Model\Config\Source\ArpaymentOptions 
     */    
    protected $showIsArpaymentAllowed;
    /*
    * @var \Epicor\Dealerconnect\Model\Config\Source\ErpToggleAllowed
    */
    protected $inventorySearchOptions;
    /*
    * @var \Epicor\Dealerconnect\Model\Config\Source\DealerGroups
    */
    protected $dealerGroups;
    /*
    * @var \Epicor\AccessRight\Model\Config\Source\AccessRightOptions
    */
    protected $accessRightOptions;
    /*
    * @var \Epicor\AccessRight\Model\Config\Source\AccessRoles
    */
    protected $accessRoles;
    /*
    * @var \Epicor\Customerconnect\Model\Config\Source\MiscViewType
    */
    protected $miscViewOptions;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        \Epicor\Comm\Model\Config\Source\Yesnonulloption $yesnonulloption,
        \Epicor\BranchPickup\Model\Config\Source\Branchoptions $branchoptions,
        \Epicor\Comm\Model\LocationFactory $locationFactory,
        \Epicor\Dealerconnect\Model\Config\Source\ErpLoginModeType $loginmodeTypeOptions,
        \Epicor\Dealerconnect\Model\Config\Source\ErpToggleAllowed $toggleOptions,
        \Epicor\Dealerconnect\Model\Config\Source\showCusPriceOptions $showCusPriceOptions,
        \Epicor\Dealerconnect\Model\Config\Source\showMarginOptions $showMarginOptions,
        \Epicor\Customerconnect\Model\Config\Source\ArpaymentOptions $showIsArpaymentAllowed,
        \Epicor\Dealerconnect\Model\Config\Source\InventorySearchType $inventorySearchOptions,
        \Epicor\Dealerconnect\Model\Config\Source\DealerGroups $dealerGroups,
        \Epicor\AccessRight\Model\Config\Source\AccessRightOptions $accessRightOptions,
        \Epicor\AccessRight\Model\Config\Source\AccessRoles $accessRoles,
        \Epicor\Customerconnect\Model\Config\Source\MiscViewType $miscViewOptions,
        array $data = []
    ) {
        $this->yesnonulloption = $yesnonulloption;
        $this->branchoptions = $branchoptions;
        $this->locationFactory = $locationFactory;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->loginmodeTypeOptions = $loginmodeTypeOptions;
        $this->toggleOptions = $toggleOptions;
        $this->showCusPriceOptions = $showCusPriceOptions;
        $this->showMarginOptions = $showMarginOptions;
        $this->showIsArpaymentAllowed = $showIsArpaymentAllowed;
        $this->inventorySearchOptions = $inventorySearchOptions;
        $this->dealerGroups = $dealerGroups;
        $this->accessRightOptions = $accessRightOptions;
        $this->accessRoles = $accessRoles;
        $this->miscViewOptions = $miscViewOptions;
        parent::__construct(
            $context,
            $registry,
            $data
        );
        $this->_title = 'Details';
        $this->setTemplate('epicor_comm/customer/erpaccount/edit/erpinfo.phtml');
    }

    public function getCustomerGroup()
    {
        $customerGroupId = $this->getErpCustomer()->getMagentoId();
        $model = $this->customerGroupFactory->create()->load($customerGroupId);
        return $model;
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }
    //M1 > M2 Translation End


    //M1 > M2 Translation Begin (Rule p2-1)
    public function getYesNoNullOption()
    {
        return $this->yesnonulloption;
    }

    public function getBranchOption()
    {
        return $this->branchoptions;
    }

    public function getLocation()
    {
        return $this->locationFactory;
    }
    
    public function  getToggleOption(){
        return $this->toggleOptions;
    }
    
    public function getLoginModeOptions(){
        return $this->loginmodeTypeOptions;
    }
    
    public function  getCusPriceOptions(){
        return $this->showCusPriceOptions;
    }
    
    public function getMarginOptions(){
        return $this->showMarginOptions;
    }

    public function getIsArpaymentAllowed(){
        return $this->showIsArpaymentAllowed;
    }    
    
    public function getIsWarrantyAllowed(){
        return $this->showMarginOptions;
    }

    public function getSearchOptions(){
        return $this->inventorySearchOptions;
    }

    public function getDealerGroups(){
        return $this->dealerGroups;
    }
    //M1 > M2 Translation End

    public function getMinOrderAmountFlagOptionsHtml(int $flagValue)
    {
        return MinOrderAmountFlag::getMinOrderAmountFlagOptionsHtml($flagValue);
    }
    public function getAccessRightOptions()
    {
        return $this->accessRightOptions;
    }
    public function getAccessRoles()
    {
        return $this->accessRoles;
    }
    public function getMiscViewOptions()
    {
        return $this->miscViewOptions;
    }

    /**
     * Get the locations for ERP account
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getLocationLists()
    {
        $_company = $this->getErpCustomer()->getCompany();
        $_locations = $this->getLocation()->create()->getCollection();
        if ($_company) {
            $_locations->addFieldToFilter('company', $_company);
        }
        return $_locations;
    }
}
