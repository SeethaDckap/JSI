<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Filter;

use \Epicor\Comm\Model\Customer\Erpaccount as CustomerErpAccount;

/**
 * Model Class for List Filtering
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Erpaccount extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
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

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory
    ) {
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
    }

    /**
     * @return bool
     */
    private function isOnlySupplierLicense()
    {
        $licenseTypes = $this->commHelper->getValidLicenseTypes();
        if (is_array($licenseTypes) && count($licenseTypes) === 1) {
            $singleLicense = $licenseTypes[0] ?? '';
            return $singleLicense === CustomerErpAccount::CUSTOMER_TYPE_Supplier;
        }

        return false;
    }

    /**
     * Adds ERP Account filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return bool
     */
    public function filter($collection)
    {
        $type = 'customer';
        if ($this->isOnlySupplierLicense()) {
            $type = 'supplier';
        }

        $erpAccountId = $this->getErpAccountId();

        /* @var $helper \Epicor\Comm\Helper\Data */
        $helper = $this->commHelper;

        $erpAccount = $helper->getErpAccountInfo($erpAccountId, $type);

        if (!$erpAccount->getId()) {
            return false;
        }

        if (!($customerId = $this->getCustomerId())) {
            $session = $this->customerSession;
            /* @var $session Mage_Customer_Model_Session */

            $customer = $session->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
        } else {
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            /* @var $customer Epicor_Comm_Model_Customer */
        }


        $having = 'erp_account_link_type = "N"';
        if (!$erpAccount instanceof \Epicor\Comm\Model\Customer\Erpaccount || ($customer->isGuest() && !$customer->getEccErpaccountId())) {
            $countTable = array(
                'erp_account_count' => $collection->getTable('ecc_list_erp_account')
            );

            $countCol = array(
                'erp_accounts_count' => new \Zend_Db_Expr('COUNT(erp_account_count.erp_account_id)')
            );

            $erpAccounts = $this->commResourceCustomerErpaccountCollectionFactory->create();
            $erpAccounts->addFieldToFilter('account_type', 'B2C');
            $erpAccountIds = $erpAccounts->getAllIds();
            if(!in_array($erpAccount->getId(), $erpAccountIds)) {
                $erpAccountIds[] = $erpAccount->getId();
            }
            $having .= ' OR (erp_account_link_type IN ("E", "C") AND erp_accounts_exclusion = "Y"';
            if ($erpAccountIds) {
                $collection->getSelect()->joinLeft(
                    $countTable, 'erp_account_count.list_id = main_table.id AND erp_account_count.erp_account_id IN (' . join(',', $erpAccountIds) . ')', $countCol
                );
                $having .= ' AND erp_accounts_count = 0';
            }

            $having .= ')';
        } else {
            $tableJoin = array(
                'lea' => $collection->getTable('ecc_list_erp_account')
            );

            $tableCols = array(
                'erp_account_id' => 'erp_account_id',
            );

            $collection->getSelect()->joinLeft(
                $tableJoin, 'lea.list_id = main_table.id AND lea.erp_account_id = ' . $erpAccount->getId(), $tableCols
            );
            $typeFilter = ($erpAccount->getAccountType() == 'B2B') ? 'B' : 'C';

            $having .= ' OR ('
                . ' erp_account_link_type IN ("E", "' . $typeFilter . '")'
                . ' AND ('
                . '(erp_accounts_exclusion = "N" AND erp_account_id = "' . $erpAccount->getId() . '")'
                . ' OR (erp_accounts_exclusion = "Y" AND erp_account_id IS NULL)'
                . ')'
                . ')';
        }

        $collection->getSelect()->having($having);
        return $collection;
    }

}
