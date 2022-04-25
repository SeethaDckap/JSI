<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private static $connectionName = 'checkout';

    protected $configWriter;


    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        WriterInterface $configWriter,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    )
    {
        $this->configWriter = $configWriter;
        $this->customerFactory = $customerCustomerFactory;

        try{
            $state->setAreaCode('frontend');
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            /* DO NOTHING, THE AREA CODE IS ALREADY SET */
        }
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.6.1', '<')) {
            $this->version_2_6_1($installer);
        }
        if (version_compare($context->getVersion(), '2.6.2', '<')) {
            $this->version_2_6_2($installer);
        }
        if (version_compare($context->getVersion(), '2.6.3', '<')) {
            $this->version_2_6_3($installer);
        }
        if (version_compare($context->getVersion(), '2.6.4', '<')) {
            $this->version_2_6_4($installer);
        }
        if (version_compare($context->getVersion(), '2.6.5', '<')) {
            $this->version_2_6_5($installer);
        }
        if (version_compare($context->getVersion(), '2.6.6', '<')) {
            $this->version_2_6_6($setup);
        }
        if (version_compare($context->getVersion(), '2.6.7', '<')) {
            $this->version_2_6_7($installer);
        }
        if (version_compare($context->getVersion(), '2.6.8', '<')) {
            $this->version_2_6_8($installer);
        }
        if (version_compare($context->getVersion(), '2.6.9', '<')) {
            $this->version_2_6_9($installer);
        }
        if (version_compare($context->getVersion(), '2.7.0', '<')) {
            $this->version_2_7_0($installer);
        }
        $installer->endSetup();
    }

    protected function version_2_6_1(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_erp_mapping_datamapping'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_erp_mapping_datamapping')) != true) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_erp_mapping_datamapping')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'message'
            )->addColumn(
                'orignal_tag',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'orignal_tag'
            )->addColumn(
                'mapped_tag',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'mapped_tag'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )->setComment(
                'Data mapping for ECC messages'
            );
            $installer->getConnection()->createTable($table);
        }

    }

    protected function version_2_6_2(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addIndex(
            $installer->getTable('ecc_erp_mapping_datamapping'),
            $installer->getIdxName('ecc_erp_mapping_datamapping', ['message', 'orignal_tag'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
            ['message', 'orignal_tag'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    protected function version_2_6_3(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_manage_dashboard'
         */
        if ($installer->getConnection()->isTableExists('ecc_manage_dashboard') != true) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_manage_dashboard')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'message_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Message Type'
            )->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'code'
            )->addColumn(
                'filters',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Filters'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '1'],
                'Status'
            )->addColumn(
                'date_range',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Date Range'
            )->addColumn(
                'grid_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Grid Count'
            )->setComment(
                'Supplier Dashboard'
            );
            $table->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    $installer->getTable('ecc_manage_dashboard'),
                    'customer_id'),
                'customer_id',
                $installer->getTable('customer_entity'), 'entity_id',
                Table::ACTION_CASCADE
            );
            $installer->getConnection()->createTable($table);
        }

    }

    protected function version_2_6_4(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_erp_mapping_erpclaimstatus'
         */
        $tableName = $installer->getTable('ecc_erp_mapping_erpclaimstatus');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ], 'ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    55,
                    [
                        'nullable' => false
                    ], 'ERP Code'
                )
                ->addColumn(
                    'claim_status',
                    Table::TYPE_TEXT,
                    200,
                    [
                        'nullable' => false
                    ], 'ECC Claim Status'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ], 'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }
    }

    protected function version_2_6_5(SchemaSetupInterface $installer)
    {
        //install ecc_customer_erp_account
        $installer->getConnection()->dropTable('ecc_customer_erp_account');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_customer_erp_account')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );
        $table->addColumn('customer_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Sales Rep Id'
        );
        $table->addColumn('erp_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Erp Account Id'
        );
        $table->addColumn('erp_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Erp Account Id'
        );
        $table->addColumn('erp_account_type', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Erp Account Type'
        );
        $table->addColumn('contact_code', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Erp Contact Code'
        );
        $table->addColumn('is_favourite', Table::TYPE_BOOLEAN,
            1,
            ['nullable' => false, 'default' => '0'],
        'Is Favourite'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_customer_erp_account'),
                array('customer_id')
            ),
            array('customer_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_customer_erp_account'),
                array('erp_account_id')
            ),
            array('erp_account_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_customer_erp_account'),
                array('customer_id', 'erp_account_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('customer_id', 'erp_account_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('customer_entity'),
                'entity_id',
                $installer->getTable('ecc_customer_erp_account'),
                'customer_id'),
            'customer_id',
            $installer->getTable('customer_entity'), 'entity_id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_erp_account'),
                'entity_id',
                $installer->getTable('ecc_customer_erp_account'),
                'erp_account_id'),
            'erp_account_id',
            $installer->getTable('ecc_erp_account'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);
    }

    protected function version_2_6_6(SchemaSetupInterface $installer)
    {
        $collection = $this->customerFactory->create()->getCollection();
        if ($collection->getSize()) {
            $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
            $collection->addAttributeToSelect('ecc_erp_account_type');
            $collection->addAttributeToSelect('ecc_contact_code');
            $data = [];
            foreach ($collection as $customer) {
                if ($customer->getData('ecc_erpaccount_id')) {
                    $data[] = [
                        'customer_id' => $customer->getData('entity_id'),
                        'erp_account_id' => $customer->getData('ecc_erpaccount_id'),
                        'contact_code' => $customer->getData('ecc_contact_code'),
                        'erp_account_type' => $customer->getData('ecc_erp_account_type'),
                    ];
                }
            }
            if (!empty($data)) {
                $tableName = $installer->getTable('ecc_customer_erp_account');
               // $installer->getConnection()->truncateTable($tableName);
                $installer->getConnection()->insertOnDuplicate($tableName, $data);
            }

        }
    }

    protected function version_2_6_7(SchemaSetupInterface $installer)
    {
        if (!$installer->getConnection()->tableColumnExists($installer->getTable('ecc_manage_dashboard'),
            'account_id')) {
            $installer->run("ALTER TABLE `{$installer->getTable('ecc_manage_dashboard')}` ADD `account_id` VARCHAR(255)  AFTER `customer_id`;");
        }
    }

    protected function version_2_6_8(SchemaSetupInterface $installer)
    {
        $collection = $this->customerFactory->create()->getCollection();

        $tableName = $installer->getTable('ecc_manage_dashboard');
        $collection->getSelect()->joinInner(
            ['mandas' => $tableName],
            'e.entity_id = mandas.customer_id',
            ['']
        )->group('e.entity_id');
        if ($collection->getSize()) {
            $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
            $data = [];
            foreach ($collection as $customer) {
                if ($customer->getData('ecc_erpaccount_id')) {
                    $data[] = [
                        'customer_id' => $customer->getData('entity_id'),
                        'account_id' => $customer->getData('ecc_erpaccount_id'),
                    ];
                }
            }
            if (!empty($data)) {
                $connection=$installer->getConnection();
                foreach($data as $id){
                    $connection->update($tableName,
                        ['account_id' => $id['account_id']],
                        ['customer_id = ?' => $id['customer_id']]
                    );
                }
            }

        }
    }

    protected function version_2_6_9(SchemaSetupInterface $installer){
        $connection = $installer->getConnection();
        /* Start Create an ERP customer account attribute tax_exempt_reference */
        if ($connection->tableColumnExists('ecc_erp_account', 'is_tax_exempt') === false) {
            $connection
                ->addColumn(
                    $installer->getTable('ecc_erp_account'), 'is_tax_exempt', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'unsigned' => true,
                        'primary' => false,
                        'length' => 1,
                        'comment' => 'Allow Tax Exempt, 2: Default, 0: Disabled, 1: Enabled',
                        'default' => 2
                    ]
                );
        }
        /* End Create an ERP customer account attribute ecc_tax_exempt_reference */

        /*Start Create attribute ecc_tax_exempt_reference for quote and order table*/
        if ($connection->tableColumnExists('sales_order', 'ecc_tax_exempt_reference') === false) {
            $connection
                ->addColumn(
                    $installer->getTable('sales_order'), 'ecc_tax_exempt_reference', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Tax Exempt Reference'
                    ]
                );
        }
        if ($connection->tableColumnExists('quote', 'ecc_tax_exempt_reference') === false) {
            $connection
                ->addColumn(
                    $installer->getTable('quote'), 'ecc_tax_exempt_reference', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Tax Exempt Reference'
                    ]
                );
        }
        /* End Create attribute ecc_tax_exempt_reference for quote and order table*/
    }


    /**
     * Adds new column 'shipping_address_allowed' and 'billing_address_allowed' to table ecc_erp_account.
     *
     * @param SchemaSetupInterface $installer DB schema resource interface.
     *
     * @return void
     **/
    private function version_2_7_0(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'shipping_address_allowed') === false) {
                $installer->getConnection()->addColumn(
                    $tableName,
                    'shipping_address_allowed',
                    [
                        'type'     => Table::TYPE_BOOLEAN,
                        'length'   => 1,
                        'nullable' => true,
                        'comment'  => ' Allow Shipping Address Creation? ',
                    ]
                );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'billing_address_allowed') === false) {
                $installer->getConnection()->addColumn(
                    $tableName,
                    'billing_address_allowed',
                    [
                        'type'     => Table::TYPE_BOOLEAN,
                        'length'   => 1,
                        'nullable' => true,
                        'comment'  => ' Allow Billing Address Creation? ',
                    ]
                );
            }
        }//end if

    }//end version_2_7_0()


}
