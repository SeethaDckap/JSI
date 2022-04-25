<?php
/**
 * Copyright Â© 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing;


/**
 * Quick add block
 *
 * Displays the quick add to Basket / wishlist block
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Reorder extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locationHelper;
   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $locationsHelper,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->locationHelper = $locationsHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
         $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/debm/reorder.phtml');
    }

//    /**
//     * Checks to see if the autocomplete is allowed
//     */

    public function showLocations()
    {
        // check if locations enabled and loations are to be displayed
        $showLocations = $this->locationHelper->isLocationsEnabled();
        if ($showLocations) {
            $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (in_array($stockVisibility, (array('all_source_locations', 'default')))) {                 // if default location code required         
                $showLocations = false;
            }
        }
        return $showLocations;
    }

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-8)
    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    //M1 > M2 Translation End
    
    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
