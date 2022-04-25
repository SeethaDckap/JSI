<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block;

class Selectedbranch extends \Epicor\Comm\Block\Catalog\Product\Listing\Locations
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;
    
    /**
     *
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $helperLocations;
    
    /**
     *
     * @var \Epicor\Comm\Model\Location\Relatedlocations
     */
    protected $relatedLocations;
    
    /**
     *
     * @var \Epicor\Comm\Model\Location\Groups
     */
    protected $groups;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     *
     * @var \Epicor\BranchPickup\Helper\Branchpickup 
     */
    protected $helperBranch;
    
    /**
     *
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $helper;
    
    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface 
     */
    protected $messageManager;

    /**
     * @var array
     */
    protected $productLocations = [];

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory
     */
    protected $commResourceLocationProductCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * Selectedbranch constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Msrp\Helper\Data $catalogHelper
     * @param \Epicor\Comm\Model\LocationFactory $commLocationFactory
     * @param \Epicor\Comm\Model\Location\Relatedlocations $relatedLocations
     * @param \Epicor\Comm\Model\Location\Groups $groups
     * @param \Epicor\BranchPickup\Helper\Branchpickup $helperBranch
     * @param \Epicor\BranchPickup\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Framework\Registry $registry,
        \Magento\Msrp\Helper\Data $catalogHelper,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Epicor\Comm\Model\Location\Relatedlocations $relatedLocations,
        \Epicor\Comm\Model\Location\Groups $groups,
        \Epicor\BranchPickup\Helper\Branchpickup $helperBranch,
        \Epicor\BranchPickup\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutsession;
        $this->commLocationFactory = $commLocationFactory;
        $this->relatedLocations = $relatedLocations;
        $this->catalogHelper = $catalogHelper;
        $this->groups = $groups;
        $this->helperBranch = $helperBranch;
        $this->helper = $helper;
        $this->helperLocations = $this->helper->getLocationHelper();
        $this->messageManager = $messageManager;
        $this->commResourceLocationProductCollectionFactory = $commResourceLocationProductCollectionFactory;
        $this->configProvider = $configProvider;
        $this->directoryCountryFactory = $directoryCountryFactory;
        parent::__construct(
            $context,
            $registry,
            $catalogHelper,
            $layerResolver,
            $data
        );
    }
    
    public function _construct()
    {
        parent::_construct();
        $style = $this->helperLocations->getLocationStyle();
        $selectedBranch = $this->helper->getSelectedBranch();

        //Check SalesRep Access
        $checkSalesRep = $this->helper->checkSalesRep();
        //Check Supplier Access
        $checkSupplier = $this->helper->checkSupplier();
        if (!$checkSalesRep || !$checkSupplier) {
            $this->helper->emptyBranchPickup();
            $this->helper->resetBranchLocationFilter();
            return;
        }
        if($this->isInventoryViewEnabled($style, $selectedBranch)) {
            $defaultLocationCode = $this->helperLocations->getDefaultLocationCode();
            $this->helper->selectBranchPickup($defaultLocationCode, false, true);
            $this->helperBranch->setBranchLocationFilter($defaultLocationCode);
        } else if ($this->isInventoryViewDisabled($style, $selectedBranch)) {
            $this->helper->emptyBranchPickup();
            $this->helper->resetBranchLocationFilter();
        }
        $allowed = array_keys($this->helperLocations->getCustomerAllowedLocations());
        $controllerName = $this->getRequest()->getControllerName();
        if ($selectedBranch &&
            (!$this->helperLocations->getLocation($selectedBranch)->getLocationVisible()
                || !in_array($selectedBranch, $allowed))
        ) {
            $error = __('Access to Site is blocked, as selected branch is not valid');
            if (($this->helperLocations->errorExists('customer/session', $error) == false) &&
                $controllerName != 'portal') {
                $this->messageManager->addErrorMessage($error);
            }
             
           if (!$this->helperLocations->getCustomerSession()->getIsNotified() &&
                $controllerName != 'portal') {
               $this->helperLocations->getCustomerSession()->setIsNotified(true);
                $erpAccount = $this->helperLocations->getErpAccountInfo();
                $title = "Inventory View Issue:";
                $message = "Please set valid location for " . $erpAccount->getName() . ".";
               $this->helperLocations->sendMagentoMessage($message, $title, \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE);
            }
        }
    }

    private function isInventoryViewEnabled($style, $selectedBranch) {
        return $this->helperLocations->isLocationsEnabled()
            && $style == 'inventory_view'
            && !$selectedBranch;
    }

    private function isInventoryViewDisabled($style, $selectedBranch) {
        return $style == 'location_view'
            && !$this->helper->isBranchPickupAvailable()
            && $selectedBranch;
    }
    
    /**
     * Get Related Locations for selected branch
     * 
     * @param string $locationCode
     */
    public function getRelatedLocations($locationCode)
    {
        $location = $this->commLocationFactory->create();
        /* @var $location \Epicor\Comm\Model\Location */
        $location->load($locationCode, 'code');
        $locationId = $location->getId();
        $allowed = array_keys($this->helperLocations->getCustomerAllowedLocations());
        $relatedLocations = $this->relatedLocations->getRelatedLocations($locationId);
        $relatedLocations->addFieldToFilter('main_table.location_visible', 1)
                        ->addFieldToFilter('main_table.code', array('in' => $allowed));
        return $relatedLocations;
    }
    
    /**
     * Get collection of all locations allowed for customer
     * 
     * @return array
     */
    public function getAllLocations()
    {
        return $this->helperLocations->_getCustomerAllowedLocations();
    }
    
    /**
     * Returns address of the branch
     *
     * @param \Epicor\Comm\Model\Location $location
     * @return string
     */
    public function getBranchAddress($location)
    {
        $address = "";
        $address .= ($location->getAddress1()) ? $location->getAddress1().", " : ""; 
        $address .= ($location->getAddress2()) ? $location->getAddress2().", " : ""; 
        $address .= ($location->getAddress3()) ? $location->getAddress3().", " : ""; 
        $address .= ($location->getCity()) ? $location->getCity().", " : ""; 
        $address .= ($location->getCounty()) ? $location->getCounty().", " : ""; 
        $address .= ($location->getCountry()) ? $location->getCountry().", " : ""; 
        $address .= ($location->getPostcode()) ? $location->getPostcode().", " : ""; 
        return rtrim($address,", ");
    }
    
    /**
     * Get Group Locations for the product
     * 
     * @return Object
     */
    public function getGroupLocations($locationCode)
    {
        $allowed = array_keys($this->helperLocations->getCustomerAllowedLocations());
        $groups = $this->groups->getGroupings($locationCode);
        $groups->addFieldToFilter('locations.code', array('in' =>$allowed));
        return $groups;     
    }

    /**
     * @param int|string $locationCode
     * @return \Epicor\Comm\Model\Location
     */
    public function getSelectedBranch($locationCode)
    {
        return $this->commLocationFactory->create()->load($locationCode, 'code');
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Gets the related locations details for product
     *
     * @return array
     */
    public function getRelatedLocationsForProduct($selectedBranch)
    {
        $_product = $this->getProduct();
        if (!$this->getRegistry()->registry('related_locations')) {
            $_relatedLocations = $this->relatedLocations->getRelatedLocationsForProduct($selectedBranch);
            $this->getRegistry()->unregister('related_locations');
            $this->getRegistry()->register('related_locations', $_relatedLocations);
        }
        $_relatedLocations = $this->getRegistry()->registry('related_locations');
        $related_locations = array_keys($_relatedLocations);
        $_locations = $_product->getLocations();
        $allowed = $this->helperLocations->getCustomerAllowedLocations();
        $locations = array_intersect_key($_locations, $allowed);
        $relatedLocations = [];

        $locVisibilityCount = $showInventoryCount = 0;
        foreach ($locations as $location) {
            if (in_array(strval($location->getLocationCode()), $related_locations)) {
                $includeInventory = $_relatedLocations[strval($location->getLocationCode())]['include_inventory'];
                $showInventory = $_relatedLocations[strval($location->getLocationCode())]['show_inventory'];
                $locationVisible = $_relatedLocations[strval($location->getLocationCode())]['location_visible'];
                $location->setIncludeInventory($includeInventory);
                $location->setShowInventory($showInventory);
                $location->setLocationVisible($locationVisible);
                if($includeInventory) {
                    $relatedLocations[strval($location->getLocationCode())] = $location;
                } else {
                    $relatedLocations[] = $location;
                }
                if ($locationVisible) {
                    $locVisibilityCount++;
                }
                $showInventoryCount += $showInventory;
            }
        }
        $this->getRegistry()->register('rellocation_visibility_count_'.$_product->getId(), $locVisibilityCount);
        $this->getRegistry()->register('rellocation_showinventory_count_'.$_product->getId(), $showInventoryCount);
        return $relatedLocations;
    }

    /**
     * Returns the Location Grouping for product
     *
     * @return array
     */
    public function getGroupings($selectedBranch)
    {
        $_product = $this->getProduct();
        $_locations = $_product->getLocations();
        $allowed = $this->helperLocations->getCustomerAllowedLocations();
        $locations = array_intersect_key($_locations, $allowed);
        if (!$this->getRegistry()->registry('branch_groupings')) {
            $groups = $this->groups->getGroupLocations($selectedBranch);
            $this->getRegistry()->unregister('branch_groupings');
            $this->getRegistry()->register('branch_groupings', $groups);
        }
        $groups = $this->getRegistry()->registry('branch_groupings');
        $_groups = [];
        foreach ($groups as $groupName => $group) {
            $_groups[$groupName]['group_id'] = $group['group_id'];
            $_groups[$groupName]['group_expandable'] = $group['group_expandable'];
            $_groups[$groupName]['show_aggregate_stock'] = $group['show_aggregate_stock'];
            $_groups[$groupName]['location_visibility_count'] = $_groups[$groupName]['location_showinventory_count'] = 0;
            $groupLocations = array_keys($group['locations']);
            foreach ($locations as $location) {
                if (in_array(strval($location->getLocationCode()), $groupLocations)) {
                    $includeInventory = $group['locations'][strval($location->getLocationCode())]['include_inventory'];
                    $showInventory = $group['locations'][strval($location->getLocationCode())]['show_inventory'];
                    $locationVisibile = $group['locations'][strval($location->getLocationCode())]['location_visible'];
                    $location->setIncludeInventory($includeInventory);
                    $location->setShowInventory($showInventory);
                    $location->setLocationVisible($locationVisibile);
                    if ($includeInventory) {
                        $_groups[$groupName]['locations'][strval($location->getLocationCode())] = $location;
                    } else {
                        $_groups[$groupName]['locations'][] = $location;
                    }
                    if ($locationVisibile) {
                        $_groups[$groupName]['location_visibility_count']++;
                    }
                    $_groups[$groupName]['location_showinventory_count'] += $showInventory;
                }
            }
        }
        return $_groups;
    }
    /**
     * Returns the current product
     *
     * @return \Epicor\Comm\Model\Product
     */
    public function getProduct()
    {
        if ($this->getAssociatedProduct()) {
            return $this->getAssociatedProduct();
        }
        return $this->registry->registry('current_product');
    }

    public function getCheckoutConfig()
    {
        $checkoutConfig = $this->configProvider->getConfig();
        $checkoutConfig = \Zend_Json::encode($checkoutConfig);
        return $checkoutConfig;
    }

    public function checkErrors($branch)
    {
        $address1 = $branch->getData('address1');
        $address2 = $branch->getData('address2');
        $address3 = $branch->getData('address3');
        $emptystreet = false;
        if(!$address1 && !$address2 && !$address3) {
            $emptystreet= true;
        }
        $error = 0;
        if($emptystreet || !$branch->getCity() || !$branch->getPostcode() || !$branch->getCountry() || !$branch->getTelephoneNumber()) {
            $error = 1;
        }
        if(!empty($branch->getCountry())) {
            $stateArray = $this->directoryCountryFactory->create()->setId($branch->getCountry())->getLoadedRegionCollection()->toOptionArray();
            if((!empty($stateArray)) && (!($branch->getCountyCode()))) {
                $error = 1;
            }
        }
        $showpopuperror="1";
        if($error) {
            $showpopuperror = "2";
        }
        return $showpopuperror;
    }
}
