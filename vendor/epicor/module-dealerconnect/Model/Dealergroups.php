<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model;


/**
 * Model Class for Dealer Groups
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 * 
 * @method string getType()
 * @method string getTitle()
 * @method string getLabel()
 * @method string getStartDate()
 * @method string getEndDate()
 * @method string getActive()
 * @method string getSource()
 * @method string getDefaultCurrency()
 * @method string getErpAccountLinkType()
 * @method string getErpAccountsExclusion()
 * @method string getIsDummy()
 * @method string getNotes()
 * @method string getPriority()
 * @method string getCreatedDate()
 * @method string getUpdatedDate()
 * 
 * @method string setErpCode()
 * @method string setType()
 * @method string setTitle()
 * @method string setLabel()
 * @method string setStartDate()
 * @method string setEndDate()
 * @method string setActive()
 * @method string setSource()
 * @method string setDefaultCurrency()
 * @method string setErpAccountLinkType()
 * @method string setErpAccountsExclusion()
 * @method string setIsDummy()
 * @method string setNotes()
 * @method string setPriority()
 * @method string setCreatedDate()
 * @method string setUpdatedDate()
 */

class Dealergroups extends \Epicor\Database\Model\Dealergroups
{

    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const ACTION_UPDATE = 'update';
    const KEY_ERP_ACCOUNTS = 'erp_accounts';
    const ERP_ACC_LINK_TYPE_B2B = 'B';
    const ERP_ACC_LINK_TYPE_B2C = 'C';
    const ERP_ACC_LINK_TYPE_CHOSEN = 'E';
    const ERP_ACC_LINK_TYPE_NONE = 'N';

    protected $_noCache = false;
    protected $_cache = array();
    protected $_changes = array();
    protected $_pricing = array();
    protected $typeInstance;
    protected $sortedLabels;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;





    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;



    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;





    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;


    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\Erp\AccountFactory
     */
    protected $dealerGroupModelErpAccountFactory;

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\Erp\Account\CollectionFactory
     */
    protected $dealerGroupResourceModelErpAccountCollectionFactory;

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory
     */
    protected $dealerGroupResourceModelCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;



    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteCollectionFactory;


    protected $groupCollectionFactory;




    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,

        \Magento\Framework\App\ResourceConnection $resourceConnection,

        \Epicor\Dealerconnect\Model\Dealergroups\Erp\AccountFactory $dealerGroupModelErpAccountFactory,
        \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\Erp\Account\CollectionFactory $dealerGroupResourceModelErpAccountCollectionFactory,
        \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory $dealerGroupResourceModelCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,

        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,

        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->commMessagingHelper = $commMessagingHelper;

        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;

        $this->resourceConnection = $resourceConnection;

        $this->dealerGroupModelErpAccountFactory = $dealerGroupModelErpAccountFactory;
        $this->dealerGroupResourceModelErpAccountCollectionFactory = $dealerGroupResourceModelErpAccountCollectionFactory;
        $this->dealerGroupResourceModelCollectionFactory = $dealerGroupResourceModelCollectionFactory;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;

        $this->request = $request;

