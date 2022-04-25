<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model;

use Magento\Framework\Model\AbstractModel;
use \Magento\Store\Model\ScopeInterface;

use Epicor\AccessRight\Model\ResourceModel\Rules\CollectionFactory as RulesCollectionFactory;

/**
 * Model Class for AccessRight
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 *
 * @method string getTitle()
 * @method string getStartDate()
 * @method string getEndDate()
 * @method string getErpAccountsConditions()
 * @method string getCustomerConditions()
 * @method string getActive()
 * @method string getErpAccountLinkType()
 * @method string getErpAccountsExclusion()
 * @method string getNotes()
 * @method string getPriority()
 * @method string getCreatedDate()
 * @method string getUpdatedDate()
 * @method string getCustomerExclusion()
 *
 * @method string setTitle()
 * @method string setStartDate()
 * @method string setEndDate()
 * @method string setErpAccountsConditions()
 * @method string setCustomerConditions()
 * @method string setActive()
 * @method string setDefaultCurrency()
 * @method string setErpAccountLinkType()
 * @method string setErpAccountsExclusion()
 * @method string setNotes()
 * @method string setPriority()
 * @method string setCreatedDate()
 * @method string setUpdatedDate()
 * @method string setCustomerExclusion()
 */
class RoleModel extends AbstractModel
{

    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const ACTION_UPDATE = 'update';
    const KEY_CUSTOMERS = 'customer';
    const KEY_ERP_ACCOUNTS = 'erp_accounts';

    const ERP_ACC_LINK_TYPE_B2B = 'B';
    const ERP_ACC_LINK_TYPE_B2C = 'C';
    const ERP_ACC_LINK_TYPE_DELEAR = 'R';
    const ERP_ACC_LINK_TYPE_DISTRIBUTOR = 'D';
    const ERP_ACC_LINK_TYPE_SUPPLIER = 'S';
    const ERP_ACC_LINK_TYPE_CHOSEN = 'E';
    const ERP_ACC_LINK_TYPE_NONE = 'N';

    protected $_noCache = false;
    protected $_cache = array();
    protected $_changes = array();
    protected $_pricing = array();
    protected $_contract = null;
    protected $typeInstance;
    protected $sortedLabels;
    protected $customer;
    protected $operatorsMap = [
        '==' => 'eq',    // is
        '!=' => 'neq',   // is not
        '>=' => 'gteq',  // equals or greater than
        '<=' => 'lteq',  // equals or less than
        '>' => 'gt',    // greater than
        '<' => 'lt',    // less than
        '{}' => 'like',  // contains
        '!{}' => 'nlike', // does not contains
        '()' => 'in',    // is one of
        '!()' => 'nin',   // is not one of
        '<=>' => 'null'
    ];

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $erpaccountCollectionFactory;

    /**
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModelFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    //protected $rolesHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\AccessRight\Model\RoleModel\CustomerFactory
     */
    protected $roleCustomerFactory;

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\Customer\CollectionFactory
     */
    protected $roleCustomerCollectionFactory;

    /**
     * @var \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory
     */
    protected $rolesRoleModelErpAccountFactory;

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory
     */
    protected $rolesResourceRoleModelErpAccountCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory
     */
    protected $rolesResourceRoleModelCollectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $groupCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $allowedResources = null;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory
     */
    protected $rulesCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * Scope config
     *
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchHelper;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManager;

