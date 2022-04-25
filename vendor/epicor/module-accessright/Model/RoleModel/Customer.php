<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\RoleModel;

use Epicor\AccessRight\Model\RoleModel\Customer\Condition\Combine;
//use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\AbstractModel;

/**
 * Model Class for Role Customer
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 *
 * @method string getAccessRoleId()
 * @method string getCustomerId()
 * @method string getByCustomer()
 * @method string getByRole()
 *
 * @method string setAccessRoleId()
 * @method string setCustomerId()
 * @method string setByCustomer()
 * @method string setByRole()
 */
class Customer extends AbstractModel
{

    protected $applyexpire = false;
    protected $applyids = false;
    /**
     * @var Customer\Condition\CombineFactory
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

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\AccessRight\Model\RoleModel\Customer\Condition\CombineFactory $condCombineFactory,
        \Magento\Rule\Model\Condition\CombineFactory $condProdCombineF,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
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
        $this->_init('Epicor\AccessRight\Model\ResourceModel\RoleModel\Customer');
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

            $accessRoleModel->where('main_table.id IN (?)', $this->applyids);
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

    // get roles which are assigned to the parent erp account
    public function getCustomFrontEndRoles($customerId, $erpAccountId)
    {
        if ($erpAccountId === "M") {
            $collection = $this->accessRolesModel()
                ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role as by_role_erp', 'by_erp_account'))
                ->joinLeft(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'by_role as by_role_customer', 'by_customer'))
                ->where("main_table.active =1 and " . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', ['N']) . "
                and ((main_table.customer_exclusion = 'Y' and (cus.customer_id = " . $customerId . " and ((cus.access_role_id IS NULL) or (cus.by_role = 0 and cus.by_customer = 1)))) or
             (main_table.customer_exclusion = 'N' and (cus.customer_id = " . $customerId . " or ((cus.access_role_id is NULL) or (cus.by_role=0 and cus.by_customer=1)))))"
                );
        } else if ($erpAccountId) {
            $collection = $this->accessRolesModel()
                ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role as by_role_erp', 'by_erp_account'))
                ->joinLeft(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'by_role as by_role_customer', 'by_customer'))
                ->where("main_table.active =1 and " . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', [$this->getErpAccountType($erpAccountId), 'E', 'N']) . "
            and ((main_table.erp_accounts_exclusion = 'Y') or
             (main_table.erp_accounts_exclusion = 'N' and (erp.erp_account_id = " . $erpAccountId . " or ((erp.access_role_id is NULL) or (erp.by_role=0 and erp.by_erp_account=1)))))
            and ((main_table.customer_exclusion = 'Y') or
             (main_table.customer_exclusion = 'N' and (cus.customer_id = " . $customerId . " or ((cus.access_role_id is NULL) or (cus.by_role=0 and cus.by_customer=1)))))"
                );
        } else {
            $collection = $this->accessRolesModel()
                ->joinLeft(array('erp' => $this->getErpRolesTable()), 'main_table.id = erp.access_role_id', array('erp_account_id', 'by_role as by_role_erp', 'by_erp_account'))
                ->joinLeft(array('cus' => $this->getCustomerRolesTable()), 'main_table.id = cus.access_role_id', array('customer_id', 'by_role as by_role_customer', 'by_customer'))
                ->where("main_table.active =1 and " . $this->getConnection()->quoteInto('main_table.erp_account_link_type IN(?)', ['C', 'E', 'N']) . "
                and ((main_table.customer_exclusion = 'Y') or
             (main_table.customer_exclusion = 'N' and (cus.customer_id = " . $customerId . " or ((cus.access_role_id is NULL) or (cus.by_role=0 and cus.by_customer=1)))))"
                );

        }

        return $this->getConnection()->fetchAll($collection);
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAccessRolesOptionsFrontEnd($customerId, $erpAccountId, $expired = false, $bycustomer = 'customer')
    {
        if ($expired) {
            $this->applyexpire = true;
        }
        if (is_array($bycustomer)) {
            $this->applyids = $bycustomer;
        }
        $accessRoleOptions = [];
        if ($customerId) {
            $getParentErpAccountRoles = $this->getCustomFrontEndRoles($customerId, $erpAccountId);
            $excludeRole = [];
            if ($erpAccountId) {
                foreach ($getParentErpAccountRoles as $roles) {
                    if ((($roles['erp_accounts_exclusion'] == 'Y' && ($roles['by_role_erp'] && $roles['erp_account_id'] == $erpAccountId)) ||
                            ($roles['erp_accounts_exclusion'] == 'N' && ($roles['by_role_erp'] && $roles['erp_account_id'] && $roles['erp_account_id'] != $erpAccountId))) ||
                        (($roles['customer_exclusion'] == 'Y' && ($roles['by_role_customer'] && $roles['customer_id'] == $customerId)) ||
                            ($roles['customer_exclusion'] == 'N' && ($roles['by_role_customer'] && $roles['customer_id'] && $roles['customer_id'] != $customerId)))) {
                        if (!isset($excludeRole[$roles['id']])) {
                            $excludeRole[$roles['id']] = $roles['id'];
                        }
                        continue;
                    }
                }
            } else {
                foreach ($getParentErpAccountRoles as $roles) {
                    if (($roles['by_role_erp'] || $roles['by_role_customer']) ||
                        (($roles['customer_exclusion'] == 'Y' && ($roles['by_role_customer'] && $roles['customer_id'] == $customerId)) ||
                            ($roles['customer_exclusion'] == 'N' && ($roles['by_role_customer'] && $roles['customer_id'] && $roles['customer_id'] != $customerId)))) {
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
                }

            }
            $accessRoleOptions = $this->proccessOptionsArray($getParentErpAccountRoles, $bycustomer, $customerId, $erpAccountId, $excludeRole);
        }
        return $accessRoleOptions;
    }

    public function proccessOptionsArray($getParentErpAccountRoles, $bycustomer, $customerId, $erpAccountId, $excludeRole)
    {
        $this->_options = [];
        $accessRoleOptions = [];
        $enable = 1;
        $disable = 0;
        $byerp = 0;
        foreach ($getParentErpAccountRoles as $roles) {
            if (isset($excludeRole[$roles['id']])) {
                continue;
            }

            if (is_array($bycustomer)) {
                if (!in_array($roles['id'], $bycustomer)) {
                    continue;
                }
                $byerp = 1;
                $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
            } else {
                if ($roles['auto_assign']) {
                    $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
                }
                if ((($roles['by_customer'] && $roles['customer_id'] == $customerId) || ($roles['by_erp_account'] && $roles['erp_account_id'] == $erpAccountId)) && $bycustomer == 'customer') {
                    $this->_options = $this->optionsArray($this->_options, $roles, $enable, $disable, $byerp);
                }
                if ($roles['by_erp_account'] && $roles['erp_account_id'] == $erpAccountId && $bycustomer == 'erp_account') {
                    $byerp = 1;
                    $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
                }
                if ($bycustomer == 'admin') {
                    $byerp = 0;
                    if ((!$roles['auto_assign'] && !$roles['by_erp_account'] && !$roles['by_customer']) || ($roles['by_customer'] && $roles['customer_id'] != $customerId) || ($roles['by_erp_account'] && $roles['erp_account_id'] != $erpAccountId)) {
                        $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
                    }
                    if ($roles['by_customer'] && $roles['customer_id'] == $customerId) {
                        $this->_options = $this->optionsArray($this->_options, $roles, $enable, $disable, $byerp);
                    }
                    if ($roles['by_erp_account'] && $roles['erp_account_id'] == $erpAccountId) {
                        $byerp = 1;
                        $this->_options = $this->optionsArray($this->_options, $roles, $disable, $disable, $byerp);
                    }
                }
            }
        }
        if ($this->_options) {
            foreach ($this->_options as $key => $row) {
                $roles_label[$key] = $row['label'];
                $roles_by_erp[$key] = $row['by_erp_account'];
                $roles_by_cus[$key] = $row['by_customer'];
            }

            array_multisort($roles_label, SORT_ASC, $roles_by_erp, SORT_DESC, $roles_by_cus, SORT_DESC, $this->_options);
            $accessRoleOptions = $this->unique_multidim_array($this->_options, 'role_id');
        }
        return $accessRoleOptions;
    }

    public function optionsArray($options, $roles, $byCustomer, $byRole, $byerp)
    {
        $options [] = array(
            'role_id' => $roles['id'],
            'label' => $roles['title'],
            'value' => $roles['id'],
            'autoAssign' => $roles['auto_assign'],
            'priority' => $roles['priority'],
            'erp_accounts_conditions' => $roles['erp_accounts_conditions'],
            'customer_conditions' => $roles['customer_conditions'],
            'erp_accounts_exclusion' => $roles['erp_accounts_exclusion'],
            'customer_exclusion' => $roles['customer_exclusion'],
            'by_erp_account' => $byerp,
            'by_customer' => $byCustomer,
            'other_roles' => $byRole
        );
        return $options;
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