        $this->registry = $registry;
        
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
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\Dealergroups');
    }

    /**
     * Gets the code
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    public function afterSave()
    {
        $this->_saveErpAccounts();
        $this->clearCache();

        parent::afterSave();
    }

    /**
     * Gets ERP Override for the Group
     *
     * @return array
     */
    public function getErpOverride()
    {
        $override = $this->getData('erp_override');
        return empty($override) ? array() : unserialize($override);
    }

    /**
     * Sets ERP Override for the Group
     *
     * @return \Epicor\Dealerconnect\Model\Dealergroups
     */
    public function setErpOverride($override)
    {
        $this->setData('erp_override', serialize($override));
        return $this;
    }

    /**
     * Retrives Erp Accounts
     * 
     * @return array $items
     */
    public function getErpAccounts($id = false)
    {
        $cacheKey = false;
        if ($id) {
            $this->setId($id);
        } else {
            $cacheKey = self::KEY_ERP_ACCOUNTS;
            if ($cache = $this->_getCachedData($cacheKey)) {
                return $cache;
            }
        }

        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_ResourceCustomer_Erpaccount */
        $collection->getSelect()->join(
            array('group' => $collection->getTable('ecc_dealer_groups_accounts')),
            'main_table.entity_id = group.dealer_account_id AND group.group_id = "' . $this->getId() . '"',
            array()
        );

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
     * Retrives Valid ERP Accounts
     *
     * @return array $items
     */
    public function getValidErpAccounts($id = false , $erpAccountIds, $exclusion = N)
    {
        $cacheKey = false;
        if ($id) {
            $this->setId($id);
        } else {
            $cacheKey = self::KEY_ERP_ACCOUNTS;
            if ($cache = $this->_getCachedData($cacheKey)) {
                return $cache;
            }
        }

        $erpAccountsCollection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $erpAccountsCollection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */
        $erpAccountsCollection->addFieldToFilter('account_type', 'Dealer');
        $condition = $exclusion == 'Y' ? 'nin' : 'in';
        $erpAccountIdFilter = empty($erpAccountIds) ? array(0) : $erpAccountIds;
        $erpAccountsCollection->addFieldToFilter('entity_id', array($condition => $erpAccountIdFilter));

        $items = array();
        foreach ($erpAccountsCollection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        if ($cacheKey) {
            $this->_cacheData($cacheKey, $items);
        }


        return $items;
    }

    /**
     * Gets the lists of Dealer Accounts, updated with changes
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
                    $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
                    /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount */
                    $collection->addFieldToFilter('entity_id', array_keys($items));
                    $erpAccounts = $erpAccounts + $collection->getItems();
                } else if ($type == self::ACTION_REMOVE) {
                    foreach ($items as $key => $item) {
                        if (isset($erpAccounts[$key])) {
                            unset($erpAccounts[$key]);
                        }
                    }
                }
            }
        }

        $this->_cacheData($cacheKey, $erpAccounts);
        return $erpAccounts;
    }

    /**
     * Validates if the erp account exists in the Group
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
     * Validates  whether the user edit the Group or not
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
     * Adds erp accounts to the Group
     * 
     * @param array|int|object $erpAccounts
     */
    public function addErpAccounts($erpAccounts)
    {
        $this->_changes($erpAccounts, self::KEY_ERP_ACCOUNTS, self::ACTION_ADD);
    }

    /**
     * Removes erp accounts from the Group
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
                    if ($new_diff != FALSE) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!isset($array2[$key]) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }


    protected function _saveErpAccounts()
    {
        if (isset($this->_changes[self::KEY_ERP_ACCOUNTS])) {
            $existingErpAccounts = $this->getErpAccounts();
            if (isset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD])) {

                foreach ($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD] as $erpAccountId => $erpAccount) {
                    if (!array_key_exists($erpAccountId, $existingErpAccounts)) {
                        $erpAccount = $this->dealerGroupModelErpAccountFactory->create();
                        /* @var $erpAccount Epicor_Dealerconnect_Model_Dealergroups_Erp_Account */
                        $erpAccount->setDealerAccountId($erpAccountId);
                        $erpAccount->setGroupId($this->getId());
                        $erpAccount->save();

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
                    $erpAccountsCollection = $this->dealerGroupResourceModelErpAccountCollectionFactory->create();
                    /* @var $erpAccountsCollection Epicor_Dealerconnect_Model_Dealergroups_Erp_Account_Collection */
                    $erpAccountsCollection->addFieldtoFilter('group_id', $this->getId());
                    $erpAccountsCollection->addFieldtoFilter('dealer_account_id', array('in' => $erpAccountIds));

                    foreach ($erpAccountsCollection->getItems() as $item) {
                        $item->delete();
                    }
                }

                unset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]);
            }
        }
    }


    /**
     * Returns whether the Group is active
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
        } else if ($startDate && $startTimeStamp > $currentTimeStamp) {
            return false;
        } else {
            return true;
        }
    }


    public function validate($frontEnd = null)
    {
        $errors = array();

        $code = $this->getCode();
        if (empty($code)) {
            $errors[] = __('Code must not be empty');
        }

        $title = $this->getTitle();
        if (empty($title)) {
            $errors[] = __('Title must not be empty');
        }
        $text = 'update';
        if ($this->isObjectNew()) {
            $text = 'create';
            if (empty($code) == false) {
                $col = $this->dealerGroupResourceModelCollectionFactory->create();
                /* @var $Col Epicor_Dealerconnect_Model_Resource_DealerGroups_Collection */
                $col->addFieldToFilter('code', $code);
                if ($col->count() > 0) {
                    $errors[] = !empty($frontEnd) ? __('Group Code "' . $code . '"" is already taken by another Dealer Group. Please enter a different  code.') : __('Group Code must be unique');
                }
            }
        }

        //check if excluded erp account indicator set and no erpaccounts selected (should only be triggered for backend)
        $linksOfAcctsSelected = $this->request->getParam('links');
        if ($linksOfAcctsSelected ) {
            $accountsSelected = $linksOfAcctsSelected['erpaccounts'];
            if (!$accountsSelected) {
                $errors[] = __("Cannot ".$text." Dealer Group. Either one or more Dealer Accounts must be selected");
            }
        }elseif($this->isObjectNew()){
            $errors[] = __("Cannot create Dealer Group. Either one or more Dealer Accounts must be selected");
        }

        return empty($errors) ? true : $errors;
    }

    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getConditions() as $condition) {
            $condition->setJsFormObject($form);
        }
        return $this;
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

        $removeType = 'All';

        $removeErpAccounts = array();

        foreach ($erpAccounts as $key => $erpAccount) {
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            if (
                $erpAccount->getAccountType() == $removeType ||
                $removeType == 'All'
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
}
