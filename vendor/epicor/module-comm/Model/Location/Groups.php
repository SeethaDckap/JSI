<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Location;


/**
 * Locations Group model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method \Epicor\Comm\Model\Location\Groups setGroupName(string $value)
 * @method \Epicor\Comm\Model\Location\Groups setGroupExpandable(boolean $value)
 * @method \Epicor\Comm\Model\Location\Groups setShowAggregateStock(boolean $value)
 * @method \Epicor\Comm\Model\Location\Groups setEnabled(boolean $value)
 * @method \Epicor\Comm\Model\Location\Groups setOrder(integer $value)
 * 
 * @method string getGroupName()
 * @method boolean getRelatedLocationId()
 * @method boolean getShowAggregateStock()
 * @method boolean getEnabled()
 * @method integer getOrder()
 */
class Groups extends \Epicor\Common\Model\AbstractModel
{

//    protected $_eventPrefix = 'ecc_location_product';
//    protected $_eventObject = 'location_product';
//    private $_currencies;
//    private $_deleteCurrencies = array();
//
//    /**
//     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory
//     */
//    protected $commResourceLocationProductCurrencyCollectionFactory;
//
//    /**
//     * @var \Epicor\Comm\Model\Location\Product\CurrencyFactory
//     */
//    protected $commLocationProductCurrencyFactory;
//
//    /**
//     * @var \Epicor\Comm\Helper\Messaging
//     */
//    protected $commMessagingHelper;
//
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;
    
    /**
     *
     * @var \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory 
     */
    protected $groupLocationsCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory $groupLocationsCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->commLocationFactory = $commLocationFactory;
        $this->groupLocationsCollectionFactory = $groupLocationsCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Groups');
    }
    
    /**
     * Get Group locations collection
     * 
     * @param type $locationcode
     * @return Object
     */
    public function getGroupings($locationCode)
    {
        $location = $this->commLocationFactory->create();
        /* @var $location \Epicor\Comm\Model\Location */
        $location->load($locationCode, 'code');
        $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $addCondition = "";
        switch ($stockVisibility) {
            case 'default':
            case 'logged_in_shopper_source':
                $addCondition = " and locations.id = 0";
                break;
            case 'locations_to_include':
                $locationsToInclude = $this->scopeConfig->getValue('epicor_comm_locations/global/locations_to_include', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $locationCodesToInclude = explode(',', $locationsToInclude);
                $addCondition = " and locations.code in ('" . implode("', '", $locationCodesToInclude) . "')";
                break;
            case 'locations_to_exclude':
                $locationsToExclude = $this->scopeConfig->getValue('epicor_comm_locations/global/locations_to_exclude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $locationCodesToExclude = explode(',', $locationsToExclude);
                $addCondition = " and locations.code not in ('" . implode("', '", $locationCodesToExclude) . "')";
                break;
        }
        
        $groups = $this->groupLocationsCollectionFactory->create();
        $locationsTable = $groups->getTable('ecc_location');
        $groupsTable = $groups->getTable('ecc_location_groups');
        $groups->getSelect()
                ->join(
                    array('groups' => $groupsTable),
                    'main_table.group_id = groups.id'
                )
                ->join(
                    array('locations' => $locationsTable),
                    'main_table.group_location_id = locations.id'
                )
                ->where('groups.enabled = 1' . $addCondition);
        $groups->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns('main_table.id as id')
                ->columns('groups.id as group_id')
                ->columns('groups.group_name')
                ->columns('groups.group_expandable')
                ->columns('groups.show_aggregate_stock')
                ->columns('locations.id as location_id')
                ->columns('locations.code')
                ->columns('locations.name')
                ->columns('locations.address1')
                ->columns('locations.address2')
                ->columns('locations.address3')
                ->columns('locations.city')
                ->columns('locations.county')
                ->columns('locations.county_code')
                ->columns('locations.country')
                ->columns('locations.postcode')
                ->columns('locations.telephone_number')
                ->columns('locations.location_visible')
                ->columns('locations.include_inventory')
                ->columns('locations.show_inventory')
                ->order(array('groups.order', 'groups.group_name', 'main_table.position'));
        return $groups;
    }

    /**
     * Get group location of selected branch
     *
     * @param string $locationCode
     * @return Object
     */
    public function getGroupLocations($locationCode)
    {
        $groups = $this->getGroupings($locationCode);
        $_groups = array();
        foreach ($groups as $group) {
            $_groups[$group->getGroupName()]['group_id'] = $group->getGroupId();
            $_groups[$group->getGroupName()]['group_expandable'] = $group->getGroupExpandable();
            $_groups[$group->getGroupName()]['show_aggregate_stock'] = $group->getShowAggregateStock();
            //$_groups[$group->getGroupName()]['locations'][] = $group->getCode();
            $_groups[$group->getGroupName()]['locations'][$group->getCode()]['include_inventory'] = $group->getIncludeInventory();
            $_groups[$group->getGroupName()]['locations'][$group->getCode()]['show_inventory'] = $group->getShowInventory();
            $_groups[$group->getGroupName()]['locations'][$group->getCode()]['location_visible'] = $group->getLocationVisible();
        }
        return $_groups;
    }

    /**
     * Get group location code of selected branch
     *
     * @param string $locationCode
     * @return array
     */
    public function getGroupLocationCodes($locationCode)
    {
        if (!$this->_registry->registry('group_locations_'.$locationCode)) {
            $groups = $this->getGroupings($locationCode);
            $locations = [];
            foreach ($groups as $group) {
                $locations[] = $group->getCode();
            }
            $this->_registry->unregister('group_locations_'.$locationCode);
            $this->_registry->register('group_locations_'.$locationCode, $locations);
        }
        $locations = $this->_registry->registry('group_locations_'.$locationCode);
        return $locations;
    }

    /**
     * Returns locations for a given group
     *
     * @param int $groupId
     * @return array
     */
    public function getLocations($groupId)
    {
        $locations = $this->groupLocationsCollectionFactory->create();
        $locationsTable = $locations->getTable('ecc_location');
        $locations->getSelect()
            ->join(
                array('locations' => $locationsTable),
                'main_table.group_location_id = locations.id'
            )
            ->where('main_table.group_id = '.$groupId.' and locations.location_visible = 1');
        $locations->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('locations.*');
        $_locations = array();
        foreach($locations as $location) {
            $_locations[$location->getCode()] = $location;
        }
        return $_locations;
    }
}
