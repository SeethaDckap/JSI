<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\ResourceModel;


/**
 * Customer  list Collections
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class SidebarLists
{
    /**
     * Limit of lists in side bar
     */
    const SIDEBAR_LISTS_LIMIT = 5;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    private $commHelper;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    private $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Constructor
     *
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->commHelper = $commHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->customerSession = $customerSession;
    }


    /*
     * get Lists Collection
     *
     * @return collection
     */
    public function getCollection()
    {
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $customerAccountType = $this->customerSession->getCustomer()->getEccErpAccountType();
        $collection = $this->listsResourceListModelCollectionFactory->create();
        // dont display lists which are excluded  
        $collection->addFieldToFilter('erp_accounts_exclusion', array('eq' => 'N'));
        $collection->getSelect()->joinLeft(array(
            'lea' => $collection->getTable('ecc_list_erp_account')
        ), 'lea.list_id=main_table.id', array(
            'lea.erp_account_id'
        ));
        $erpAccountId = $erpAccount->getId();
        $this->_masterShopperList($collection, $erpAccountId);
        if ($customerAccountType != "guest") {
            $collection->addFieldToFilter('lea.erp_account_id', $erpAccount->getId());
        }
        //only sees lists with a list type of pre-defined or favourite or product group
        $collection->addFieldToFilter('type', array('in' => array('Pl', 'Fa', 'Pg')));
        $collection->getSelect()->where(new \Zend_Db_Expr("(source IN('customer')) OR (source = 'web' AND settings NOT LIKE '%M%' )"));

        $collection->getSelect()->order('id DESC');
        return $collection;
    }


    /*
     * master Shopper List
     *
     * @return collection
     */
    protected function _masterShopperList($collection, $erpAccountId)
    {
        $customerSessionvar = $this->customerSession->getCustomer();
        $isMasterShopper = $customerSessionvar->getData('ecc_master_shopper');
        $customerId = $customerSessionvar->getData('entity_id');
        //A master shopper only sees (and can only amend and delete) lists with a list type of pre-defined or favourite 
        //and which are assigned to his ERP Account and no other ERP Account. 
        $eTableName = $collection->getTable('ecc_list_erp_account');

        if ($isMasterShopper) {
            $subquery = new \Zend_Db_Expr('SELECT lea.list_id FROM ' . $eTableName . ' AS lea WHERE lea.list_id = main_table.id AND lea.erp_account_id <> "' . $erpAccountId . '"');
            $collection->addFieldToFilter('lea.list_id', array('nin' => array($subquery)));
        } else {
            // A non master shopper/Registered shopper/Registered Guest 
            // only sees (and can only amend and delete) lists with a list type of pre-defined or favourite 
            // and which are assigned to his ERP Account and customer and no other ERP Account / customer 

            $cTableName = $collection->getTable('ecc_list_customer');
            $tableJoin = array('customer' => $cTableName);
            $tableCols = array('customer.customer_id' => 'customer_id');
            $collection->getSelect()->joinLeft($tableJoin, 'main_table.id = customer.list_id', $tableCols);

            //retrieve customers other than the current customer for a list
            // but remove any lists that have the current customer on it

            $customerSub = new \Zend_Db_Expr('SELECT lc.list_id FROM ' . $cTableName . ' AS lc WHERE lc.list_id = main_table.id AND lc.customer_id <> "' . $customerId . '" and lc.list_id <> ('
                . 'SELECT ld.list_id FROM ' . $cTableName . '  AS ld WHERE ld.list_id = main_table.id AND ld.customer_id = "' . $customerId . '")');


            // remove from collection any lists returned from above condition 
            $collection->addFieldToFilter('lea.list_id', array('nin' => array($customerSub)));

            $subqueryErp = new \Zend_Db_Expr('SELECT lea.list_id FROM ' . $eTableName . ' AS lea WHERE lea.list_id = main_table.id AND lea.erp_account_id <> "' . $erpAccountId . '"');
            $collection->addFieldToFilter('lea.list_id', array('nin' => array($subqueryErp)));
        }

        return $collection;
    }
}