    /**
     * @var string[]
     */
    private $customerType = [
        self::ERP_ACC_LINK_TYPE_B2B         => 'b2b',
        self::ERP_ACC_LINK_TYPE_B2C         => 'b2c',
        self::ERP_ACC_LINK_TYPE_DELEAR      => 'dealer',
        self::ERP_ACC_LINK_TYPE_SUPPLIER    => 'supplier'
    ];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\BranchPickup\Helper\DataFactory $branchHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpaccountCollectionFactory,
        \Epicor\AccessRight\Model\RoleModelFactory $rolesRoleModelFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        RulesCollectionFactory $rulesCollectionFactory,
        // \Epicor\Lists\Helper\Data $rolesHelper,
        \Epicor\AccessRight\Model\RoleModel\CustomerFactory $roleCustomerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\Customer\CollectionFactory $roleCustomerCollectionFactory,
        \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory $rolesRoleModelErpAccountFactory,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory $rolesResourceRoleModelErpAccountCollectionFactory,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory $rolesResourceRoleModelCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\Comm\Model\Context $epicContext,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->branchHelper = $branchHelper;
        $this->erpaccountCollectionFactory = $erpaccountCollectionFactory;
        $this->rolesRoleModelFactory = $rolesRoleModelFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        $this->rolesResourceRoleModelCollectionFactory = $rolesResourceRoleModelCollectionFactory;
        $this->request = $request;
        $this->registry = $registry;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        //$this->listsHelper = $rolesHelper;
        $this->roleCustomerFactory = $roleCustomerFactory;
        $this->roleCustomerCollectionFactory = $roleCustomerCollectionFactory;
        $this->rolesRoleModelErpAccountFactory = $rolesRoleModelErpAccountFactory;
        $this->rolesResourceRoleModelErpAccountCollectionFactory = $rolesResourceRoleModelErpAccountCollectionFactory;
        $this->serializer = $serializer;
        $this->_scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $epicContext->getStoreManager();
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        $this->_init('Epicor\AccessRight\Model\ResourceModel\RoleModel');
    }

    public function afterSave()
    {
        $this->_saveErpAccounts();
        $this->_saveCustomers();
        $this->saveConfig("insert");
        $this->clearCache();

        parent::afterSave();
    }

    /**
     * Process after data delete
     *
     * @return RoleModel|void
     */
    public function afterDelete()
    {
        $this->saveConfig("delete");
        $this->clearCache();

        parent::afterDelete();
    }

    /**
     * @param bool $id
     * @param bool $byRole
     * @return array|mixed
     */
    public function getErpAccounts($id = false, $byRole = true)
    {
        $cacheKey = false;
        if ($id) {
            $this->setId($id);
        } else {
            if ($byRole) {
                $cacheKey = self::KEY_ERP_ACCOUNTS;
                if ($cache = $this->_getCachedData($cacheKey)) {
                    return $cache;
                }
            }
        }

        $collection = $this->erpaccountCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */

        if ($byRole) {
            $collection->getSelect()->join(
                array('erp_account' => $collection->getTable('ecc_access_role_erp_account')),
                'main_table.entity_id = erp_account.erp_account_id AND erp_account.access_role_id = "' . $this->getId() . '" AND erp_account.by_role = "1"',
                array('*')
            );
        } else {
            $collection->getSelect()->join(
                array('erp_account' => $collection->getTable('ecc_access_role_erp_account')),
                'main_table.entity_id = erp_account.erp_account_id AND erp_account.access_role_id = "' . $this->getId() . '"',
                array('*')
            );
        }


        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        if ($cacheKey) {
            $this->_cacheData($cacheKey, $items);
        }


        return $items;
    }

    /**
     * Gets the Role of ERP Accounts, updated with changes
     *
     * @return type
     */
    public function getErpAccountsWithChanges()
    {
        $cacheKey = self::KEY_ERP_ACCOUNTS . '_UPDATED';
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $erpAccounts = $this->getErpAccounts();
        if (isset($this->_changes[self::KEY_ERP_ACCOUNTS])) {
            foreach ($this->_changes[self::KEY_ERP_ACCOUNTS] as $type => $items) {
                if ($type == self::ACTION_ADD) {
                    $collection = $this->erpaccountCollectionFactory->create();
                    /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount */
                    $collection->addFieldToFilter('entity_id', array_keys($items));
                    $erpAccounts = $erpAccounts + $collection->getItems();
                } else {
                    if ($type == self::ACTION_REMOVE) {
                        foreach ($items as $key => $item) {
                            if (isset($erpAccounts[$key])) {
                                unset($erpAccounts[$key]);
                            }
                        }
                    }
                }
            }
        }

        $this->_cacheData($cacheKey, $erpAccounts);
        return $erpAccounts;
    }

    /**
     * Validates if the erp account exists in the Role
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @return bool
     */
    public function isValidForErpAccount($erpAccount)
    {
        $valid = true;

        $erpAccounts = $this->getErpAccounts();
        if (count($erpAccounts) > 0) {
            return isset($erpAccounts[$erpAccount->getId()]);
        }

        switch ($this->getErpAccountLinkType()) {
            case 'B':
                $valid = $erpAccount->getAccountType() == 'B2B';
                break;
            case 'C':
                $valid = $erpAccount->getAccountType() == 'B2C';
                break;
            case 'E':
                $valid = false;
                break;
        }

        return $valid;
    }

    /**
     * Validates  whether the user edit the Role or not
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @return bool
     */
    public function isValidEditForErpAccount($customer, $id = false)
    {
        $isMasterShoper = $customer->getData('ecc_master_shopper');
        if ($isMasterShoper) {
            $erpAccounts = array_keys($this->getErpAccounts($id));
            if (count($erpAccounts) > 1) {
                $valid = false;
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }
        return $valid;
    }

    /**
     * Validates  whether the user edit the customers or not (assigning customers)
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @return bool
     */
    public function isValidEditForCustomers($customer, $id = null, $checkOwnerId = null)
    {
        $isMasterShoper = $customer->getData('ecc_master_shopper');
        if (!$isMasterShoper) {
            $customers = $this->getCustomers($id);
            $customerId = $customer->getId();
            $assignedCustomers = isset($customers[$customer->getId()]) ? $customers[$customer->getId()] : null;
//            if ((count($customers) > 1) || ($customerId != $checkOwnerId) || (empty($assignedCustomers))) {
            if ((count($customers) > 1) || ($customerId != $checkOwnerId)) {
                $valid = false;
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }
        return $valid;
    }


    public function getOwnerIds($id)
    {
        $roleModel = $this->rolesRoleModelFactory->create()->load($id);
        $ownerId = $roleModel->getOwnerId();
        return $ownerId;
    }


    /**
     * Retrives Customers from the Role
     *
     * @param bool $id
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomers($id = false, $byRole = true)
    {
        if ($id) {
            $this->setId($id);
        } else {
            if ($byRole) {
                $cacheKey = self::KEY_CUSTOMERS;
                if ($cache = $this->_getCachedData($cacheKey)) {
                    return $cache;
                }
            }
        }

        $collection = $this->customerCollectionFactory->create();
        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */

        $collection->addAttributeToSelect('ecc_erp_account_type');
        $collection->addAttributeToSelect('ecc_erpaccount_id');
        $collection->addAttributeToSelect('ecc_supplier_erpaccount_id');
        if ($byRole) {
            $collection->getSelect()->join(
                array('customer' => $collection->getTable('ecc_access_role_customer')),
                'e.entity_id = customer.customer_id AND customer.access_role_id = "' . $this->getId() . '" AND customer.by_role = "1"', array('*')
            );
        } else {
            $collection->getSelect()->join(
                array('customer' => $collection->getTable('ecc_access_role_customer')),
                'e.entity_id = customer.customer_id AND customer.access_role_id = "' . $this->getId() . '"', array('*')
            );
        }


        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        if (isset($cacheKey)) {
            $this->_cacheData($cacheKey, $items);
        }

        return $items;
    }

    /**
     * Gets the Role of Customers, updated with changes
     *
     * @return array|\Magento\Framework\DataObject[]|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomersWithChanges()
    {
        $cacheKey = self::KEY_CUSTOMERS . '_UPDATED';
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $customers = $this->getCustomers();
        if (isset($this->_changes[self::KEY_CUSTOMERS])) {
            foreach ($this->_changes[self::KEY_CUSTOMERS] as $type => $items) {
                if ($type == self::ACTION_ADD) {
                    $collection = $this->customerCollectionFactory->create();
                    /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
                    $collection->addAttributeToSelect('ecc_erp_account_type', 'left');
                    $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
                    $collection->addAttributeToSelect('ecc_supplier_erpaccount_id', 'left');
                    $collection->addFieldToFilter('entity_id', array_keys($items));
                    $customers = $customers + $collection->getItems();
                } else {
                    if ($type == self::ACTION_REMOVE) {
                        foreach ($items as $key => $item) {
                            if (isset($customers[$key])) {
                                unset($customers[$key]);
                            }
                        }
                    }
                }
            }
        }

        $this->_cacheData($cacheKey, $customers);
        return $customers;
    }

    /**
     * Validates if the customer exists in the Role
     *
     * @param $customer
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isValidForCustomer($customer)
    {
        $customers = $this->getCustomers();
        return isset($customers[$customer->getId()]) || count($customers) == 0;
    }

    /**
     * Adds customers to the Role
     *
     * @param array|int|object $customers
     */
    public function addCustomers($customers)
    {
        $this->_changes($customers, self::KEY_CUSTOMERS, self::ACTION_ADD);
    }

    /**
     * Removes customers from the Role
     *
     * @param array|int|object $customers
     */
    public function removeCustomers($customers)
    {
        $this->_changes($customers, self::KEY_CUSTOMERS, self::ACTION_REMOVE);
    }


    /**
     * Adds erp accounts to the Role
     *
     * @param array|int|object $erpAccounts
     */
    public function addErpAccounts($erpAccounts)
    {
        $this->_changes($erpAccounts, self::KEY_ERP_ACCOUNTS, self::ACTION_ADD);
    }

    /**
     * Removes erp accounts from the Role
     *
     * @param array|int|object $erpAccounts
     */
    public function removeErpAccounts($erpAccounts)
    {
        $this->_changes($erpAccounts, self::KEY_ERP_ACCOUNTS, self::ACTION_REMOVE);
    }

    /**
     * Returns the value for noCache
     *
     * @param bool
     */
    public function getNoCache()
    {
        return $this->_noCache;
    }

    /**
     * Sets the value for noCache
     *
     * @param bool
     */
    public function setNoCache($noCache = true)
    {
        $this->_noCache = $noCache;
    }

    /**
     * Clears cache for an specific key if given or all if not
     *
     * @param string $key
     */
    public function clearCache($key = null)
    {
        if ($key) {
            unset($this->_cache[$key]);
        } else {
            $this->_cache = array();
        }
    }

    /**
     * Returns cached data if avaible, else returns false
     *
     * @param string $key
     * @return mixed
     */
    protected function _getCachedData($key)
    {
        if (!$this->getNoCache() && isset($this->_cache[$key])) {
            return $this->_cache[$key];
        }

        return false;
    }

    /**
     * Caches data with given key
     *
     * @param string $key
     * @param mixed $data
     */
    protected function _cacheData($key, $data)
    {
        $this->_cache[$key] = $data;
    }

    /**
     * Registers label changes
     *
     * @param array|object|int $items
     * @param string $section
     * @param string $action
     */
    protected function _labelChanges($items, $section, $action)
    {
        if (!is_array($items)) {
            $items = array($items);
        }

        foreach ($items as $item) {
            $this->_changes[$section][$action][] = $item;
        }

        $this->_hasDataChanges = true;
    }

    /**
     * Registers changes
     *
     * @param array|object|int $items
     * @param string $section
     * @param string $action
     */
    protected function _changes($items, $section, $action, $idField = false)
    {
        $verify = ($action == self::ACTION_ADD ? self::ACTION_REMOVE : self::ACTION_ADD);

        if (!is_array($items)) {
            $items = array($items);
        }
        foreach ($items as $item) {
            $itemId = (is_object($item) ? ($idField ? $item->getData($idField) : $item->getId()) : $item);
            $this->_changes[$section][$action][$itemId] = $item;
            if (isset($this->_changes[$section][$verify][$itemId])) {
                unset($this->_changes[$section][$verify][$itemId]);
            }
        }
        $this->_hasDataChanges = true;
    }

    /*  Finding differences in two multidimensional arrays
     */

    public function array_diff_assoc_recursive($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                    if ($new_diff != false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!isset($array2[$key]) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }


    protected function _saveCustomers($byRole = true)
    {
        if (isset($this->_changes[self::KEY_CUSTOMERS])) {
            $existingCustomers = $this->getCustomers(false, false);

            if (isset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD] as $customerId => $customer) {
                    if (!array_key_exists($customerId, $existingCustomers)) {
                        $customer = $this->roleCustomerFactory->create();
                        /* @var $customer Epicor/AccessRight/Model/RoleModel/Customer */
                        $customer->setCustomerId($customerId);
                        $customer->setAccessRoleId($this->getId());
                        if ($byRole) {
                            $customer->setByRole(1);
                        } else {
                            $customer->setByRole(0);
                        }
                        $customer->save();
                    } elseif (array_key_exists($customerId,
                            $existingCustomers) && isset($existingCustomers[$customerId])) {

                        $existRow = $existingCustomers[$customerId];
                        /* @var \Epicor\Dealerconnect\Model\Customer\Erpaccount $existRow */

                        if ($existRow->getData("by_customer") == "1" && $existRow->getData("id")) {
                            $customer = $this->roleCustomerFactory->create();
                            /* @var $erpAccount \Epicor\AccessRight\Model\RoleModel\Erp\Account */
                            $customer = $customer->load($existRow->getData("id"));
                            $customer->setByRole(1);
                            $customer->save();
                        }
                    }
                }

                unset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD]);
            }

            if (isset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE])) {
                $customerIds = array();
                foreach ($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE] as $customerId => $customer) {
                    if (array_key_exists($customerId, $existingCustomers)) {
                        $customerIds[] = $customerId;
                    }
                }

                if (count($customerIds) > 0) {
                    $customersCollection = $this->roleCustomerCollectionFactory->create();
                    /* @var $customersCollection Epicor\AccessRight\Model\Resource\RoleModel\Customer\Collection */
                    $customersCollection->addFieldtoFilter('access_role_id', $this->getId());
                    $customersCollection->addFieldtoFilter('customer_id', array('in' => $customerIds));
                    $customersCollection->addFieldtoFilter('by_role', "1");
                    foreach ($customersCollection->getItems() as $item) {
                        $item->delete();
//                        if ($item->getByCustomer() == 1) {
//                            $item->setByRole(0);
//                            $item->save();
//                        } else {
//                            $item->delete();
//                        }
                    }
                }

                unset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE]);
            }
        }
    }

    /**
     * Save ERP Data
     *
     * @param bool $byRole
     */
    protected function _saveErpAccounts($byRole = true)
    {
        if (isset($this->_changes[self::KEY_ERP_ACCOUNTS])) {
            $existingErpAccounts = $this->getErpAccounts(false, false);

            if (isset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD] as $erpAccountId => $erpAccount) {
                    if (!array_key_exists($erpAccountId, $existingErpAccounts)) {
                        //$erpAccount = $this->rolesRoleModelErpAccountFactory->create();
                        $erpAccount = $this->rolesRoleModelErpAccountFactory->create();
                        /* @var $erpAccount \Epicor\AccessRight\Model\RoleModel\Erp\Account */
                        $erpAccount->setErpAccountId($erpAccountId);
                        $erpAccount->setAccessRoleId($this->getId());
                        $erpAccount->setAccessRoleId($this->getId());
                        if ($byRole) { // save BY Role
                            //$erpAccount->setByErpAccount(0);
                            $erpAccount->setByRole(1);
                        } else {
                            //$erpAccount->setByErpAccount(1);
                            $erpAccount->setByRole(0);
                        }
                        $erpAccount->save();
                    } elseif (array_key_exists($erpAccountId,
                            $existingErpAccounts) && isset($existingErpAccounts[$erpAccountId])) {
                        $existRow = $existingErpAccounts[$erpAccountId];
                        /* @var \Epicor\Dealerconnect\Model\Customer\Erpaccount $existRow */
                        if ($existRow->getData("by_erp_account") == "1" && $existRow->getData("id")) {
                            $erpAccount = $this->rolesRoleModelErpAccountFactory->create();
                            /* @var $erpAccount \Epicor\AccessRight\Model\RoleModel\Erp\Account */
                            $erpAccount = $erpAccount->load($existRow->getData("id"));
                            $erpAccount->setByRole(1);
                            $erpAccount->save();
                        }
                    }
                    $getAllIds[] = $erpAccountId;
                }

                unset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD]);
            }

            if (isset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE])) {
                $erpAccountIds = array();
                foreach ($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE] as $erpAccountId => $erpAccount) {
                    if (array_key_exists($erpAccountId, $existingErpAccounts)) {
                        $erpAccountIds[] = $erpAccountId;
                    }
                }

                if (count($erpAccountIds) > 0) {
                    $erpAccountsCollection = $this->rolesResourceRoleModelErpAccountCollectionFactory->create();
                    /* @var $erpAccountsCollection Epicor\AccessRight\Model\Resource\RoleModel\Erp\Account_Collection */
                    $erpAccountsCollection->addFieldtoFilter('access_role_id', $this->getId());
                    $erpAccountsCollection->addFieldtoFilter('erp_account_id', array('in' => $erpAccountIds));
                    $erpAccountsCollection->addFieldtoFilter('by_role', "1");

                    foreach ($erpAccountsCollection->getItems() as $item) {
                        $item->delete();
//                        if ($item->getByErpAccount() == 1) {
//                            $item->setByRole(0);
//                            $item->save();
//                        } else {
//                            $item->delete();
//                        }

                    }
                }

                unset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]);
            }
        }
    }

    /**
     * Returns whether the Role is active
     *
     * @return boolean
     */
    public function isActive()
    {
        if ($this->getActive() == 0) {
            return false;
        }


        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $dateModel = $this->dateTimeDateTimeFactory->create();
        $currentTimeStamp = $dateModel->timestamp(time());
        $startTimeStamp = $dateModel->timestamp(strtotime($startDate));
        $endTimeStamp = $dateModel->timestamp(strtotime($endDate));

        if ($endDate && $endTimeStamp < $currentTimeStamp) {
            return false;
        } else {
            if ($startDate && $startTimeStamp > $currentTimeStamp) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @param null $frontEnd
     * @return array|bool
     */
    public function validate($frontEnd = null)
    {
        $errors = array();

        $title = $this->getTitle();
        if (empty($title)) {
            $errors[] = __('Title must not be empty');
        }
        //check if excluded erp account indicator not set and no erpaccounts selected (should only be triggered for backend)
        if ($this->request->getParam('selected_erpaccounts')) {
            $linksOfAcctsSelected = $this->request->getParam('links');
            $accountsSelected = $linksOfAcctsSelected['erpaccounts'];
            if (!$accountsSelected && ($this->getErpAccountLinkType() != 'N') && ($this->getErpAccountsExclusion() == 'N')) {
                $errors[] = __("Cannot update Role. Either one or more ERP Accounts must be selected for inclusion or 'Exclude Selected Erp Accounts' should be ticked");
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getConditions() as $condition) {
            $condition->setJsFormObject($form);
        }
        return $this;
    }

    /**
     * Checks to see if orphans need to be removed
     *
     * @param string $flag
     * @return boolean|array
     */
    public function orphanCheck($flag = '')
    {
        $warn = ($flag == 'warn');
        // check link type has changed

        if (
            $this->getOrigData('erp_account_link_type') == $this->getErpAccountLinkType() &&
            $this->getOrigData('erp_accounts_exclusion') == $this->getErpAccountsExclusion() &&
            isset($this->_changes[self::KEY_ERP_ACCOUNTS]) == false
        ) {
            return false;
        }

        // check any ERP accounts to be removed
        $orphanErpAccounts = $this->removeOrphanErpAccounts($warn);
        $orphanCustomers = $this->removeOrphanCustomers($warn);

        if ($orphanErpAccounts == false && $orphanCustomers == false) {
            return false;
        }

        if ($warn) {
            $messageArray = array(__('Changes made to this Role will result in the deletion of : '));

            $orphanErpAccountsCount = 0;
            if (empty($orphanErpAccounts) == false) {
                $orphanErpAccountsCount = count($orphanErpAccounts);
                $messageArray[] = __('ERP Accounts : %1', $orphanErpAccountsCount);
            }

            $orphanCustomersCount = 0;
            if (empty($orphanCustomers) == false) {
                $orphanCustomersCount = count($orphanCustomers);
                $messageArray[] = __('Customers : %1', $orphanCustomersCount);
            }

            return array(
                'message' => implode("<br>", $messageArray),
                'type' => 'success',
                'erpaccounts' => count($this->getErpAccountsWithChanges()) - $orphanErpAccountsCount
            );
        }

        return true;
    }

    /**
     * Looks at the ERP Accounts to determine if they need to be
     * @param boolean $warn
     * @return boolean|array
     */
    protected function removeOrphanErpAccounts($warn)
    {
        $erpAccounts = $this->getErpAccountsWithChanges();

        if (empty($erpAccounts)) {
            return false;
        }

        $removeType = $this->getAccountTypeToRemove();

        if (in_array('None', $removeType)) {
            return false;
        }

        $removeErpAccounts = array();

        foreach ($erpAccounts as $key => $erpAccount) {
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            if (
                in_array($erpAccount->getAccountType(), $removeType) ||
                in_array('All', $removeType)
            ) {
                $removeErpAccounts[$key] = $erpAccount;
            }
        }

        if (empty($removeErpAccounts)) {
            return false;
        }

        if ($warn == false) {
            $this->removeErpAccounts($removeErpAccounts);
        }
        return $removeErpAccounts;
    }

    /**
     * Removes orphan customers
     * @param boolean $warn
     * @return boolean|array
     */
    protected function removeOrphanCustomers($warn)
    {
        $customers = $this->getCustomersWithChanges();

        if (empty($customers)) {
            return false;
        }

        $removeType = $this->getAccountTypeToRemove();
        $removeCustomers = array();
        foreach ($customers as $key => $customer) {
            /* @var $customer Epicor_Comm_Model_Customer */
            if ($this->shouldRemoveCustomer($customer, $removeType)) {
                $removeCustomers[$key] = $customer;
            }
        }

        if (empty($removeCustomers)) {
            return false;
        }

        if ($warn == false) {
            $this->removeCustomers($removeCustomers);
        }
        return $removeCustomers;
    }

    /**
     * Works out if a customer should be removed from this Role due to data integrity
     *
     * @param \Epicor\Comm\Model\Customer $customer
     * @param string $removeType
     *
     * @return boolean
     */
    protected function shouldRemoveCustomer($customer, $removeType)
    {
        // sales reps should never be removed
        if ($customer->isSalesRep()) {
            return false;
        }

        $customerEdpAccountType = [];
        if ($customer->getEccErpAccountType() == "supplier") {
            if ($customer->getSupplierErpAccount()) {
                $customerEdpAccountType = $customer->getSupplierErpAccount()->getAccountType() ?: [];
            }
            // simple check: does the customer match the correct link type
            if (count(array_intersect([$customerEdpAccountType], $removeType)) && $customer->isCustomer(false)) {
                return true;
            }
        } else {
            if ($customer->getCustomerErpAccount()) {
                $customerEdpAccountType = $customer->getCustomerErpAccount()->getAccountType() ?: [];
            }
            // simple check: does the customer match the correct link type
            if ((count(array_intersect([$customerEdpAccountType], $removeType)) && $customer->isCustomer(false)) ||
                (in_array('B2C', $removeType) && $customer->isGuest(false))
            ) {
                return true;
            }
        }

        $excluded = $this->getErpAccountsExclusion();
        $erpAccounts = $this->getErpAccountsWithChanges();
        $hasErpAccounts = (empty($erpAccounts) == false);

        // if B2C, exclusion set to no, then remove guests with no erp accounts
        if (
            $customer->isGuest(false) &&
            $customer->getEccErpaccountId() == false &&
            $this->getErpAccountLinkType() == self::ERP_ACC_LINK_TYPE_B2C &&
            $excluded == 'N' &&
            $hasErpAccounts
        ) {
            return true;
        }

        // if excluded, customer erp account should not be selected
        // if inclusion, customer erp account should be selected
        if ($customer->getEccErpAccountType() == "supplier") {
            $accountSelected = isset($erpAccounts[$customer->getEccSupplierErpaccountId()]);
        } else {
            $accountSelected = isset($erpAccounts[$customer->getEccErpaccountId()]);
        }
        if ($this->getErpAccountLinkType() != self::ERP_ACC_LINK_TYPE_NONE &&
            (($excluded == 'N' && $accountSelected == false) || ($excluded == 'Y' && $accountSelected == true))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the type of terp accounts / customers to be removed basedon link type
     *
     * @return string
     */
    protected function getAccountTypeToRemove()
    {
        $removeType = [];
        $linkType = $this->getErpAccountLinkType();
        switch ($linkType) {
            case self::ERP_ACC_LINK_TYPE_B2C:
                $removeType = ['B2B', 'Dealer', 'Distributor', 'Supplier'];
                break;
            case self::ERP_ACC_LINK_TYPE_B2B:
                $removeType = ['B2C', 'Dealer', 'Distributor', 'Supplier'];
                break;
            case self::ERP_ACC_LINK_TYPE_DELEAR:
                $removeType = ['B2B', 'B2C', 'Distributor', 'Supplier'];
                break;
            case self::ERP_ACC_LINK_TYPE_DISTRIBUTOR:
                $removeType = ['B2B', 'B2C', 'Dealer', 'Supplier'];
                break;
            case self::ERP_ACC_LINK_TYPE_SUPPLIER:
                $removeType = ['B2B', 'B2C', 'Dealer', 'Distributor'];
                break;
            case self::ERP_ACC_LINK_TYPE_NONE:
                $removeType = ['All'];
                break;
            default:
                $removeType = ['None'];
                break;
        }

        return $removeType;
    }

    /**
     * Get a list of available resource using user role id
     *
     * @param string $roleId
     * @return string[]
     */
    public function getAllowedResourcesByRole($roleId)
    {

        if ($this->allowedResources === null) {
            $this->allowedResources = [];
            $rulesCollection = $this->rulesCollectionFactory->create();
            $rulesCollection->getByRoles($roleId)->load();
            /** @var \Magento\Authorization\Model\Rules $ruleItem */
            foreach ($rulesCollection->getItems() as $ruleItem) {
                $resourceId = $ruleItem->getResourceId();
                $this->allowedResources[] = $resourceId;
            }
        }
        return $this->allowedResources;
    }

    /**
     * @return \Epicor\AccessRight\Model\RoleModel\Erp\Account
     */
    public function getErpAccountModel()
    {
        return $this->rolesRoleModelErpAccountFactory->create();
    }

    /**
     * @return \Epicor\AccessRight\Model\RoleModel\Customer
     */
    public function getCustomerModel()
    {
        return $this->roleCustomerFactory->create();
    }


    /**
     * Get the Roles of particular ERP Account Link type
     *
     * @param string $erpAccLinkType
     * @return array
     */
    public function getRoles($erpAccLinkType)
    {
        return $this->_getResource()->getRolesCollection($erpAccLinkType);
    }


    /**
     * Get the Role based on priorty from list of roles ids
     *
     * @param array $roleids
     * @return int
     */

    public function getRoleFromRoles($rolesids = false)
    {
        return $this->_getResource()->getRoleFromRoles($rolesids);
    }

    public function getRoleAppliedByErp($customer, $erpAccountId, $ids = false)
    {
        $id = false;
        $validroles = $this->getRolesByErp($customer, $erpAccountId, $ids);
        if (!empty($validroles)) {
            $id = $this->getRoleFromRoles($validroles);
        }
        return $id;
    }

    public function getRoleAppliedByDefaultGlobal($ids = false)
    {
        $roles = $this->getErpAccountModel()->getDefaultGlobalAccessRolesOptions(true, $ids);
        $roleids = [];
        foreach ($roles as $role) {
            $roleids[] = $role['role_id'];
        }
        return $roleids;
    }

    public function getErpConditions($roles, $customerId, $erpAccountId)
    {
        $roleids = [];
        foreach ($roles as $role) {
            if ($erpAccountId) {
                if ($role['erp_accounts_conditions'] && $role['erp_accounts_conditions'] !== null) {
                    $conditions = $this->serializer->unserialize($role['erp_accounts_conditions']);
                    if (isset($conditions['conditions'])) {
                        $conditions = $conditions['conditions'];
                        $collection = $this->applyErpConditions($conditions, $erpAccountId);
                        if (!$collection) {
                            continue;
                        }
                    }
                }
            }
            if ($customerId) {
                if ($role['customer_conditions']) {
                    $conditions = $this->serializer->unserialize($role['customer_conditions']);
                    if (isset($conditions['conditions'])) {
                        $conditions = $conditions['conditions'];
                        $collection = $this->applyCustomerConditions($conditions, $customerId, $erpAccountId);
                        if (!$collection) {
                            continue;
                        }
                    }
                }
            }
            $roleids[] = $role['role_id'];
        }
        return $roleids;
    }

    public function initMappingData()
    {

        $mappingModel = $this->resourceConnection->getConnection()->select()->from(
            ['main_table' => $this->resourceConnection->getTableName('ecc_access_right_attributemapping')]
        );
        $mappingdatas = $this->resourceConnection->getConnection()->fetchAll($mappingModel);

        foreach ($mappingdatas as $mappingdata) {
            if ($mappingdata['erp_code']) {
                $this->erpaccountAttributeMapping[$mappingdata['erp_code']] = [
                    'customer_code' => $mappingdata['customer_code'],
                    'config' => $mappingdata['config']
                ];
                if ($mappingdata['customer_code']) {
                    $this->erpaccountCustomerAttributeMapping[$mappingdata['erp_code']] = $mappingdata['customer_code'];
                }
            }
            if ($mappingdata['customer_code']) {
                $this->customerAttributeMapping[$mappingdata['customer_code']] = [
                    'erp_code' => $mappingdata['erp_code'],
                    'config' => $mappingdata['config']
                ];
            }
        }

    }

    public function getRolesByErp($customer, $erpAccountId, $ids = false)
    {
        $this->initMappingData();
        if ($ids) {
            $getType = $ids;
        } else {
            $getType = 'erp_account';
        }
        if ($customer) {
            $customerId = $customer->getId();
            $this->customer = $customer;
            $roles = $this->getCustomerModel()->getAccessRolesOptionsFrontEnd($customerId, $erpAccountId, true, $getType);
        } else {
            $roles = $this->getErpAccountModel()->getAccessRolesOptionsFrontEnd($erpAccountId, true);
        }
        $roleids = $this->getErpConditions($roles, $customerId, $erpAccountId);
        return $roleids;
    }


    public function getRoleAppliedByCustomer($customer, $erpAccountId)
    {
        $this->initMappingData();
        $id = false;
        $validroles = $this->getRolesByCustomer($customer, $erpAccountId);
        if (!empty($validroles)) {
            $id = $this->getRoleFromRoles($validroles);
        }
        return $id;
    }

    public function checkConditions($operator, $value1, $value2)
    {
        switch ($operator) {
            case "==":
                if (!($value1 == $value2)) {
                    return false;
                }
                break;
            case "!=":
                if (!($value1 != $value2)) {
                    return false;
                }
                break;
            case "<=>":
                if (!(is_null($value1))) {
                    return false;
                }
                break;
        }
        return true;
    }

    public function erpAccountAttributeMappingConditions($field, $data, $conditionvalue, $operator)
    {
        $defultDropdownValue = [0, 1];
        $customervalue = $this->_scopeConfig->getValue($this->erpaccountAttributeMapping[$field]['config'],
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!($this->checkConditions($operator, $customervalue, $conditionvalue))) {
            return false;
        }
        return true;
    }

    public function customerAttributeMappingConditions($field, $data, $conditionvalue, $operator, $erpAccountId)
    {
        $defultDropdownValue = [0, 1];
        $erpfieldmapping = $this->customerAttributeMapping[$field]['erp_code'];
        $type = strtolower($this->customer->getCustomerErpAccount()->getAccountType());
        if ($this->customer->getEccErpAccountType() == "supplier") {
            $type = 'supplier';
        }
        $type = $erpAccountId ? $type : 'b2c';

        $customervalue = $this->_scopeConfig->getValue($this->customerAttributeMapping[$field]['config'],
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($field == 'ecc_is_branch_pickup_allowed' && !in_array($customervalue, $defultDropdownValue)) {
            if ($type == strtolower($this->branchHelper->create()->checkShopperType())) {
                $customervalue = 1;
            }
        }

        if ($erpAccountId) {
            if ($data[$erpfieldmapping] != 2) {
                $customervalue = $data[$erpfieldmapping];
            }
        }
        if ($field == 'ecc_login_mode_type') {
            if ($type != 'dealer') {
                $customervalue = 'shopper';
            }
        }

        if (!($this->checkConditions($operator, $customervalue, $conditionvalue))) {
            return false;
        }
        return true;
    }

    public function applyErpConditions($conditions, $erpAccountId)
    {
        if ($this->customer) {
            $customerId = $this->customer->getId();
            $customrcollection = $this->customerCollectionFactory->create();

            $erpaccountTable = $customrcollection->getTable('ecc_erp_account');

            $customrcollection->addFieldToFilter('entity_id', ['eq' => $customerId]);
            $customrcollection->addAttributeToSelect('ecc_erpaccount_id', 'left');
            $erpattribute = [];
            foreach ($this->erpaccountCustomerAttributeMapping as $key => $attribute) {
                $customrcollection->addAttributeToSelect($attribute, 'left');
                $erpattribute[] = $key;
            }
            $customrcollection->joinTable(array('erp' => $erpaccountTable), 'entity_id=ecc_erpaccount_id',
                $erpattribute, null, 'left');
            $data = $customrcollection->getFirstItem();
        }
        $collection = $this->erpaccountCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['eq' => $erpAccountId]);
        foreach ($conditions as $condition) {
            $canapply = true;
            $canapplyerp = true;
            $field = $condition['attribute'];
            $operator = $condition['operator'];
            $value = $condition['value'];
            if ($this->operatorsMap[$operator] == 'null') {
                $value = true;
            }
            //validate customer attributes
            if ($this->customer && isset($this->erpaccountCustomerAttributeMapping[$field])) {
                $field = $this->erpaccountCustomerAttributeMapping[$field];
                if ($data[$field] == 2) {
                    if (!($this->customerAttributeMappingConditions($field, $data, $value, $operator, $erpAccountId))) {
                        return false;
                    }
                    $canapply = false;
                }
                if ($canapply) {
                    $customrcollection->addFieldToFilter($field, [$this->operatorsMap[$operator] => $value]);
                }
                if ($customrcollection->getSize() == 0) {
                    return false;
                }
                $canapplyerp = false;
            } else if (!$this->customer || isset($this->erpaccountAttributeMapping[$field])) {
                $erpdata = $collection->getFirstItem();
                if ($erpdata[$field] == 2) {
                    if (!($this->erpAccountAttributeMappingConditions($field, $erpdata, $value, $operator))) {
                        return false;
                    }
                    $canapplyerp = false;
                }
            }
            if ($canapplyerp) {
                $collection->addFieldToFilter($field, [$this->operatorsMap[$operator] => $value]);
            }
        }
        return $collection->getSize();
    }

    public function applyCustomerConditions($conditions, $customerId, $erpAccountId)
    {
        $collection = $this->customerCollectionFactory->create();

        $erpaccountTable = $collection->getTable('ecc_erp_account');

        $collection->addFieldToFilter('entity_id', ['eq' => $customerId]);
        $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
        $erpattribute = [];
        foreach ($this->customerAttributeMapping as $key => $attribute) {
            $collection->addAttributeToSelect($key, 'left');
            $erpattribute[] = $attribute['erp_code'];
        }
        $collection->joinTable(array('erp' => $erpaccountTable), 'entity_id=ecc_erpaccount_id',
            $erpattribute, null, 'left');
        $data = $collection->getFirstItem();
        foreach ($conditions as $condition) {
            $field = $condition['attribute'];
            $operator = $condition['operator'];
            $value = $condition['value'];

            if ($this->operatorsMap[$operator] == 'null') {
                $value = true;
            }
            $canapply = true;
            if (isset($this->customerAttributeMapping[$field])) {
                if ($data[$field] == 2) {
                    if (!($this->customerAttributeMappingConditions($field, $data, $value, $operator, $erpAccountId))) {
                        return false;
                    }
                    $canapply = false;
                }
            }
            if ($canapply) {
                $collection->addFieldToFilter($field, [$this->operatorsMap[$operator] => $value]);
            }
        }
        return $collection->getSize();
    }

    public function getRolesByCustomer($customer, $erpAccountId)
    {
        $customerId = $customer->getId();
        $this->customer = $customer;
        $roles = $this->getCustomerModel()->getAccessRolesOptionsFrontEnd($customerId, $erpAccountId, true);
        $roleids = [];
        foreach ($roles as $role) {
            if ($role['autoAssign'] || $role['by_customer'] || $role['by_erp_account']) {
                if ($role['customer_conditions']) {
                    $conditions = $this->serializer->unserialize($role['customer_conditions']);
                    if (isset($conditions['conditions'])) {
                        $conditions = $conditions['conditions'];
                        $collection = $this->applyCustomerConditions($conditions, $customerId, $erpAccountId);
                        if (!$collection) {
                            continue;
                        }
                    }
                }
                if ($conditions = $role['erp_accounts_conditions']) {
                    $conditions = $this->serializer->unserialize($role['erp_accounts_conditions']);
                    if (isset($conditions['conditions'])) {
                        $conditions = $conditions['conditions'];
                        $collection = $this->applyErpConditions($conditions, $erpAccountId);
                        if (!$collection) {
                            continue;
                        }
                    }
                }
                $roleids[] = $role['role_id'];
            }
        }
        return $roleids;
    }

    /**
     * Adding/Removing the auto assign roles to store configuration
     *
     * @param $action
     * @return void
     */
    private function saveConfig($action)
    {
        if (!$this->getAutoAssign()) {
            return;
        }
        switch ($this->getErpAccountLinkType()) {
            case self::ERP_ACC_LINK_TYPE_CHOSEN:
            case self::ERP_ACC_LINK_TYPE_NONE:
            case null:
                foreach ($this->customerType as $type) {
                    $this->saveRoleConfig($type, $action);
                }
                break;
            case self::ERP_ACC_LINK_TYPE_B2B:
                $this->saveRoleConfig(
                    $this->customerType[self::ERP_ACC_LINK_TYPE_B2B],
                    $action
                );
                break;
            case self::ERP_ACC_LINK_TYPE_B2C:
                $this->saveRoleConfig(
                    $this->customerType[self::ERP_ACC_LINK_TYPE_B2C],
                    $action
                );
                break;
            case self::ERP_ACC_LINK_TYPE_DELEAR:
                $this->saveRoleConfig(
                    $this->customerType[self::ERP_ACC_LINK_TYPE_DELEAR],
                    $action
                );
                break;
            case self::ERP_ACC_LINK_TYPE_SUPPLIER:
                $this->saveRoleConfig(
                    $this->customerType[self::ERP_ACC_LINK_TYPE_SUPPLIER],
                    $action
                );
                break;
        }
        return;
    }

    /**
     * @param $customerType
     * @param $action
     * @return void
     */
    private function saveRoleConfig($customerType, $action)
    {
        $id = $this->getId();
        $config = 'epicor_access_control/access_role_settings/'.$customerType.'_access_role';
        $roles = $this->_scopeConfig->getValue($config, ScopeInterface::SCOPE_STORE);
        $save = false;
        switch ($action) {
            case "insert":
                if (is_null($roles) || $roles == "") {
                    $roles = $id;
                    $save = true;
                } else {
                    $value = explode(',', $roles);
                    if (!in_array($id, $value)) {
                        $value[] = $id;
                        $roles = implode(',', $value);
                        $save = true;
                    }
                }
                break;
            case "delete":
                if (!is_null($roles) && $roles != "") {
                    $value = explode(',', $roles);
                    if (($key = array_search($id, $value)) !== false) {
                        unset($value[$key]);
                        $roles = implode(',', $value);
                        $save = true;
                    }
                }
                break;
        }

        if ($save) {
            $this->resourceConfig
                ->saveConfig(
                    $config,
                    $roles,
                    'default',
                    0
                );
            $this->storeManager->getStore()->resetConfig();
        }
        return;
    }
}
