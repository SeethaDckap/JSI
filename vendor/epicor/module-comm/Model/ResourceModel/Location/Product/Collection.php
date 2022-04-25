<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Location\Product;


/**
 * Location product collection model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Location\Product\Collection
{

    protected $_eventPrefix = 'ecc_location_product';
    protected $_eventObject = 'location_product_collection';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $messageHelper;
    
    protected $locationHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\DataFactory
     */
    protected $branchPickupHelperFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Location\Relatedlocations
     */
    protected $relatedLocations;

    /**
     * @var \Epicor\Comm\Model\Location\Groups
     */
    protected $locationGroups;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Messaging $messageHelper,
        \Epicor\Comm\Helper\Locations $locationHelper,
        \Epicor\BranchPickup\Helper\DataFactory $branchPickupHelperFactory,
        \Epicor\Comm\Model\Location\Relatedlocations $relatedLocations,
        \Epicor\Comm\Model\Location\Groups $locationGroups,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->messageHelper = $messageHelper;
        $this->locationHelper = $locationHelper;
        $this->branchPickupHelperFactory = $branchPickupHelperFactory->create();
        $this->relatedLocations = $relatedLocations;
        $this->locationGroups = $locationGroups;
        $this->registry = $registry;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }


    protected function _construct()
    {
        $this->_init('Epicor\Comm\Model\Location\Product', 'Epicor\Comm\Model\ResourceModel\Location\Product');
    }

    /**
     * Get the extra product infomation for the locations on a given store
     * This will return base_price, currency_code, cost_price for each location on the store
     * if no value is found then it will return the default currency values
     * 
     * @param int $store_id Store id
     * @return \Epicor\Comm\Model\ResourceModel\Location\Product\Collection
     */
    public function joinExtraProductInfo($store_id = null,$productId=null)
    {
        $storeCurrencyCode = $this->storeManager->getStore($store_id)->getBaseCurrencyCode();
        $storeCurrencyCode = $this->messageHelper->getCurrencyMapping($storeCurrencyCode);
             if ($productId) {
                $locationString = $this->locationHelper->getEscapedCustomerDisplayLocationCodes();
                $selectedBranch = $this->branchPickupHelperFactory->getSelectedBranch();
                 if ($selectedBranch && !$this->registry->registry('inventory_view_locations')) {
                     $allowed = $this->locationHelper->getCustomerAllowedLocations();
                     $allowed = array_keys($allowed);
                     $_relatedLocations = $this->relatedLocations->getRelatedLocationsByCode($selectedBranch);
                     $_groupLocations = $this->locationGroups->getGroupLocationCodes($selectedBranch);
                     $locationString = str_replace("'", "", $locationString);
                     $locations = [$locationString];
                     $locations = array_merge($locations, $_relatedLocations, $_groupLocations);
                     $locations = array_unique($locations);
                     $locations = array_intersect($locations, $allowed);
                     $this->registry->unregister('inventory_view_locations');
                     $this->registry->register('inventory_view_locations', $locations);
                     $locationString = "'" . implode("','", $locations) . "'";
                } else if ($selectedBranch && $this->registry->registry('inventory_view_locations')) {
                     $locations = $this->registry->registry('inventory_view_locations');
                     $locationString = "'" . implode("','", $locations) . "'";
                 }

            $this->getSelect()->joinLeft(
                    array(
                'store_location_product_info' => $this->getTable('ecc_location_product_currency')
                    ), 'store_location_product_info.product_id = main_table.product_id AND store_location_product_info.location_code = main_table.location_code where main_table.product_id = ' . $productId . ' AND store_location_product_info.currency_code = "' . $storeCurrencyCode . '"' . ' AND location_info.code in (' . $locationString . ')', array(
                    )
            )->columns(
                    new \Zend_Db_Expr("
                    coalesce(`store_location_product_info`.`currency_code`,'$storeCurrencyCode') as `currency_code`,
                    `store_location_product_info`.`base_price` as `base_price`,
                    `store_location_product_info`.`cost_price` as `cost_price`
                ")
                    // Group by id to avoid bad data causing the grid to fail
            )->group('main_table.id');
        } else {
            // join the store values
            $this->getSelect()->joinLeft(
                    array(
                'store_location_product_info' => $this->getTable('ecc_location_product_currency')
                    ), 'store_location_product_info.product_id = main_table.product_id AND store_location_product_info.location_code = main_table.location_code AND store_location_product_info.currency_code = "' . $storeCurrencyCode . '"', array(
                    )
            )->columns(
                    new \Zend_Db_Expr("
                    coalesce(`store_location_product_info`.`currency_code`,'$storeCurrencyCode') as `currency_code`,
                    `store_location_product_info`.`base_price` as `base_price`,
                    `store_location_product_info`.`cost_price` as `cost_price`
                ")
                    // Group by id to avoid bad data causing the grid to fail
            )->group('main_table.id');
        }
        return $this;
    }

    /**
     * Get the extra product infomation for the locations on a given store
     * This will return base_price, currency_code, cost_price for each location on the store
     * if no value is found then it will return the default currency values
     * 
     * @param int $store_id Store id
     * @return \Epicor\Comm\Model\ResourceModel\Location\Product\Collection
     */
    public function joinLocationInfo()
    {

        $this->getSelect()->joinLeft(
            array(
            'location_info' => $this->getTable('ecc_location')
            ), 'location_info.code = main_table.location_code', array(
            'location_id' => 'id',
            'name',
            'company',
            'address1',
            'address2',
            'address3',
            'city',
            'county',
            'county_code',
            'country',
            'postcode',
            'telephone_number',
            'fax_number',
            'email_address',
            'dummy',
            'source',
            'mobile_number',
            'sort_order',
            'include_inventory'
            )
        );
        $this->getSelect()->order('sort_order asc');

        return $this;
    }

    /**
     * Join company value of location
     *
     * @return $this
     */
    public function joinCompanyInfo()
    {

        $this->getSelect()->joinLeft(
            array(
                'location_info' => $this->getTable('ecc_location')
            ), 'location_info.code = main_table.location_code', array(
                'company'
            )
        );
        $this->getSelect()->order('sort_order asc');

        return $this;
    }


    /**
     * Join company value of location
     *
     * @param array  $ids            Product ids.
     * @param string $locationString Location string for filtering.
     *
     * @return array
     */
    public function filterProductByIds(array $ids, string $locationString)
    {
        $this->getSelect()->where(
            '(`product_id` IN ('.implode(",", $ids).') AND `location_code` IN ('.$locationString.'))');
        $select = $this->getSelect();
        $select->order(new \Zend_Db_Expr('FIELD(product_id, '.implode(',', $ids).')'));
        return $this->getColumnValues('product_id');

    }//end filterProductByIds()


}
