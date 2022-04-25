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
class Store extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $storeGroupFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\GroupFactory $storeGroupFactory
    ) {
        $this->storeManager = $storeManager;
        $this->storeGroupFactory = $storeGroupFactory;
    }
    /**
     * Adds Store Group filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filter($collection)
    {
        if (!($storeGroupId = $this->getStoreGroupId())) {
            $store = $this->storeManager->getStore();
            /* @var $store Epicor_Comm_Model_Store */
            $storeGroup = $store->getGroup();
            /* @var $storeGroup Mage_Core_Model_Store_Group */
            $storeGroupId = $store->getGroupId();
        } else {
            $storeGroup = $this->storeGroupFactory->create()->load($storeGroupId);
            /* @var $storeGroup Mage_Core_Model_Store_Group */
            $store = $storeGroup->getDefaultStore();
            /* @var $store Epicor_Comm_Model_Store */
            $storeGroupId = $store->getGroupId();
        }

        $website = $storeGroup->getWebsite();
        /* @var $website Mage_Core_Model_Website */
        $storeGroupIds = $website->getGroupIds();

        $tableJoin = array(
            'store' => $collection->getTable('ecc_list_store_group')
        );

        $tableCols = array(
            'store_group_id' => 'store_group_id'
        );

        $countTable = array(
            'store_group_count' => $collection->getTable('ecc_list_store_group')
        );

        $countCol = array(
            'store_groups_count' => new \Zend_Db_Expr('COUNT(store_group_count.store_group_id)')
        );

        $collection->getSelect()->joinLeft(
            $tableJoin, 'main_table.id = store.list_id AND store.store_group_id = ' . $storeGroupId, $tableCols
        );

        $collection->getSelect()->joinLeft(
            $countTable, 'main_table.id = store_group_count.list_id AND store_group_count.store_group_id IN (' . join(',', $storeGroupIds) . ')', $countCol
        );

        $collection->getSelect()->having('store.store_group_id = ' . $store->getGroupId() . ' OR store_groups_count = 0');

        return $collection;
    }

}
