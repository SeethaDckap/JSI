<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\RoleModel\Erp;

use Epicor\AccessRight\Model\RoleModel\Erp\Condition\Combine;
//use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\AbstractModel;

/**
 * Model Class for Role Erp Account
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 *
 * @method string getAccessRoleId()
 * @method string getErpAccountId()
 * @method string getByErpAccount()
 * @method string getByRole()
 *
 * @method string setAccessRoleId()
 * @method string setErpAccountId()
 * @method string setByErpAccount()
 * @method string setByRole()
 *
 */
class Account extends AbstractModel
{

    protected $applyexpire = false;
    protected $applyids = false;
    /**
     * @var Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $condProdCombineF;

    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $request;

    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * Account constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\AccessRight\Model\RoleModel\Erp\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        $this->request = $request;
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        $this->_init('Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return RoleModel\Erp\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }

    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTable($name)
    {
        return $this->resourceConnection->getTableName($name);
    }

    public function getCustomerRolesTable()
    {
        return $this->resourceConnection->getTableName('ecc_access_role_customer');
    }

    public function getErpRolesTable()
    {
        return $this->resourceConnection->getTableName('ecc_access_role_erp_account');
    }

    public function accessRolesModel()
    {
        $accessRoleModel = $this->getConnection()->select()->from(
            ['main_table' => $this->getTable('ecc_access_role')]
        );
        if ($this->applyids) {

            $accessRoleModel->where('main_table.id IN (?)',$this->applyids);
        }
        if ($this->applyexpire) {
            $date = date('Y-m-d H:i:s');
            $expire = '(((`start_date` <= \'' . $date . '\') OR 
        (`start_date` IS NULL) OR
         (`start_date` = \'0000-00-00 00:00:00\'))) AND (((`end_date` >= \'' . $date . '\') OR
          (`end_date` IS NULL) OR (`end_date` = \'0000-00-00 00:00:00\')))';

            $accessRoleModel->where($expire);
            $accessRoleModel->order('priority DESC');
            $accessRoleModel->order('id DESC');
        }
        return $accessRoleModel;
    }
    
    public function getErpAssociatedContacts($erpAccountId) {
        $erpAccountType = $this->getErpAccountType($erpAccountId);
        $erpAccountIdField = ($erpAccountType == "S") ? "ecc_supplier_erpaccount_id": "ecc_erpaccount_id";
        $customerCollection = $this->customerFactory->create()->getCollection()
                ->addFieldToFilter($erpAccountIdField, $erpAccountId);
        return $customerCollection->getAllIds();
    }

    public function getErpAccountType($erpAccountId)
    {
        $sql = "select account_type from ecc_erp_account where entity_id =" . $erpAccountId;
        $result = $this->getConnection()->fetchAll($sql);
        if ($result) {
            switch ($result[0]['account_type']) {
                case "B2C":
                    return 'C';
                case "B2B":
                    return 'B';
                case "Dealer":
                    return 'R';
                case "Distributor":
                    return 'D';
                case "Supplier":
                    return 'S';
            }
        }
    }

    //get roles which are assigned for specific erp account
    public function getErpSpecificRoles($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role', 'by_erp_account'))
            ->where("main_table.active =1 and ((erp.erp_account_id = " . $erpAccountId . ") or"
                . "(" . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', [$this->getErpAccountType($erpAccountId), 'E', 'N']) . " and main_table.erp_accounts_exclusion = 'Y' and erp.access_role_id is NULL))"
            );
        return $this->getConnection()->fetchAll($collection);
    }

    //get roles which are assigned for specific erp account
    public function getExcludedRolesForSpecificErp($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->join(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role', 'by_erp_account'))
            ->where("main_table.active = 1 and erp.by_role = 1 and "
                . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', [$this->getErpAccountType($erpAccountId), 'E', 'N'])
                . " and main_table.erp_accounts_exclusion = 'Y' and erp.erp_account_id = " . $erpAccountId);

        return $this->getConnection()->fetchAll($collection);
    }

    //get roles which are excluded for other erp accounts
    public function getExcludedRolesForOtherErps($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->join(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role', 'by_erp_account'))
            ->where("main_table.active =1 and erp.by_role = 1 and "
                . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', [$this->getErpAccountType($erpAccountId), 'E', 'N'])
                . " and main_table.erp_accounts_exclusion = 'Y' and erp.erp_account_id != " . $erpAccountId);
        return $this->getConnection()->fetchAll($collection);
    }

    // get the roles which are assigned to the contacts of ERP
    public function getRolesAssignedForContactsOfErp($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->join(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'by_role', 'by_customer'))
            ->where("main_table.active =1 and main_table.customer_exclusion = 'N' and " . $this->getConnection()->quoteInto('cus.customer_id IN(?)', $this->getErpAssociatedContacts($erpAccountId)));
        return $this->getConnection()->fetchAll($collection);
    }

    // get the roles which are not assigned for any erp and any contact
    public function getRolesNotAssignedForAnyErpAndContact($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'erp_by_role' => 'by_role', 'by_erp_account'))
            ->joinLeft(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'customer_by_role' => 'by_role', 'by_customer'))
            ->where("main_table.active =1 and main_table.erp_accounts_exclusion = 'N' and main_table.customer_exclusion = 'N'  and ((erp.access_role_id IS NULL) or (erp.by_role=0 and erp.by_erp_account=1)) and ((cus.access_role_id IS NULL) or (cus.by_role=0 and cus.by_customer=1))");
        return $this->getConnection()->fetchAll($collection);
    }

    // get roles which are assigned to the parent erp account
    public function getValidDefaultGlobalRoles($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role', 'by_erp_account'))
            ->where("main_table.active =1 and ((erp.erp_account_id = " . $erpAccountId . ") or "
                . "(" . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', [$this->getErpAccountType($erpAccountId), 'E', 'N']) . "))"
            );

        return $this->getConnection()->fetchAll($collection);
    }


    // get roles which are assigned to the parent erp account
    public function getCustomFrontEndRoles($erpAccountId)
    {
        $collection = $this->accessRolesModel()
            ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role as by_role_erp', 'by_erp_account'))
            ->joinLeft(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'by_role as by_role_customer', 'by_customer'))
            ->where("main_table.active =1 and " . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', [$this->getErpAccountType($erpAccountId), 'E', 'N']) . "
            and ((main_table.erp_accounts_exclusion = 'Y') or
             (main_table.erp_accounts_exclusion = 'N' and (erp.erp_account_id = " . $erpAccountId . " or erp.access_role_id is NULL)))"
            );
        return $this->getConnection()->fetchAll($collection);
    }
    public function getDefaultGlobalFrontEndRoles()
    {
        $collection = $this->accessRolesModel()
            ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role as by_role_erp', 'by_erp_account'))
            ->joinLeft(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'by_role as by_role_customer', 'by_customer'))
            ->where("main_table.active =1 and " . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', ['C', 'E', 'N']) . "")
            ->group('main_table.id');
        return $this->getConnection()->fetchAll($collection);
    }
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAccessRolesOptionsFrontEnd($erpAccountId, $expired = false)
    {
        if ($expired) {
            $this->applyexpire = true;
        }

        $accessRoleOptions = [];
        if ($erpAccountId) {
            $getParentErpAccountRoles = $this->getCustomFrontEndRoles($erpAccountId);
            $this->_options = [];
            $enable = 1;
            $disable = 0;
            $excludeRole = [];
            $byerp = 0;  foreach ($getParentErpAccountRoles as $roles) {
                if ((($roles['erp_accounts_exclusion'] == 'Y'  && ($roles['by_role_erp'] && $roles['erp_account_id'] == $erpAccountId)) ||
                    ($roles['erp_accounts_exclusion'] == 'N' && ($roles['by_role_erp'] && $roles['erp_account_id'] && $roles['erp_account_id'] != $erpAccountId)))) {
                    if (!isset($excludeRole[$roles['id']])) {
                        $excludeRole[$roles['id']] = $roles['id'];
                    }
                    continue;
                }
            }


            foreach ($getParentErpAccountRoles as $roles) {
                if (isset($excludeRole[$roles['id']])) {
                    continue;
                }
                if ($roles['auto_assign']) {
                    $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
                }
                if ($roles['by_erp_account']) {
                    $byerp = 1;
                    $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
                }

            }
            $accessRoleOptions = $this->unique_multidim_array($this->_options, 'role_id');


        }
        return $accessRoleOptions;
    }
    public function getDefaultGlobalAccessRolesOptions($expired = false,$ids=false)
    {
        if ($expired) {
            $this->applyexpire = true;
        }
        if ($ids) {
            $this->applyids = $ids;
        }

        $accessRoleOptions = [];
            $getParentErpAccountRoles = $this->getDefaultGlobalFrontEndRoles();
            $this->_options = [];
            $enable = 1;
            $disable = 0;
            $byerp = 0;
            $excludeRole = [];
            foreach ($getParentErpAccountRoles as $roles) {
                if ($roles['by_role_erp'] || $roles['by_role_customer']
                ) {
                    if (!isset($excludeRole[$roles['id']])) {
                        $excludeRole[$roles['id']] = $roles['id'];
                    }
                    continue;
                }
                if ($roles['erp_accounts_conditions']) {
                    $conditions = $this->serializer->unserialize($roles['erp_accounts_conditions']);
                    if (!isset($excludeRole[$roles['id']]) && isset($conditions['conditions'])) {
                        $excludeRole[$roles['id']] = $roles['id'];
                        continue;
                    }
                }
                if ($roles['customer_conditions']) {
                    $conditions = $this->serializer->unserialize($roles['customer_conditions']);
                    if (!isset($excludeRole[$roles['id']]) && isset($conditions['conditions'])) {
                        $excludeRole[$roles['id']] = $roles['id'];
                        continue;
                    }
                }

            foreach ($getParentErpAccountRoles as $roles) {
                if (isset($excludeRole[$roles['id']])) {
                    continue;
                }
                $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);

            }
            $accessRoleOptions = $this->unique_multidim_array($this->_options, 'role_id');


        }
        return $accessRoleOptions;

    }
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAccessRolesOptions($erpAccountId, $expired = false)
    {
        if ($expired) {
            $this->applyexpire = true;
        }

        $getRolesNotAssignedForAnyErpAndContact = $this->getRolesNotAssignedForAnyErpAndContact($erpAccountId);
        $getErpSpecificRoles = $this->getErpSpecificRoles($erpAccountId);
        $getRolesNotAssignedForAnyErpAndContactDiff=$this->array_diff($getRolesNotAssignedForAnyErpAndContact, $getErpSpecificRoles);       
        $getExcludedRolesForSpecificErp = $this->getExcludedRolesForSpecificErp($erpAccountId);
        $getExcludedRolesForOtherErps = $this->getExcludedRolesForOtherErps($erpAccountId);
        $res1 = array_merge($getErpSpecificRoles, $getExcludedRolesForOtherErps);
        $uniqueRes1 = $this->unique_multidim_array($res1, 'id');
        $erpRoles=$this->array_diff($uniqueRes1, $getExcludedRolesForSpecificErp);
        $getRolesAssignedForContactsOfErp = $this->getRolesAssignedForContactsOfErp($erpAccountId);       
        $this->_options = [];
        $enable = 1;
        $disable = 0;
        foreach ($getRolesNotAssignedForAnyErpAndContactDiff as $roles) {
            $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable);
        }
        
        foreach ($erpRoles as $roles) {
            if ($roles['auto_assign']) {
                $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable);
            }
            if ($roles['by_erp_account'] && $roles['erp_account_id'] == $erpAccountId) {
                $this->_options = $this->optionsArray($this->_options, $roles, $enable, $disable);
            }
            if ($roles['by_role'] && !$roles['auto_assign']) {
                $this->_options = $this->optionsArray($this->_options, $roles, $disable, $enable);
            }
        }

        foreach ($getRolesAssignedForContactsOfErp as $roles) {
            if ($roles['auto_assign']) {
                $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable);
            }
            if ($roles['by_customer'] || ($roles['by_role'] && !$roles['auto_assign'])) {
                $this->_options = $this->optionsArray($this->_options, $roles, $disable, $enable);
            }
        }
        $accessRoleOptions = $this->unique_multidim_array($this->_options, 'role_id');
        return $accessRoleOptions;
    }

    public function optionsArray($options, $roles, $byErpAccount, $byRole)
    {
        $options [] = array(
            'role_id' => $roles['id'],
            'label' => $roles['title'],
            'value' => $roles['title'],
            'autoAssign' => $roles['auto_assign'],
            'priority' => $roles['priority'],
            'erp_accounts_conditions' => $roles['erp_accounts_conditions'],
            'customer_conditions' => $roles['customer_conditions'],
            'erp_accounts_exclusion' => $roles['erp_accounts_exclusion'],
            'customer_exclusion' => $roles['customer_exclusion'],
            'by_erp_account' => $byErpAccount,
            'other_roles' => $byRole
        );
        return $options;
    }

    public function array_diff($arr1, $arr2)
    {

        foreach ($arr1 as $key => $value) {
            foreach ($arr2 as $key1 => $value1) {
                if ($value['id'] == $value1['id']) {
                    unset($arr1[$key]);
                    break;
                }
            }
        }
        return $arr1;
    }

    public function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

}
