<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml;

use Epicor\Comm\Block\Customer\Returns;

/**
 * Role admin actions
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
abstract class Roles extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_AccessRight::roles';
    protected $_currentCustomersInErpAccounts = array();
    protected $_erpaccounts = array();

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $dateTimeTimezone;

    /**
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModelFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;

    /**
     * @var \Epicor\AccessRight\Helper\DataFactory
     */
    protected $rolesHelperFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * Roles constructor.
     * @param Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->registry = $context->getRegistry();
        $this->rolesRoleModelFactory = $context->getRolesRoleModelFactory();
        $this->backendJsHelper = $context->getBackendJsHelper();
        $this->commHelperFactory = $context->getCommHelperFactory();
        $this->backendSession = $context->getBackendSession();
        $this->jsonHelper = $context->getJsonHelper();
        $this->dateTimeTimezone = $context->getDateTimeTimezone();
        $this->serializer = $context->getSerializer();
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Admin ACL method
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->backendAuthSession
            ->isAllowed('Epicor_AccessRight::roles');
    }

    /**
     * Saves details for the role
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processDetailsSave($role, $data)
    {
        $role->setTitle($data['title']);
        $role->setNotes($data['notes']);
        $role->setDescription($data['description']);
        $role->setPriority(isset($data['priority']) ? $data['priority'] : 0);
        $role->setActive(isset($data['active']) ? 1 : 0);
        $role->setAutoAssign(isset($data['auto_assign']) ? 1 : 0);

        if (empty($data['start_date']) == false) {
            if (!array_key_exists('select_start_time', $data)) {
                $data['start_time'] = array('00', '00', '00');
            }
            $time = implode(':', $data['start_time']);
            $dateTime = $data['start_date'] . ' ' . $time;

            $role->setStartDate($this->dateTimeTimezone->convertConfigTimeToUtc($dateTime));
        } else {
            $role->setStartDate(false);
        }

        if (empty($data['end_date']) == false) {
            if (!array_key_exists('select_end_time', $data)) {
                $data['end_time'] = array('23', '59', '59');
            }
            $time = implode(':', $data['end_time']);
            $dateTime = $data['end_date'] . ' ' . $time;

            $role->setEndDate($this->dateTimeTimezone->convertConfigTimeToUtc($dateTime));
        } else {
            $role->setEndDate(false);
        }
    }

    /**
     * Checks if ERP Accounts Information needs to be saved
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param $data
     */
    protected function processERPAccountsSave($role, $data)
    {
        $erpaccounts = $this->getRequest()->getParam('selected_erpaccounts');
        if (!is_null($erpaccounts)) {
            $this->saveERPAccounts($role, $data);
            // if erp_account_link_type = 'N', save erp account exclusion indicator as 'N', else save value
            $linkType = $data['erp_account_link_type'];
            $dataExclusion = isset($data['erp_accounts_exclusion']) ? 'Y' : 'N';
            $exclusion = $linkType == 'N' ? 'N' : $dataExclusion;
            $role->setErpAccountLinkType($linkType);
            $role->setErpAccountsExclusion($exclusion);

            //erp Condition
            if (isset($data["is_erp_account_condition_enabled"]) && $data["is_erp_account_condition_enabled"]) {
                if (isset($data['erp_rule']) && isset($data['erp_rule']['conditions'])) {
                    $erpDataRule = [];
                    $erpDataRule['conditions'] = $data['erp_rule']['conditions'];
                    $erpModel = $role->getErpAccountModel();
                    $erpModel->loadPost($erpDataRule);
                    $erpCondition = $erpModel->getConditions()->asArray();
                    $role->setErpAccountsConditions($this->serializer->serialize($erpCondition));
                }
            } else {
                $role->setErpAccountsConditions(null);
            }
        }
    }

    /**
     * Save ERP Accounts Information
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param array $data
     *
     * @return void
     */
    protected function saveERPAccounts(&$role, $data)
    {
        $erpaccounts = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
        $role->removeErpAccounts($role->getErpAccounts());
        $role->addErpAccounts($erpaccounts);
    }


    /**
     * Checks if Customers Information needs to be saved
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     *
     * @param array $data
     */
    protected function processCustomersSave($role, $data)
    {
        $customers = $this->getRequest()->getParam('selected_customers');
        if (!is_null($customers)) {
            $this->saveCustomers($role, $data);
            // if erp_account_link_type = 'N', save erp account exclusion indicator as 'N', else save value
            $exclusion = isset($data['customer_exclusion']) ? 'Y' : 'N';
            $role->setCustomerExclusion($exclusion);

            //Customer Condition
            if (isset($data["is_customers_condition_enabled"]) && $data["is_customers_condition_enabled"]) {
                if (isset($data["customer_rule"]) && isset($data['customer_rule']['conditions'])) {
                    $dataRule['conditions'] = $data['customer_rule']['conditions'];
                    $customerModel = $role->getCustomerModel();
                    $customerModel->loadPost($dataRule);
                    $customerCondition = $customerModel->getConditions()->asArray();
                    $role->setCustomerConditions($this->serializer->serialize($customerCondition));
                }
            } else {
                $role->setCustomerConditions(null);
            }
        }
    }

    /**
     * Save Customers Information
     *
     * @param \Epicor\AccessRight\Model\RoleModel $role
     * @param array $data
     *
     * @return void
     */
    protected function saveCustomers(&$role, $data)
    {
        $customers = isset($data['links']['customers']) ? array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers'])) : array();
        $role->removeCustomers($role->getCustomers());
        $role->addCustomers($customers);
    }

    /**
     * Deletes the given role by id
     *
     * @param integer $id
     * @param boolean $mass
     *
     * @return void
     */
    protected function delete($id, $mass = false)
    {
        $model = $this->rolesRoleModelFactory->create();
        /* @var $role \Epicor\AccessRight\Model\RoleModel */

        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                if ($model->delete()) {
                    if (!$mass) {
                        $this->messageManager->addSuccess(__('Role deleted'));
                    }
                } else {
                    $this->messageManager->addError(__('Could not delete Role ' . $id));
                }
            }
        }
    }

    /**
     * Load Role
     *  @param integer $copyFromId optional give this value when duplicate Role is created
     * @return \Epicor\AccessRight\Model\RoleModel
     */
    protected function loadEntity($copyFromId = null)
    {
        $role = null;
        $id = $this->getRequest()->getParam('id', null);
        if($id != null ){
            $role = $this->rolesRoleModelFactory->create()->load($id);
            /* @var $role \Epicor\AccessRight\Model\RoleModel */
        }else{
            $duplicateId = $this->getRequest()->getParam('cid', null);
            $duplicateId = isset($duplicateId) ? $duplicateId : $copyFromId;
            if($duplicateId != null ){
                $role = $this->rolesRoleModelFactory->create()->load($duplicateId);
                $this->registry->register('IsDuplicateRole', true);
            }else{
                $role = $this->rolesRoleModelFactory->create();
            }
        }
        $this->registry->register('role', $role);

        return $role;
    }

}
