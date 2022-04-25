<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\ResourceModel\Dealergroups;


/**
 * Model Collection Class for Dealer Groups
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Dealergroups\Collection
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
        $this->_init('Epicor\Dealerconnect\Model\Dealergroups','Epicor\Dealerconnect\Model\ResourceModel\Dealergroups');
    }

    /**
     * Adds ERP Account filter to the Collection
     *
     * @param integer $erpAccountId
     *
     * @return \Epicor_Dealerconnect_Model_Resource_Dealergroups_Collection
     */
    public function filterByErpAccount($erpAccountId)
    {
        $this->getSelect()->join(
            array('lea' => $this->getTable('ecc_dealer_groups_accounts')), 'lea.group_id=main_table.id', array('lea.dealer_account_id')
        );
        $this->addFieldToFilter('dealer_account_id', $erpAccountId);
        return $this;
    }

    /**
     * Adds ERP Account type filter to the Collection
     *
     * @param string $type
     *
     * @return \Epicor_Dealerconnect_Model_Resource_Dealergroups_Collection
     */
    public function filterByErpAccountType($type)
    {
        $typeFilter = ($type == 'B2B') ? 'B' : 'C';

        $this->addFieldToFilter('erp_account_link_type', $typeFilter);
        return $this;
    }

    /**
     * Adds Active filter to the Collection
     *
     * @return \Epicor_Dealerconnect_Model_Resource_Dealergroups_Collection
     */
    public function filterActive()
    {
        $this->addFieldToFilter('active', 1);

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
}
