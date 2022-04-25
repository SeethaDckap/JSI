<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Import extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    /**
     * @var array
     */
    private $linkTypes = array(
        'B' => 'B2B',
        'C' => 'B2C',
        'R' => 'Dealer',
        'D' => 'Distributor',
        'S' => 'Supplier',
        'E' => 'Chosen ERP',
        'N' => 'No Specific Link',
    );

    /**
     * @var array
     */
    protected $selectedIds = [];

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $erpAccountCollection;

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory
     */
    protected $erpAccountCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Import constructor.
     * @param \Epicor\AccessRight\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpAccountCollection
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory $erpAccountCollectionFactory
     */
    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpAccountCollection,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory $erpAccountCollectionFactory
    )
    {
        $this->erpAccountCollection = $erpAccountCollection;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->erpAccountCollectionFactory = $erpAccountCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Ajax Import ERP|Customer
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $role = $this->loadEntity();
        /* @var \Epicor\AccessRight\Model\RoleModel $role */

        $type = $this->getRequest()->getParam('type', "erpaccounts");
        $errors = array();

        if (!empty($_FILES['import']['tmp_name'])) {
            $errors = $this->_import($role, $_FILES['import']['tmp_name'], $type);
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['products' => $this->selectedIds, 'errors' => $errors]);
    }

    /**
     * import Csv Data
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param $file
     * @param string $type
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _import($role, $file, $type = "erpaccounts")
    {
        $errors = array();

        $fileContents = fopen($file, "rb");
        if ($type == 'erpaccounts') { //Erp Accout
            $this->_importErpAccout($fileContents, $errors);
        } elseif ($type == 'customer') { //Customers
            $this->_importCustomer($role, $fileContents, $errors);
        }

        fclose($fileContents);

        if (count($errors) > 0) {
            return $errors;
        } else {
            return false;
        }
    }

    /**
     * Import ERP Account
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param $fileContents
     * @param array $errors
     * @return string|string[]|null
     */
    private function _importErpAccout(&$fileContents, &$errors)
    {
        $next = null;
        $invalidLinkType = array();
        $errorAccounts = array();
        $accounts = array();

        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);

            if (!$next) {
                $property = preg_replace('/\s+/', '', strtolower($data[0]));
            } else {
                $property = preg_replace("/\r|\n/", '', $data[0]);
            }

            if (in_array($property, array('erpshortcode'))) {
                $next = $property;
            } elseif (!empty($property)) {
                if ($next) {
                    $accounts[] = $property;
                    $errorAccounts[] = $property;
                }
            }
        } while (!feof($fileContents) && $next);

        if (!$next) {
            $errors['errors'][] = __('"erpshortcode" column not found. ');
            $this->messageManager->addError(__('"ERP Short Code" column not found. '));
            return $next;
        }

        if (empty($accounts)) {
            $errors['errors'][] = __('"ERP Short Code" column cannot be blank.');
            $this->messageManager->addError(__('"ERP Short Code" column cannot be blank.'));
            return $next;
        }

        if (count($accounts) > 0) {
            $collection = $this->erpAccountCollection->create();
            /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */

            $collection->addFieldToFilter('short_code', array('in' => $accounts));
            $erpAccounts = $collection->getItems();
            foreach ($erpAccounts as $account) {
                $ERPLinkType = $this->backendAuthSession->getLinkTypeValue();

                if ($this->linkTypes[$ERPLinkType] != $account->getaccountType() && $ERPLinkType != "E") { // invalid account
                    $key = array_search(strtolower($account->getShortCode()), array_map('strtolower', $accounts));
                    array_push($invalidLinkType, $accounts[$key]);
                    $collection->removeItemByKey($account->getId());
                } else { // selected ERP account
                    $this->selectedIds[] = $account->getId();
                }

                // not exist
                if (($key = array_search(strtolower($account->getShortCode()), array_map('strtolower', $errorAccounts))) !== false) {
                    unset($errorAccounts[$key]);
                }
            }
        }

        if (count($errorAccounts) > 0) {
            $this->messageManager->addError(
                __(
                    'Accounts(s) %1 do not exist and were not assigned to Role',
                    implode(', ', $errorAccounts)
                )
            );
            $errors['warnings'][] = __(
                'Accounts(s) %1 do not exist and were not assigned to Role',
                join(', ', $errorAccounts)
            );

        }

        $invalidLinkType = array_diff_assoc($invalidLinkType, $errorAccounts);
        if (count($invalidLinkType) > 0) {
            $this->messageManager->addWarning(
                __(
                    'ERP Account %1 was not added, as it does not match the ERP Account Link Type',
                    join(', ', $invalidLinkType)
                )
            );
            $errors['error'][] = __(
                'ERP Account %1 was not added, as it does not match the ERP Account Link Type',
                join(', ', $invalidLinkType)
            );
        }

        return $next;
    }

    /**
     * Import Customer
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param $fileContents
     * @param array $errors
     * @return string|string[]|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _importCustomer($role, &$fileContents, &$errors)
    {
        $next = null;
        $invalidLinkType = array();
        $errorCustomer = array();
        $customersData = array();

        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);

            if (!$next) { // header
                $property = preg_replace('/\s+/', '', strtolower($data[0]));
            } else { // data
                $property = preg_replace("/\r|\n/", '', $data[0]);
            }

            if (in_array($property, array('customeremailaddress'))) {
                $next = $property;
            } elseif (!empty($property)) {
                if ($next) {
                    $customersData[] = $property;
                    $errorCustomer[] = $property;
                }
            }
        } while (!feof($fileContents) && $next);

        if (!$next) {
            $errors['errors'][] = __('"erpshortcode" column not found. ');
            $this->messageManager->addError(__('"Customer Email Address" column not found. '));
            return $next;
        }

        if (empty($customersData)) {
            $errors['errors'][] = __('"Customer Email Address" column cannot be blank.');
            $this->messageManager->addError(__('"Customer Email Address" column cannot be blank.'));
            return $next;
        }

        if (count($customersData) > 0) {
            $collection = $this->_prepareCollectionForCustomer($role, $customersData);
            /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */

            $customers = $collection->getItems();
            foreach ($customers as $customer) {
                // set valid customer
                if (($key = array_search(strtolower($customer->getEmail()), array_map('strtolower', $errorCustomer))) !== false) {
                    $this->selectedIds[] = $customer->getId();
                    unset($errorCustomer[$key]);
                }
            }

            // invalid account for warning
            if (count($errorCustomer) > 0) {
                $collection = $this->customerCollectionFactory->create();
                /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */

                $collection->addAttributeToSelect('email');
                $filters = array(
                    array('attribute' => 'email', array('in' => $errorCustomer))
                );
                $collection->addFieldToFilter($filters);
                $errorCustomerItems = $collection->getItems();
                foreach ($errorCustomerItems as $errorCustomerItem) {
                    if (($key = array_search(strtolower($errorCustomerItem->getEmail()), array_map('strtolower', $errorCustomer))) !== false) {
                        array_push($invalidLinkType, $errorCustomer[$key]);
                        unset($errorCustomer[$key]);
                    }
                }
            }
        }

        if (count($errorCustomer) > 0) {
            $this->messageManager->addError(
                __(
                    'Customer(s) %1 do not exist and were not assigned to role',
                    implode(', ', $errorCustomer)
                )
            );
            $errors['warnings'][] = __(
                'Customer(s) %1 do not exist and were not assigned to role',
                join(', ', $errorCustomer)
            );

        }

        $invalidLinkType = array_diff_assoc($invalidLinkType, $errorCustomer);
        if (count($invalidLinkType) > 0) {
            $this->messageManager->addWarning(
                __(
                    'Customers %1 was not added to role.',
                    join(', ', $invalidLinkType
                    )
                )
            );
            $errors['error'][] = __('Customers %1 was not added to role.', join(', ', $invalidLinkType));
        }
        return $next;
    }

    /**
     * Reference Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab:: _prepareCollection()
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param array $emailData
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollectionForCustomer($role, $emailData = array())
    {
        $erpLinkType = $this->getRequest()->getParam('erp_account_link_type');
        $exclusion = $this->getRequest()->getParam('erp_accounts_exclusion');
        $erpAccountIds = $this->getRequest()->getParam('erp_accounts');

        if (!$erpLinkType) {
            $erpAccountIds = array_keys($role->getErpAccounts());
            $erpLinkType = $role->getErpAccountLinkType() ?: 'N';
            $exclusion = $role->getErpAccountsExclusion() ?: 'N';
        }

        $allowedCustomerTypes = array('salesrep', 'customer');
        $typeNull = false;
        $types = false;
        if ($erpLinkType == "N") {
            // When the link type of the Role is “No specific link” then the customers tab should show “guests”
            array_push($allowedCustomerTypes, 'guest');
            array_push($allowedCustomerTypes, 'supplier');
        } elseif ($erpLinkType == "B") {
            $types = array('B2B');
        } elseif ($erpLinkType == "R") {
            $types = array('Dealer');
        } elseif ($erpLinkType == "D") {
            $types = array('Distributor');
        } elseif ($erpLinkType == "S") {
            if (($key = array_search("customer", $allowedCustomerTypes)) !== false) {
                unset($allowedCustomerTypes[$key]);
            }
            array_push($allowedCustomerTypes, 'supplier');
        } elseif ($erpLinkType == "C") {
            $types = array('B2C');
            if ($exclusion == 'Y' && empty($erpAccountIds)) {
                array_push($allowedCustomerTypes, 'guest');
                $typeNull = true;
            }
        } elseif ($erpLinkType == "E") {
            array_push($allowedCustomerTypes, 'supplier');
        }

        $erpAccountIds = $this->getValidErpAccountIds($erpAccountIds, $erpLinkType, $exclusion);

        $collection = $this->customerCollectionFactory->create();
        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $erpaccountTable = $collection->getTable('ecc_erp_account');
        $customerErpLinkTable = $collection->getTable('ecc_customer_erp_account');
        $salesRepTable = $collection->getTable('ecc_salesrep_account');
        $collection->addNameToSelect();
        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_erp_account_type', 'left');
        $collection->addAttributeToSelect('ecc_sales_rep_account_id', 'left');
        $collection->addAttributeToSelect('ecc_supplier_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_master_shopper');

        $collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=ecc_erpaccount_id', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code', 'account_type' => 'account_type'), null, 'left');
        $collection->joinTable(array('sr' => $salesRepTable), 'id=ecc_sales_rep_account_id', array('sales_rep_id' => 'sales_rep_id'), null, 'left');

        $collection->joinTable(
            ['erp' => $customerErpLinkTable],
            'customer_id=entity_id',
            ['erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code']
        );
        $collection->addExpressionAttributeToSelect('joined_short_code', "IF(sr.sales_rep_id IS NOT NULL, sr.sales_rep_id, IF(cc.short_code IS NOT NULL, cc.short_code, IF(cc.short_code IS NOT NULL, cc.short_code, '')))", 'ecc_erpaccount_id');
        $collection->addAttributeToFilter('ecc_erp_account_type', array('in' => $allowedCustomerTypes));

        if ($erpLinkType != 'N' && $typeNull == false) {
            if (empty($erpAccountIds)) {
                $erpAccountIds = array(0);
            }
            $collection->getSelect()->where('(`erp`.`erp_account_id` IN (?) OR `at_ecc_sales_rep_account_id`.`value` != 0 OR `at_ecc_supplier_erpaccount_id`.`value` IN (?))', $erpAccountIds);

        }

        // If ERP Link Type is B2B it should only Role B2B customers and sales reps. (Similarly if Link Type is B2C - it should only Role B2C customers and sales reps)
        if ($types) {
            $filters = array(
                array('attribute' => 'account_type', $types),
                array('attribute' => 'ecc_sales_rep_account_id', 'neq' => '0')
            );

            if ($typeNull) {
                $filters[] = array('attribute' => 'account_type', array('null' => true));
            }
            $collection->addFieldToFilter($filters);
        }

        // CSV Email Data Filter
        if (count($emailData) > 0) {
            $filters = array(
                array('attribute' => 'email', array('in' => $emailData))
            );
            $collection->addFieldToFilter($filters);
        }

        return $collection;
    }

    /**
     * Gets an array of valid erp account ids based
     * on the flags of the Role and the ids passed
     *
     * @param array $erpAccountIds
     * @param string $erpLinkType
     * @param string $exclusion
     * @return array
     */
    protected function getValidErpAccountIds($erpAccountIds, $erpLinkType, $exclusion)
    {
        if ($erpLinkType == 'N') {
            return array();
        }

        $erpAccountsCollection = $this->erpAccountCollection->create();
        /* @var $erpAccountsCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
        $type = false;
        switch ($erpLinkType) {
            case "B":
            case "C":
            case "R":
            case "D":
            case "S":
                $type = $this->linkTypes[$erpLinkType];
                break;
        }
        if ($type) {
            $erpAccountsCollection->addFieldToFilter('account_type', $type);
        }

        $condition = $exclusion == 'Y' ? 'nin' : 'in';
        $erpAccountIdFilter = empty($erpAccountIds) ? array(0) : $erpAccountIds;
        $erpAccountsCollection->addFieldToFilter('entity_id', array($condition => $erpAccountIdFilter));

        return $erpAccountsCollection->getAllIds();
    }
}
