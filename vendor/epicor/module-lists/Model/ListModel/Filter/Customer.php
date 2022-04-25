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
class Customer extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    ) {
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
    }
    /**
     * Adds Customer filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filter($collection)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        if (!($customerId = $this->getCustomerId())) {
            $session = $this->customerSession;
            /* @var $session Mage_Customer_Model_Session */

            $customer = $session->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            $customerId = $customer->getId();

            $erpAccount = $helper->getErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        } else {
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            /* @var $customer Epicor_Comm_Model_Customer */

            $erpAccount = $helper->getErpAccountInfo($customer->getEccErpAccountId());
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        }
        
        $customerIds = array();
        if ($erpAccount) {
            $customers = $erpAccount->getCustomers();
            $customerIds = $customers->getAllIds();
        }

        if (empty($customerIds)) {
            $customerIds = array(0);
        }

        $tableJoin = array(
            'customer' => $collection->getTable('ecc_list_customer')
        );

        $tableCols = array(
            'customer_id' => 'customer_id'
        );

        $countErpTable = array(
            'customer_count_erp' => $collection->getTable('ecc_list_customer')
        );

        $countErpCol = array(
            'customers_count_erp' => new \Zend_Db_Expr('COUNT(customer_count_erp.customer_id)')
        );

        $countAllTable = array(
            'customer_count_all' => $collection->getTable('ecc_list_customer')
        );

        $countAllCol = array(
            'customers_count_all' => new \Zend_Db_Expr('COUNT(customer_count_all.customer_id)')
        );

        $having = '';
        if ($customerId) {
            $collection->getSelect()->joinLeft(
                $tableJoin,
                'main_table.id = customer.list_id AND customer.customer_id = ' . $customerId,
                $tableCols
            );
            $having .= ' ((customer.customer_id = ' . $customerId . ' AND customer_exclusion = "N")';
            $having .= ' OR (customer.customer_id IS NULL AND customer_exclusion = "Y")) OR ';
        }

        if ($customer->isGuest() && !$customer->getEccErpaccountId()) {
            $collection->getSelect()->joinLeft(
                $countAllTable, 'main_table.id = customer_count_all.list_id', $countAllCol
            );
            $having .= 'customers_count_all = 0';
        } else {
            $collection->getSelect()->joinLeft(
                $countErpTable, 'main_table.id = customer_count_erp.list_id  AND customer_count_erp.customer_id IN (' . join(',', $customerIds) . ')', $countErpCol
            );

            $collection->getSelect()->joinLeft(
                $countAllTable, 'main_table.id = customer_count_all.list_id', $countAllCol
            );

            $having .= ' (customers_count_erp = 0 AND erp_account_link_type != "N")';
            $having .= ' OR (customers_count_all = 0 AND erp_account_link_type = "N")';
        }

        $collection->getSelect()->having($having);

        return $collection;
    }

}
