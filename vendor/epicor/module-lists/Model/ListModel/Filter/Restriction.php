<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Filter;


/**
 * Model Class for List Filtering
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Restriction extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    public function __construct(
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory
    ) {
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->registry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
    }
    /**
     * Adds restriction filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filter($collection)
    {
        $helper = $this->listsFrontendRestrictedHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Restricted */

        $address = $helper->getRestrictionAddress();
        /* @var $address Epicor_Comm_Model_Customer_Address */

        //new address in checkout handler
        $checkRegistryCheckout = $this->registry->registry('checkproduct-address-data');
        if ($checkRegistryCheckout) {
            $address = $checkRegistryCheckout;
        }

        if ($address) {
            $listType = 'Rp';

            $matchQuery = $this->getAddressRestrictionSubquery($address);

            $conditions = '(main_table.type != "' . $listType . '" OR (' . 'COUNT((' . $matchQuery . ')) > 0' . '))';

            $collection->getSelect()->having($conditions);
        }

        return $collection;
    }

    /**
     * Generates the query for a restricted purchase check
     * 
     * @param \Epicor\Comm\Model\Customer\Address $address
     * 
     * @return string
     */
    protected function getAddressRestrictionSubquery($address)
    {
        $conn = $this->resourceConnection->getConnection('default_write');
        /* @var $conn Magento_Db_Adapter_Pdo_Mysql */

        $addressCollection = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $addressCollection Epicor_Lists_Model_Resource_ListModel_Address_Collection */

        $addressCollection->getSelect()->reset(\Zend_Db_Select::FROM);
        $addressCollection->getSelect()->from(array(
            'ladd' => $addressCollection->getMainTable()
        ));

        $addressCollection->getSelect()->joinLeft(array(
            'laddr' => $addressCollection->getTable('ecc_list_address_restriction')
            ), 'laddr.address_id = ladd.id', array());

        $addressCollection->getSelect()->where('ladd.list_id = main_table.id');
        $addressCollection->addFieldToFilter('ladd.country', $address->getCountry());


        $addressCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $addressCollection->getSelect()->columns('ladd.id');

        $conditions = $this->getCountryCondition() . $this->getStateCondition($conn, $address) . $this->getZipCondition($conn, $address) . $this->getAddressCondition($conn, $address);

        $addressCollection->getSelect()->where($conditions);

        return $addressCollection->getSelectSql(true);
    }

    /**
     * Gets non-empty values form the address and escapes them
     * 
     * @param Magento_Db_Adapter_Pdo_Mysql $conn
     * @param \Epicor\Comm\Model\Customer\Address $address
     * 
     * @return string
     */
    protected function getCountryCondition()
    {
        return '(laddr.restriction_type = "' . \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_COUNTRY . '")';
    }

    /**
     * Gets the conditionality for a state restriction
     * 
     * @param Magento_Db_Adapter_Pdo_Mysql $conn
     * @param \Epicor\Comm\Model\Customer\Address $address
     * 
     * @return string
     */
    protected function getStateCondition($conn, $address)
    {
        $county = $this->getStateValue($conn, $address);
        return ' OR (laddr.restriction_type = "' . \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_STATE . '" AND ladd.county = ' . $county . ')';
    }

    /**
     * Gets the conditionality for a zip restriction
     * 
     * @param Magento_Db_Adapter_Pdo_Mysql $conn
     * @param \Epicor\Comm\Model\Customer\Address $address
     * 
     * @return string
     */
    protected function getZipCondition($conn, $address)
    {
        $postcode = $this->getQuotedAddressValue($conn, $address, 'postcode');
        return ' OR (laddr.restriction_type = "' . \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ZIP . '" AND (' . $postcode . ' REGEXP ladd.postcode))';
    }

    /**
     * Gets the conditionality for an address restriction
     * 
     * @param Magento_Db_Adapter_Pdo_Mysql $conn
     * @param \Epicor\Comm\Model\Customer\Address $address
     * 
     * @return string
     */
    protected function getAddressCondition($conn, $address)
    {
        $name = $this->getQuotedNameValue($conn, $address, 'name');
        $address1 = $conn->quote($address->getStreet1());
        $address2 = $conn->quote($address->getStreet2());
        $address3 = $conn->quote($address->getStreet3());
        $postcode = $this->getQuotedAddressValue($conn, $address, 'postcode');
        $county = $this->getStateValue($conn, $address);

        $condition = ' OR (laddr.restriction_type = "' . \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ADDRESS . '"';

        if (empty($name) == false) {
            $condition .= ' AND (ladd.name = ' . $name . ' OR ladd.name = "" OR ladd.name IS NULL)';
        }

        if (empty($address1) == false) {
            $condition .= ' AND (ladd.address1 = ' . $address1 . ' OR ladd.address1 = "" OR ladd.address1 IS NULL)';
        }
        if (empty($address2) == false) {
            $condition .= ' AND (ladd.address2 = ' . $address2 . ' OR ladd.address2 = "" OR ladd.address2 IS NULL)';
        }
        if (empty($address3) == false) {
            $condition .= ' AND (ladd.address3 = ' . $address3 . ' OR ladd.address3 = "" OR ladd.address3 IS NULL)';
        }
        if (empty($postcode) == false) {
            $condition .= ' AND (ladd.postcode = ' . $postcode . ' OR ladd.postcode = "" OR ladd.postcode IS NULL)';
        }
        if (empty($county) == false) {
            $condition .= ' AND (ladd.county = ' . $county . ' OR ladd.county = "" OR ladd.county IS NULL)';
        }

        $condition .= ')';

        return $condition;
    }

    protected function getStateValue($conn, $address)
    {
        if ($address->getRegionId()) {
            $region = $this->directoryRegionFactory->create()->load($address->getRegionId());
            /* @var $region Mage_Directory_Model_Region */
            $county = $conn->quote($region->getCode());
        } else {
            $county = $this->getQuotedAddressValue($conn, $address, 'region');
        }

        return $county;
    }

    /**
     * Gets non-empty values form the address and escapes them
     * 
     * @param Magento_Db_Adapter_Pdo_Mysql $conn
     * @param \Epicor\Comm\Model\Customer\Address $address
     * @param string $key
     * 
     * @return string
     */
    protected function getQuotedAddressValue($conn, $address, $key, $useVal = "''")
    {
        $val = "''";

        if ($key == 'region') {
            if (is_object($address->getData($key))) {
                $region = $address->getData($key)->getRegion();
                $val = $conn->quote($region);
            } else {
                $val = $conn->quote($address->getData($key));
            }
        } else {
            if ($address->getData($key)) {
                $val = $conn->quote($address->getData($key));
            }
        }

        return $val;
    }

    /**
     * Gets non-empty values form the name and escapes them
     * 
     * @param Magento_Db_Adapter_Pdo_Mysql $conn
     * @param \Epicor\Comm\Model\Customer\Address $address
     * @param string $key
     * 
     * @return string
     */
    protected function getQuotedNameValue($conn, $address, $key, $useVal = "''")
    {
        $val = "''";
        $firstname = $address->getData('firstname');
        $lastname = $address->getData('lastname');
        $name = $firstname . ' ' . $lastname;
        $fullName = $name;
        if (trim($fullName)) {
            $val = $conn->quote($fullName);
        }
        return $val;
    }

}
