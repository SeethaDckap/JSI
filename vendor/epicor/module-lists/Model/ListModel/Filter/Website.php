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
class Website extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    /**
     * Adds Website filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filter($collection)
    {
        if (!($websiteId = $this->getStoreGroupId())) {
            $store = $this->storeManager->getStore();
            /* @var $store Epicor_Comm_Model_Store */
            $websiteId = $store->getWebsiteId();
        }

        $tableJoin = array(
            'website' => $collection->getTable('ecc_list_website')
        );

        $tableCols = array(
            'website_id' => 'website_id'
        );

        $countTable = array(
            'website_count' => $collection->getTable('ecc_list_website')
        );

        $countCol = array(
            'websites_count' => new \Zend_Db_Expr('COUNT(website_count.website_id)')
        );

        $collection->getSelect()->joinLeft(
            $tableJoin, 'main_table.id = website.list_id AND website.website_id = ' . $websiteId, $tableCols
        );

        $collection->getSelect()->joinLeft(
            $countTable, 'main_table.id = website_count.list_id', $countCol
        );

        $collection->getSelect()->having('website.website_id = ' . $websiteId . ' OR websites_count = 0');

        return $collection;
    }

}
