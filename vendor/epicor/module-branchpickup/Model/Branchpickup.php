<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Model;

use Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup;

/**
 * Model Class for BranchPickup
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Branchpickup extends \Magento\Framework\Model\AbstractModel
{

    /**
     *
     * @var \Epicor\BranchPickup\Helper\Data 
     */
    private $_helper;

    /**
     *
     * @var \Epicor\BranchPickup\Helper\Branchpickup 
     */
    private $_helperBranch;

    /**
     *
     * @var \Epicor\Comm\Helper\Locations 
     */
    private $_helperLocation;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $ProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Model\Message\Request\BsvFactory
     */
    protected $commMessageRequestBsvFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    
     /**
     * @var \Epicor\Comm\Helper\Cart\SendbsvFactory
     */
    protected $sendBsvHelperFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ProductCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Model\Message\Request\BsvFactory $commMessageRequestBsvFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResponseInterface $response,
        \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->branchPickupHelper = $branchPickupHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->ProductCollectionFactory = $ProductCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->checkoutSession = $checkoutSession;
        $this->commMessageRequestBsvFactory = $commMessageRequestBsvFactory;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->response = $response;
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_helper = $this->branchPickupHelper;
        $this->_helperBranch = $this->branchPickupBranchpickupHelper;
        $this->_helperLocation = $this->commLocationsHelper;
    }

    /**
     * Check the product is available for the pickup location(While Adding the product to the cart)
     * Used In Observer
     * @return boolean
     */
    public function checkProductAvailability($locationCode, $productId)
    {
        $productCollection = $this->ProductCollectionFactory->create();
        /* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */

        //M1 > M2 Translation Begin (Rule 39)
        // $locationCode4Sql = $this->resourceConnection->getConnection('default_write')->quote($locationCode);
        $locationCode4Sql = $this->resourceConnection->getConnection()->quote($locationCode);
        //M1 > M2 Translation End
        $productCollection->getSelect()->joinInner(array(
            'loc' => $productCollection->getTable('ecc_location_product')
            ), 'loc.product_id=e.entity_id AND loc.location_code=' . $locationCode4Sql . '', array(
            '*'
            ), null, 'left');
        $productCollection->getSelect()->group('e.entity_id');
        $existingProductKeys = $productCollection->getAllIds();
        if (!in_array($productId, $existingProductKeys)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * pickup validation based on the location code(Whether the item is available for the location or not)
     * In Controller
     * @return json
     */
    public function pickupValidation($locationCode)
    {
        //First Checking products available for the particular location
        $locationCode = !$locationCode ? $this->commLocationsHelper->getDefaultLocationCode() : $locationCode;
        $productLocations = $this->checkProductLocations($locationCode);
        $cartItems = $this->getCartItems();
        $result = array_diff($cartItems, $productLocations);
        //If the product is available/not available for the particular location
        //Checking any restrictions available for the product
        $listHelper = $this->listsFrontendRestrictedHelper;
        $passAddress = $listHelper->getLocationAddress($locationCode, 'shipping');
        $urlEncodeVals = http_build_query($passAddress, '', '&');
        $checkAddressResult = $listHelper->checkProductAddressNew($urlEncodeVals, 'shipping');
        //Merging the location and restricted productids(if found)
        $result = array_merge($result, $checkAddressResult);
        $select['type'] = 'success';
        $select['values'] = (!empty($result) && !is_null($locationCode)) ? $result : array();
        $select['locationcode'] = $locationCode;
        $select['details'] = $this->_helper->getPickupAddress($locationCode,true);
        $this->response->setHeader('Content-type', 'application/json');
        $this->response->setBody(json_encode($select));
    }

    /**
     * Get all the product Id's for the location
     * 
     * @return array() product Ids
     */
    public function checkProductLocations($locationCode)
    {
        $productCollection = $this->ProductCollectionFactory->create();
        /* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
        //M1 > M2 Translation Begin (Rule 39)
        // $locationCode4Sql = $this->resourceConnection->getConnection('default_write')->quote($locationCode);
        $locationCode4Sql = $this->resourceConnection->getConnection()->quote($locationCode);
        //M1 > M2 Translation End

        $productCollection->getSelect()->joinInner(array(
            'loc' => $productCollection->getTable('ecc_location_product')
            ), 'loc.product_id=e.entity_id AND loc.location_code=' . $locationCode4Sql . '', array(
            '*'
            ), null, 'left');
        $productCollection->getSelect()->group('e.entity_id');
        $existingProductKeys = $productCollection->getAllIds();
        return $existingProductKeys;
    }

    /**
     * Get the cart items for the session
     * 
     * @return array() product Ids
     */
    public function getCartItems()
    {
        $quote = $this->checkoutSession->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        $productId = array();
        foreach ($cartItems as $item) {
            $productId[] = $item->getProductId();
        }
        return $productId;
    }

    /**
     * Update the cart item with Location code and Location name when there is a change in the branch pickup
     * 
     * 
     */
    public function updateBranchLocationsQuote($locationCode,$noBsv=false, $emptyCartCheck = false)
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        $locationCode = !$locationCode ? $this->commLocationsHelper->getDefaultLocationCode() : $locationCode;
        $locationName = $this->_helperLocation->getLocationName($locationCode);
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        foreach ($items as $item) {
            $item->setEccLocationCode($locationCode);
            $item->setEccLocationName($locationName);
            $item->save();
        }
        $sendBsv = false;
        if(!$noBsv && !$emptyCartCheck) {
            $sendBsv = true;
        }elseif($emptyCartCheck
            && $quote->getItemsCount() > 0
            && (is_null($shippingMethod)
            || $shippingMethod == Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE)
        ){
            $sendBsv = true;
        }

        if($sendBsv){
            // May need in future.
        }

        if(is_null($locationCode)){
            $quote->getShippingAddress()->setShippingMethod(null);
        }
        $quote->save();
    }
}
