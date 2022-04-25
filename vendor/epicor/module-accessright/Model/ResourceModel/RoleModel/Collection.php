<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\ResourceModel\RoleModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Model Collection Class for AccessRight
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Collection extends AbstractCollection
{

    protected $groupedById = false;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {

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
        $this->_init('Epicor\AccessRight\Model\RoleModel','Epicor\AccessRight\Model\ResourceModel\RoleModel');
    }

   /**
     * Adds ERP Account filter to the Collection
     *
     * @param integer $erpAccountId
     *
     * @return \Epicor/AcessRight/Model/Resource/RoleModel/Collection
     */
    public function filterByErpAccount($erpAccountId)
    {
        $this->getSelect()->join(
            array('erp_account' => $this->getTable('ecc_access_role_erp_account')),
            'erp_account.access_role_id = main_table.id', array('erp_account.erp_account_id')
        );
        $this->addFieldToFilter('erp_account_id', $erpAccountId);
        return $this;
    }

    /**
     * Adds ERP Account type filter to the Collection
     *
     * @param string $type
     *
     * @return \Epicor/AccessRight/Model/Resource/RoleModel/Collection
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
     * @return \Epicor\AccessRight\Model\Resource\RoleModel\Collection
     */
    public function filterByCustomer($customerId)
    {
        $this->getSelect()->join(
            array('customer' => $this->getTable('ecc_access_role_customer')), 'main_table.id = customer.access_role_id AND customer.by_role = "1"', array()
        );
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Adds Active filter to the Collection
     *
     * @return \Epicor\AccessRight\Model\Resource\RoleModel\Collection
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
        $this->getSelect()->join(array('erp_account' => $this->getTable('ecc_access_role_erp_account')),
            'erp_account.access_role_id = main_table.id', array('erp_account.erp_account_id'));
        $this->addFieldToFilter('erp_account_id', $erpAccountId);
        return $this;
    }

}
