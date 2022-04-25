<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.5.2', '<')) {
            $this->version2_5_2($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.5.3', '<')) {
            $this->version2_5_3($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.5.5', '<')) {
            $this->version2_5_5($setup);
        }

        if (version_compare($context->getVersion(), '2.5.7', '<')) {
            $this->version2_5_7($setup);
        }

        if (version_compare($context->getVersion(), '2.5.8', '<')) {
            $this->version2_5_8($setup);
        }


    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version2_5_2($customerSetup)
    {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_access_rights', [
            'group' => 'General',
            'label' => 'Access Rights',
            'type' => 'int',
            'input' => 'select',
            'source' => 'Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRightsOptions',
            'required' => false,
            'user_defined' => false,
            'visible' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'default' => 2,
            'system' => false
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_access_roles', [
            'group' => 'General',
            'label' => 'Select Custom Access Role',
            'type' => 'text',
            'input' => 'multiselect',
            'source' => 'Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRoles',
            'required' => false,
            'user_defined' => false,
            'visible' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $attributes = [
            'ecc_access_rights', 'ecc_access_roles'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }


    private function version2_5_3($customerSetup)
    {
        $datas = [
            ['erp_code' => 'is_branch_pickup_allowed', 'customer_code' => 'ecc_is_branch_pickup_allowed', 'config' => 'epicor_comm_locations/global/isbranchpickupallowed'],
            ['erp_code' => 'login_mode_type', 'customer_code' => 'ecc_login_mode_type', 'config' => 'dealerconnect_enabled_messages/dealer_settings/login_mode_type'],
            ['erp_code' => 'is_arpayments_allowed', 'customer_code' => '', 'config' => 'customerconnect_enabled_messages/CAPS_request/active']
        ];
        foreach ($datas as $data) {
            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable('ecc_access_right_attributemapping'),
                $data,
                ['erp_code', 'customer_code', 'config']
            );
        }
    }


    /**
     * AccessRole Add New Role Resource To Exist Role.
     *
     * @param $setup
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     * @throws \Zend_Db_Adapter_Exception                      AdapterException.
     * @throws \Zend_Db_Statement_Exception                    DBExceptions.
     */
    private function version2_5_5($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $roleIds = $writeConnection->query('Select id FROM ecc_access_role');
        $roles   = $roleIds->fetchAll();

        $dataRows = [
            ['resource_id' => 'Epicor_Checkout::catalog_search'],
            ['resource_id' => 'Epicor_Checkout::catalog_advance_search'],
            ['resource_id' => 'Epicor_Checkout::catalog_quick_add'],
        ];
        foreach ($roles as $role) {
            foreach ($dataRows as $dataRow) {
                $data                   = [];
                $data['access_role_id'] = $role['id'];
                $data['resource_id']    = $dataRow['resource_id'];
                $data['permission']     = 'allow';

                $countQuery = 'select count(rule_id) as count 
                    FROM ecc_access_role_rule 
                    WHERE resource_id = "'.$dataRow['resource_id'].'" and access_role_id = "'.$role['id'].'"';
                $countSql   = $writeConnection->query($countQuery);
                $counts     = $countSql->fetch();

                if (!$counts['count']) {
                    $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                        $this->moduleDataSetup->getTable('ecc_access_role_rule'),
                        $data,
                        [
                            'access_role_id',
                            'resource_id',
                            'permission'
                        ]
                    );
                }
            }//end foreach
        }//end foreach

    }//end version2_5_5()

    /**
     * AccessRole Add New Role Resource To Exist Role.
     *
     * @param $setup
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     * @throws \Zend_Db_Adapter_Exception                      AdapterException.
     * @throws \Zend_Db_Statement_Exception                    DBExceptions.
     */
    private function version2_5_7($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $roleIds = $writeConnection->query('Select id FROM ecc_access_role');
        $roles   = $roleIds->fetchAll();

        $dataRows = [
            ['resource_id' => 'Epicor_Customer::my_account_approvals_dashboard'],
            ['resource_id' => 'Epicor_Customer::my_account_approvals'],
            ['resource_id' => 'Epicor_Customer::my_account_approvals_read'],
            ['resource_id' => 'Epicor_Customer::my_account_approvals_export'],
            ['resource_id' => 'Epicor_Customer::my_account_approvals_details'],
            ['resource_id' => 'Epicor_Customer::my_account_approvals_approved_reject'],
            ['resource_id' => 'Epicor_Customer::my_account_group'],
            ['resource_id' => 'Epicor_Customer::my_account_group_read'],
            ['resource_id' => 'Epicor_Customer::my_account_group_create'],
            ['resource_id' => 'Epicor_Customer::my_account_group_edit'],
            ['resource_id' => 'Epicor_Customer::my_account_group_delete'],
            ['resource_id' => 'Epicor_Customer::my_account_group_details'],
        ];
        foreach ($roles as $role) {
            foreach ($dataRows as $dataRow) {
                $data                   = [];
                $data['access_role_id'] = $role['id'];
                $data['resource_id']    = $dataRow['resource_id'];
                $data['permission']     = 'allow';

                $countQuery = 'select count(rule_id) as count 
                    FROM ecc_access_role_rule 
                    WHERE resource_id = "'.$dataRow['resource_id'].'" and access_role_id = "'.$role['id'].'"';
                $countSql   = $writeConnection->query($countQuery);
                $counts     = $countSql->fetch();

                if (!$counts['count']) {
                    $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                        $this->moduleDataSetup->getTable('ecc_access_role_rule'),
                        $data,
                        [
                            'access_role_id',
                            'resource_id',
                            'permission'
                        ]
                    );
                }
            }//end foreach
        }//end foreach

    }//end version2_5_5()

    /**
     * AccessRole Add New Role Resource To Exist Role.
     *
     * @param $setup
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     * @throws \Zend_Db_Adapter_Exception                      AdapterException.
     * @throws \Zend_Db_Statement_Exception                    DBExceptions.
     */
    private function version2_5_8($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $roleIds = $writeConnection->query('Select id FROM ecc_access_role');
        $roles = $roleIds->fetchAll();

        $dataRows = [
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_account_recentpurchases'],
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_account_recentpurchases_read'],
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_account_recentpurchases_export'],
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_account_recentpurchases_edit'],
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_account_recentpurchases_reorder'],

        ];
        foreach ($roles as $role) {
            foreach ($dataRows as $dataRow) {
                $data = [];
                $data['access_role_id'] = $role['id'];
                $data['resource_id'] = $dataRow['resource_id'];
                $data['permission'] = 'allow';

                $countQuery = 'select count(rule_id) as count 
                    FROM ecc_access_role_rule 
                    WHERE resource_id = "' . $dataRow['resource_id'] . '" and access_role_id = "' . $role['id'] . '"';
                $countSql = $writeConnection->query($countQuery);
                $counts = $countSql->fetch();

                if (!$counts['count']) {
                    $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                        $this->moduleDataSetup->getTable('ecc_access_role_rule'),
                        $data,
                        [
                            'access_role_id',
                            'resource_id',
                            'permission'
                        ]
                    );
                }
            }//end foreach
        }//end foreach

    }//end version2_5_8()

}
