<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel;


/**
 * Model Collection Class for List
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Collection
{

    protected $groupedById = false;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel','Epicor\Lists\Model\ResourceModel\ListModel');
    }

    public function addProductsCount()
    {
        $this->getSelect()->join(
            array('lpc' => $this->getTable('ecc_list_product')), 'lpc.list_id = main_table.id', array('products_count' => 'COUNT(lpc.sku)')
        );

        $this->groupById();

        return $this;
    }

    /**
     * Adds ERP Account filter to the Collection
     *
     * @param integer $erpAccountId
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterByErpAccount($erpAccountId)
    {
        $this->getSelect()->join(
            array('lea' => $this->getTable('ecc_list_erp_account')), 'lea.list_id=main_table.id', array('lea.erp_account_id')
        );
        $this->addFieldToFilter('erp_account_id', $erpAccountId);
        return $this;
    }

    /**
     * Adds ERP Account type filter to the Collection
     *
     * @param string $type
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterByErpAccountType($type)
    {
        $typeFilter = ($type == 'B2B') ? 'B' : 'C';

        $this->addFieldToFilter('erp_account_link_type', $typeFilter);
        return $this;
    }

    /**
     * Adds Customer filter to the Collection
     *
     * @param integer $customerId
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterByCustomer($customerId)
    {
        $this->getSelect()->join(
            array('customer' => $this->getTable('ecc_list_customer')), 'main_table.id = customer.list_id', array()
        );
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Adds Customer filter to the Collection
     *
     * @param integer $groupId
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterByStoreGroup($groupId)
    {
        $this->getSelect()->join(
            array('store' => $this->getTable('ecc_list_store_group')), 'main_table.id = store.list_id', array()
        );
        $this->addFieldToFilter('store_group_id', $groupId);
        return $this;
    }

    /**
     * Adds Customer filter to the Collection
     *
     * @param integer $websiteId
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterByWebsite($websiteId)
    {
        $this->getSelect()->join(
            array('website' => $this->getTable('ecc_list_website')), 'main_table.id = website.list_id', array()
        );
        $this->addFieldToFilter('website_id', $websiteId);
        return $this;
    }

    /**
     * Adds Addresses Code filter to the Collection
     *
     * @param string $addressCode
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterByAddressCode($addressCode)
    {
        if (is_array($addressCode)) {
            $addressCode = array('in' => $addressCode);
        }

        $this->getSelect()->join(
            array('la' => $this->getTable('ecc_list_address')), 'la.list_id=main_table.id', array('la.address_code')
        );
        $this->addFieldToFilter('address_code', $addressCode);

        $this->addFieldToFilter('la.activation_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('lteq' => now()),
            array('lteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        $this->addFieldToFilter('la.expiry_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('gteq' => now()),
            array('gteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        return $this;
    }

    /**
     * Adds List Type filter to the Collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterLists()
    {
        $typeModel = $this->listsListModelTypeFactory->create();
        /* @var $typeModel Epicor_Lists_Model_ListModelModel_Type */
        $this->addFieldToFilter('type', array('in' => $typeModel->getListTypes()));

        return $this;
    }

    /**
     * Adds Contract Type filter to the Collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterContracts()
    {
        $typeModel = $this->listsListModelTypeFactory->create();
        /* @var $typeModel Epicor_Lists_Model_ListModelModel_Type */
        $this->addFieldToFilter('type', array('in' => $typeModel->getContractTypes()));

        return $this;
    }

    /**
     * Adds Active filter to the Collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterActive()
    {
        $this->addFieldToFilter('active', 1);
        $this->addFieldToFilter('start_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('lteq' => now()),
            array('lteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        $this->addFieldToFilter('end_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('gteq' => now()),
            array('gteq' =>date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        return $this;
    }

    public function groupById()
    {
        if (!$this->groupedById) {
            $this->getSelect()->group('main_table.id');
            $this->groupedById = true;
        }

        return $this;
    }

    /**
     * (Fix) using group() breaks getSelectCountSql in magento
     *  Wrong count in admin Grid when using GROUP BY clause, overriding lib module
     * 
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::HAVING);

        if (count($this->getSelect()->getPart(\Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(\Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(\Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }

        return $countSelect;
    }

    public function addFilterByErpAccount($erpAccountId)
    {

        $this->getSelect()->join(array('lea' => $this->getTable('ecc_list_erp_account')), 'lea.list_id=main_table.id', array('lea.erp_account_id'));
        $this->addFieldToFilter('erp_account_id', $erpAccountId);
        return $this;
    }

}
