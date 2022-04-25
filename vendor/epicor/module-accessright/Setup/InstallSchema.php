<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        /*
         * Create table ecc_access_role
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_access_role')) != true) {

            $tableName = $installer->getTable('ecc_access_role');
            $table = $installer->getConnection()->newTable(
                $tableName
            );
            $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ), 'Access Roles ID'
            );
            $table->addColumn('title', Table::TYPE_TEXT, 255, array(
                'nullable' => false,
            ), 'Access Roles Title'
            );
            $table->addColumn('description', Table::TYPE_TEXT, '4G', array(
                'nullable' => true,
                'default' => false
            ), 'Description');
            $table->addColumn('notes', Table::TYPE_TEXT, '4G', array(
                'nullable' => true,
                'default' => false
            ), 'Notes Field');
            $table->addColumn('active', Table::TYPE_BOOLEAN, null, array(
                'nullable' => false,
                'default' => '0'
            ), 'Active');
            $table->addColumn('auto_assign', Table::TYPE_BOOLEAN, null, array(
                'nullable' => false,
                'default' => '0'
            ), 'Auto Assign');
            $table->addColumn('start_date', Table::TYPE_DATETIME, 255, array(
                'identity' => false,
                'nullable' => true,
                'primary' => false,
            ), 'Start Date');
            $table->addColumn('end_date', Table::TYPE_DATETIME, 255, array(
                'identity' => false,
                'nullable' => true,
                'primary' => false,
            ), 'End Date');
            $table->addColumn('erp_accounts_conditions', Table::TYPE_TEXT, '4G', array(
                'nullable' => true,
                'default' => false
            ), 'Product Filter Conditions');
            $table->addColumn('customer_conditions', Table::TYPE_TEXT, '4G', array(
                'nullable' => true,
                'default' => false
            ), 'Product Filter Conditions');
            $table->addColumn('erp_account_link_type', Table::TYPE_TEXT, 1, array(
                'nullable' => false,
                'default' => 'N'
            ), 'How are ERP Accounts linked?');
            $table->addColumn('priority', Table::TYPE_INTEGER, 11, array(
                'nullable' => true,
                'default' => 0
            ), 'Priority');
            $table->addColumn('created_date', Table::TYPE_DATETIME, 255, array(
                'identity' => false,
                'nullable' => false,
                'primary' => false,
            ), 'Start Date');
            $table->addColumn('updated_date', Table::TYPE_DATETIME, 255, array(
                'identity' => false,
                'nullable' => false,
                'primary' => false,
            ), 'End Date');

            $table->addColumn('erp_accounts_exclusion', Table::TYPE_TEXT, 1, array(
                'nullable' => false,
                'default' => 'N'
            ), 'Erp Accounts Exclusion');

            $table->addColumn('customer_exclusion', Table::TYPE_TEXT, 1, array(
                'nullable' => false,
                'default' => 'N'
            ), 'Customer Exclusion');
            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_access_role'),
                    array('active', 'start_date', 'end_date')
                ),
                array('active', 'start_date', 'end_date')
            );
            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_access_role'),
                    array('erp_account_link_type')
                ),
                'erp_account_link_type'
            );
            $installer->getConnection()->createTable($table);
        }
        /**
         * Create table 'ecc_access_role_erp_account'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_access_role_erp_account')) != true) {

            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_access_role_erp_account')
            );
            $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ), 'Incremental ID'
            );

            $table->addColumn('access_role_id', Table::TYPE_INTEGER, 10, array(
                'identity' => false,
                'unsigned' => true,
                'nullable' => false,
                'primary' => false,
            ), 'Access Roles ID'
            );
            $table->addColumn('erp_account_id', Table::TYPE_INTEGER, 10, array(
                'identity' => false,
                'unsigned' => true,
                'nullable' => false,
                'primary' => false,
            ), 'ERP Account ID'
            );
            $table->addColumn('by_erp_account', Table::TYPE_BOOLEAN, null, array(
                'nullable' => false,
                'default' => '0'
            ), 'Role Selected By Erp Account');
            $table->addColumn('by_role', Table::TYPE_BOOLEAN, null, array(
                'nullable' => false,
                'default' => '0'
            ), 'Erp Account Selected By Role');

            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_access_role_erp_account'),
                    array('access_role_id', 'erp_account_id','by_erp_account','by_role'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                array('access_role_id', 'erp_account_id','by_erp_account','by_role'),
                array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
            );
            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_access_role_erp_account'),
                    array('erp_account_id')
                ),
                'erp_account_id'
            );

            $table->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('ecc_erp_account'),
                    'entity_id',
                    $installer->getTable('ecc_access_role_erp_account'),
                    'erp_account_id'),
                'erp_account_id',
                $installer->getTable('ecc_erp_account'), 'entity_id',
                Table::ACTION_CASCADE);

            $table->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('ecc_access_role'),
                    'id',
                    $installer->getTable('ecc_access_role_erp_account'),
                    'access_role_id'),
                'access_role_id',
                $installer->getTable('ecc_access_role'), 'id',
                Table::ACTION_CASCADE);
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'ecc_access_role_customer'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_access_role_customer')) != true) {

            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_access_role_customer')
            );
            $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ), 'Incremental ID'
            );

            $table->addColumn('access_role_id', Table::TYPE_INTEGER, 10, array(
                'identity' => false,
                'unsigned' => true,
                'nullable' => false,
                'primary' => false,
            ), 'Access Roles ID'
            );
            $table->addColumn('customer_id', Table::TYPE_INTEGER, 10, array(
                'identity' => false,
                'unsigned' => true,
                'nullable' => false,
                'primary' => false,
            ), 'Customer ID'
            );
            $table->addColumn('by_customer', Table::TYPE_BOOLEAN, null, array(
                'nullable' => false,
                'default' => '0'
            ), 'Role Selected By Customer');
            $table->addColumn('by_role', Table::TYPE_BOOLEAN, null, array(
                'nullable' => false,
                'default' => '0'
            ), 'Customer Selected By Role');

            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_access_role_customer'),
                    array('access_role_id', 'customer_id','by_customer','by_role'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                array('access_role_id', 'customer_id','by_customer','by_role'),
                array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
            );
            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_access_role_customer'),
                    array('customer_id')
                ),
                'customer_id'
            );
            $table->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    $installer->getTable('ecc_access_role_customer'),
                    'customer_id'),
                'customer_id',
                $installer->getTable('customer_entity'), 'entity_id',
                Table::ACTION_CASCADE);
            $table->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('ecc_access_role'),
                    'id',
                    $installer->getTable('ecc_access_role_customer'),
                    'access_role_id'),
                'access_role_id',
                $installer->getTable('ecc_access_role'), 'id',
                Table::ACTION_CASCADE);
            $installer->getConnection()->createTable($table);

        }

        /**
         * Create table 'ecc_access_role_rule'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_access_role_rule')) != true) {

            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_access_role_rule')
            )->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Incremental Rule ID'
            )->addColumn(
                'access_role_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => false,'unsigned' => true, 'nullable' => false, 'primary' => false],
                'Access Role ID'
            )->addColumn(
                'resource_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Resource ID'
            )->addColumn(
                'privileges',
                Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Privileges'
            )->addColumn(
                'permission',
                Table::TYPE_TEXT,
                10,
                [],
                'Permission'
            )->addIndex(
                $installer->getIdxName($installer->getTable('ecc_access_role_rule'), ['resource_id', 'access_role_id']),
                ['resource_id', 'access_role_id']
            )->addIndex(
                $installer->getIdxName($installer->getTable('ecc_access_role_rule'), ['access_role_id', 'resource_id']),
                ['access_role_id', 'resource_id']
            )->addForeignKey(
                $installer->getFkName($installer->getTable('ecc_access_role_rule'), 'access_role_id', $installer->getTable('ecc_access_role'), 'id'),
                'access_role_id',
                $installer->getTable('ecc_access_role'),
                'id',
                Table::ACTION_CASCADE
            )->setComment(
                'Ecc Access Role Rule Table'
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
