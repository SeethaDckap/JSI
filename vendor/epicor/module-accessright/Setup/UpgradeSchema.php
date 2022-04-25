<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.5.1', '<')) {
            $this->version2_5_1($installer);
        }

        if (version_compare($context->getVersion(), '2.5.3', '<')) {
            $this->version2_5_3($installer);
        }
        //fix for WSO-7676
        //erp_access_rights was not created in ecc_erp_account when it was upgrade to 2.5.1
        if (version_compare($context->getVersion(), '2.5.4', '<')) {
            $this->version2_5_1($installer);
        }
        //fix for ECC-9066
        //erp_access_rights was not created in ecc_erp_account for fresh and exiting upgrade task
        if (version_compare($context->getVersion(), '2.5.6', '<')) {
            $this->version2_5_1($installer);
        }
    }

    protected function version2_5_1(SchemaSetupInterface $installer)
    {

        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'erp_access_rights') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName, 'erp_access_rights', [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => 'Access Rights, 2: Global Default, 0: Disabled, 1: Access Role'
                        ]
                    );
            }
        }
    }

    protected function version2_5_3(SchemaSetupInterface $installer)
    {

        /**
         * Create table 'ecc_access_role_rule'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_access_right_attributemapping')) != true) {

            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_access_right_attributemapping')
            )->addColumn(
                'erp_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Erp Account Attribute code'
            )->addColumn(
                'customer_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Customer Attribute Code'
            )->addColumn(
                'config',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Config Path'
            )->setComment(
                'Ecc Access Role Attribute Mapping'
            );
            $installer->getConnection()->createTable($table);
        }
    }

}
