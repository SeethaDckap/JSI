<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Database\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $tableName = $installer->getTable('ecc_erp_account');
        
        $description = $setup->getConnection()
                ->describeTable($installer->getTable('customer_group'))['customer_group_id'];
        $customer_group_id_type = $description['DATA_TYPE'];
        if($customer_group_id_type == 'int'){
            $customer_group_id_type = Table::TYPE_INTEGER;
        }
        
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'magento_id',
                    $customer_group_id_type,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Magento ID'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Name'
                )
                ->addColumn(
                    'allow_backorders',
                    Table::TYPE_SMALLINT,
                    6,
                    [],
                    'Allow Backorders'
                )
                ->addColumn(
                    'allow_cash_on_delivery',
                    Table::TYPE_SMALLINT,
                    6,
                    [],
                    'Allow Cash on Delivery'
                )->addColumn(
                    'onstop',
                    Table::TYPE_SMALLINT,
                    6,
                    [],
                    'On stop'
                )
                ->addColumn(
                    'balance',
                    Table::TYPE_FLOAT,
                    null,
                    [],
                    'Balance'
                )
                ->addColumn(
                    'credit_limit',
                    Table::TYPE_FLOAT,
                    null,
                    [],
                    'Credit Limit'
                )
                ->addColumn(
                    'unallocated_cash',
                    Table::TYPE_FLOAT,
                    null,
                    [],
                    'Unallocated Cash'
                )
                ->addColumn(
                    'currency_code',
                    Table::TYPE_TEXT,
                    6,
                    [],
                    'Currency Code'
                )
                ->addColumn(
                    'last_payment_date',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Last Payment Date'
                )
                ->addColumn(
                    'last_payment_value',
                    Table::TYPE_FLOAT,
                    null,
                    [],
                    'Last Payment Value'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email'
                )
                ->addColumn(
                    'default_payment_method_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Default Payment Method Code'
                )
                ->addColumn(
                    'default_delivery_address_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Default Delivery Address Code'
                )
                ->addColumn(
                    'default_delivery_method_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Default Delivery Method Code'
                )
                ->addColumn(
                    'default_invoice_address_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Default Invoice Address Code'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Updated At'
                )
                ->addColumn(
                    'min_order_amount',
                    Table::TYPE_DECIMAL,
                    '10,2',
                    ['default' => '0.00'],
                    'Minimum Order Amount'
                )
                ->addColumn(
                    'account_type',
                    Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' => 'Customer'],
                    'Account Type'
                )
                ->addColumn(
                    'brand_refresh',
                    Table::TYPE_BOOLEAN,
                    1,
                    ['nullable' => false],
                    'Flag to say if this erp account needs its brands refreshing'
                )
                ->addColumn(
                    'brands',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Brand information for the account'
                )
                ->addColumn(
                    'short_code',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Erp Account Short Code'
                )
                ->addColumn(
                    'account_number',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Erp Account Number'
                )
                ->addColumn(
                    'company',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Erp Account Company'
                )
                ->addColumn(
                    'custom_address_allowed',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Custom Address Allowed'
                )
                ->addColumn(
                    'allow_masquerade',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Allow Masquerade'
                )
                ->addColumn(
                    'allow_masquerade_cart_clear',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Allow Masquerade Cart Clear'
                )
                ->addColumn(
                    'allow_masquerade_cart_reprice',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Allow Masquerade Cart Reprice'
                )
                ->addColumn(
                    'is_warranty_customer',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false],
                    'Flag to say if this erp account needs is a warranty customer'
                )
                ->addColumn(
                    'tax_class',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Tax Class'
                )
                ->addColumn(
                    'default_location_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Default Location Code'
                )
                ->addColumn(
                    'location_link_type',
                    Table::TYPE_TEXT,
                    1,
                    ['default' => 'E'],
                    'Location Link Type'
                )
                ->addColumn(
                    'cpn_editing',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Customer Part Number/SKU editing enabling, NULL: Default, 0: Disabled, 1: Enabled'
                )
                ->addColumn(
                    'po_mandatory',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Purchase Order Mandatory, NULL: Default, 0: Disabled, 1: Enabled'
                )
                ->addColumn(
                    'pre_reg_password',
                    Table::TYPE_TEXT,
                    23,
                    ['nullable' => false, 'default' => ''],
                    'Pre Register password'
                )
                ->addColumn(
                    'sales_rep',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Sales Rep Id'
                )
                ->addColumn(
                    'allowed_contract_type',
                    Table::TYPE_TEXT,
                    1,
                    [],
                    'Allowed Contract Type'
                )
                ->addColumn(
                    'required_contract_type',
                    Table::TYPE_TEXT,
                    1,
                    [],
                    'Required Contract Type'
                )
                ->addColumn(
                    'allow_non_contract_items',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Allow Non Contract Items'
                )
                ->addColumn(
                    'contract_shipto_default',
                    Table::TYPE_TEXT,
                    50,
                    [],
                    'Default Ship to Selection'
                )
                ->addColumn(
                    'contract_shipto_date',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Use Ship To Based on Contract Date'
                )
                ->addColumn(
                    'contract_shipto_prompt',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Prompt for Ship To Selection if More Than 1'
                )
                ->addColumn(
                    'contract_header_selection',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Header Contract Selection'
                )
                ->addColumn(
                    'contract_header_prompt',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Prompt for Header Selection if More Than 1'
                )
                ->addColumn(
                    'contract_header_always',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Always use Header Contract when Available'
                )
                ->addColumn(
                    'contract_line_selection',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Line Contract Selection'
                )
                ->addColumn(
                    'contract_line_prompt',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Show Dropdown for Optional Contracts'
                )
                ->addColumn(
                    'contract_line_always',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Always use Line Level Contract when Available'
                )
                ->addColumn(
                    'is_branch_pickup_allowed',
                    Table::TYPE_TEXT,
                    1,
                    ['default' => '2'],
                    'Branch Pickup Allowed, 2: Default, 0: Disabled, 1: Enabled'
                )
                ->addColumn(
                    'allowed_delivery_methods',
                    Table::TYPE_TEXT,
                    '4G',
                    [],
                    'Serialized array of delivery methods allowed for ERP account.'
                )
                ->addColumn(
                    'allowed_delivery_methods_exclude',
                    Table::TYPE_TEXT,
                    '4G',
                    [],
                    'Serialized array of delivery methods not allowed for ERP account.'
                )
                ->addColumn(
                    'allowed_payment_methods',
                    Table::TYPE_TEXT,
                    '4G',
                    [],
                    'Serialized array of payment methods allowed for ERP account.'
                )
                ->addColumn(
                    'allowed_payment_methods_exclude',
                    Table::TYPE_TEXT,
                    '4G',
                    [],
                    'Serialized array of payment methods not allowed for ERP account.'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['erp_code']),
                    ['erp_code']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['magento_id']),
                    ['magento_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'magento_id',
                        $installer->getTable('customer_group'),
                        'customer_group_id'
                    ),
                    'magento_id',
                    $installer->getTable('customer_group'),
                    'customer_group_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_account_address');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'Entity ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'erp_customer_group_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Customer Group Code'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Name'
                )
                ->addColumn(
                    'address1',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Address 1'
                )
                ->addColumn(
                    'address2',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Address 2'
                )
                ->addColumn(
                    'address3',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Address 3'
                )
                ->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'City'
                )
                ->addColumn(
                    'county',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'County'
                )
                ->addColumn(
                    'country',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Country'
                )
                ->addColumn(
                    'postcode',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Postcode'
                )
                ->addColumn(
                    'phone',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Phone'
                )
                ->addColumn(
                    'fax',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Fax'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Updated At'
                )
                ->addColumn(
                    'instructions',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Instructions'
                )
                ->addColumn(
                    'county_code',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false, 'default' => ''],
                    'County code'
                )
                ->addColumn(
                    'brands',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Brand information for the address'
                )
                ->addColumn(
                    'is_registered',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false],
                    'Flag to say if this address is a registered type address'
                )
                ->addColumn(
                    'is_invoice',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false],
                    'Flag to say if this address is an invoice type address'
                )
                ->addColumn(
                    'is_delivery',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false],
                    'Flag to say if this address is a delivery type address'
                )
                ->addColumn(
                    'mobile_number',
                    Table::TYPE_TEXT,
                    15,
                    ['nullable' => false],
                    'Mobile Phone'
                )
                ->addColumn(
                    'default_location_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Location Code'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['erp_code', 'erp_customer_group_code']),
                    ['erp_code', 'erp_customer_group_code']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'erp_customer_group_code',
                        $installer->getTable('ecc_erp_account'),
                        'erp_code'
                    ),
                    'erp_customer_group_code',
                    $installer->getTable('ecc_erp_account'),
                    'erp_code',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_account_address_store');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'Entity ID'
                )
                ->addColumn(
                    'erp_customer_group_address',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Erp Account Code'
                )
                ->addColumn(
                    'store',
                    Table::TYPE_SMALLINT,
                    5,
                    ['unsigned' => true, 'nullable' => false],
                    'Store ID'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['erp_customer_group_address', 'store']),
                    ['erp_customer_group_address', 'store']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'erp_customer_group_address',
                        $installer->getTable('ecc_erp_account_address'),
                        'entity_id'
                    ),
                    'erp_customer_group_address',
                    $installer->getTable('ecc_erp_account_address'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_account_group_currency');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'erp_account_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'is_default',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Default Currency'
                )
                ->addColumn(
                    'currency_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Currency Code'
                )
                ->addColumn(
                    'onstop',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'On Stop'
                )
                ->addColumn(
                    'balance',
                    Table::TYPE_DECIMAL,
                    '16,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Balance'
                )
                ->addColumn(
                    'credit_limit',
                    Table::TYPE_DECIMAL,
                    '16,4',
                    ['default' => '0.0000'],
                    'Credit Limit'
                )
                ->addColumn(
                    'unallocated_cash',
                    Table::TYPE_DECIMAL,
                    '16,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Unallocated Cash'
                )
                ->addColumn(
                    'min_order_amount',
                    Table::TYPE_DECIMAL,
                    '16,4',
                    ['default' => '0.0000'],
                    'Minimum Order Amount'
                )
                ->addColumn(
                    'last_payment_date',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Last Payment Date'
                )
                ->addColumn(
                    'last_payment_value',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Last Payment Value'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Updated At'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'erp_account_id',
                        $installer->getTable('ecc_erp_account'),
                        'entity_id'
                    ),
                    'erp_account_id',
                    $installer->getTable('ecc_erp_account'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_account_group_hierarchy');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'parent_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Parent ID'
                )
                ->addColumn(
                    'child_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Child ID'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    1,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Updated At'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'parent_id',
                        $installer->getTable('ecc_erp_account'),
                        'entity_id'
                    ),
                    'parent_id',
                    $installer->getTable('ecc_erp_account'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'child_id',
                        $installer->getTable('ecc_erp_account'),
                        'entity_id'
                    ),
                    'child_id',
                    $installer->getTable('ecc_erp_account'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_account_group_store');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'Entity ID'
                )
                ->addColumn(
                    'erp_customer_group',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Erp Group ID'
                )
                ->addColumn(
                    'store',
                    Table::TYPE_SMALLINT,
                    5,
                    ['unsigned' => true, 'nullable' => false],
                    'Store ID'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['erp_customer_group', 'store']),
                    ['erp_customer_group', 'store']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['store']),
                    ['store']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'erp_customer_group',
                        $installer->getTable('ecc_erp_account'),
                        'entity_id'
                    ),
                    'erp_customer_group',
                    $installer->getTable('ecc_erp_account'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_account_sku');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'Entity ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )
                ->addColumn(
                    'customer_group_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Customer Group ID'
                )
                ->addColumn(
                    'sku',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Sku'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Description'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['product_id', 'customer_group_id', 'sku']),
                    ['product_id', 'customer_group_id', 'sku']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['customer_group_id', 'product_id']),
                    ['product_id', 'customer_group_id', 'sku']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'product_id',
                        $installer->getTable('catalog_product_entity'),
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_customer_return');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'erp_returns_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Erp Returns Number'
                )
                ->addColumn(
                    'rma_date',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'RMA Date'
                )
                ->addColumn(
                    'returns_status',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'returnsStatus'
                )
                ->addColumn(
                    'customer_reference',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'customerReference'
                )
                ->addColumn(
                    'email_address',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email Address'
                )
                ->addColumn(
                    'reason_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'reason Code'
                )
                ->addColumn(
                    'address_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'addressCode'
                )
                ->addColumn(
                    'customer_name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'customerName'
                )
                ->addColumn(
                    'credit_invoice_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'creditInvoiceNumber'
                )
                ->addColumn(
                    'rma_case_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'rmaCaseNumber'
                )
                ->addColumn(
                    'rma_contact',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'rmaContact'
                )
                ->addColumn(
                    'note_text',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'note Text'
                )
                ->addColumn(
                    'previous_erp_data',
                    Table::TYPE_TEXT,
                    '4G',
                    [],
                    'Previous ERP Data'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Customer ID'
                )
                ->addColumn(
                    'erp_account_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'erp account id'
                )
                ->addColumn(
                    'is_global',
                    Table::TYPE_INTEGER,
                    11,
                    ['default' => '0'],
                    'Is Return global to ERP Account'
                )
                ->addColumn(
                    'actions',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Actions'
                )
                ->addColumn(
                    'submitted',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Submitted'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Store ID'
                )
                ->addColumn(
                    'erp_sync_action',
                    Table::TYPE_TEXT,
                    1,
                    ['nullable' => false, 'default' => ''],
                    'ERP Sync Action'
                )
                ->addColumn(
                    'erp_sync_status',
                    Table::TYPE_TEXT,
                    1,
                    ['nullable' => false, 'default' => 'N'],
                    'ERP Sync Status'
                )
                ->addColumn(
                    'last_erp_status',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'ERP Sync Last Status'
                )
                ->addColumn(
                    'last_erp_error_description',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'ERP Sync Last Description'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['customer_id']),
                    ['customer_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'erp_account_id',
                        $installer->getTable('ecc_erp_account'),
                        'entity_id'
                    ),
                    'erp_account_id',
                    $installer->getTable('ecc_erp_account'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_customer_return_line');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'erp_line_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'ERP line number'
                )
                ->addColumn(
                    'return_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Return ID'
                )
                ->addColumn(
                    'product_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Product Code'
                )
                ->addColumn(
                    'revision_level',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Revision Level'
                )
                ->addColumn(
                    'unit_of_measure_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Unit Of Measure Code'
                )
                ->addColumn(
                    'qty_ordered',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Qty Ordered'
                )
                ->addColumn(
                    'qty_returned',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Qty Returned'
                )
                ->addColumn(
                    'returns_status',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'returnsStatus'
                )
                ->addColumn(
                    'order_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'order Number'
                )
                ->addColumn(
                    'order_line',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'order Line'
                )
                ->addColumn(
                    'order_release',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'order Release'
                )
                ->addColumn(
                    'shipment_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'shipment number'
                )
                ->addColumn(
                    'invoice_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'invoice Number'
                )
                ->addColumn(
                    'serial_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'serial Number'
                )
                ->addColumn(
                    'reason_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'reason Code'
                )
                ->addColumn(
                    'note_text',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'note Text'
                )
                ->addColumn(
                    'to_be_deleted',
                    Table::TYPE_TEXT,
                    1,
                    ['default' => 'N'],
                    'To Be deleted Text'
                )
                ->addColumn(
                    'previous_erp_data',
                    Table::TYPE_TEXT,
                    '4G',
                    ['nullable' => false],
                    'Previous ERP Data'
                )
                ->addColumn(
                    'actions',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Actions'
                )
                ->addColumn(
                    'invoice_line',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Invoice Line'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['return_id']),
                    ['return_id']
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_customer_return_attachment');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'return_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Return ID'
                )
                ->addColumn(
                    'line_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Line ID'
                )
                ->addColumn(
                    'attachment_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Attachment ID'
                )
                ->addColumn(
                    'to_be_deleted',
                    Table::TYPE_TEXT,
                    1,
                    ['default' => 'N'],
                    'To Be deleted'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['attachment_id']),
                    ['attachment_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'return_id',
                        $installer->getTable('ecc_customer_return'),
                        'id'
                    ),
                    'return_id',
                    $installer->getTable('ecc_customer_return'),
                    'id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'line_id',
                        $installer->getTable('ecc_customer_return_line'),
                        'id'
                    ),
                    'line_id',
                    $installer->getTable('ecc_customer_return_line'),
                    'id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_entity_register');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'row_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'Row ID'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Group ID'
                )
                ->addColumn(
                    'child_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Group ID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'modified_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Modified At'
                )
                ->addColumn(
                    'manually_modified_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Modified At'
                )
                ->addColumn(
                    'to_be_deleted',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'To Be deleted flag'
                )
                ->addColumn(
                    'details',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Entity Register Details'
                )
                ->addColumn(
                    'is_dirty',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false],
                    'Flag to say if this row is dirty and needs updating'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_catalog_category_entity');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'Entity ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'magento_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Magento ID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Updated At'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['erp_code']),
                    ['erp_code']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'magento_id',
                        $installer->getTable('catalog_category_entity'),
                        'entity_id'
                    ),
                    'magento_id',
                    $installer->getTable('catalog_category_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_cardtype');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'payment_method',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'payment method'
                )
                ->addColumn(
                    'magento_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'magento code'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_country');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'magento_id',
                    Table::TYPE_TEXT,
                    5,
                    ['nullable' => false],
                    'Magento ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_currency');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'magento_id',
                    Table::TYPE_TEXT,
                    5,
                    ['nullable' => false],
                    'Magento ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_orderstatus');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false, 'default' => 'processing'],
                    'Order status'
                )
                ->addColumn(
                    'state',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false, 'default' => 'processing'],
                    'Order State'
                )
                ->addColumn(
                    'sou_trigger',
                    Table::TYPE_TEXT,
                    50,
                    [],
                    'Sou Trigger'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_payment');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'magento_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Magento Code'
                )
                ->addColumn(
                    'payment_collected',
                    Table::TYPE_TEXT,
                    1,
                    [],
                    'payment collected'
                )
                ->addColumn(
                    'gor_trigger',
                    Table::TYPE_TEXT,
                    90,
                    [],
                    'Gor Trigger'
                )
                ->addColumn(
                    'gor_online_prevent_repricing',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Prevent Repricing for GOR for this Payment Method when order placed online?'
                )
                ->addColumn(
                    'gor_offline_prevent_repricing',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Prevent Repricing for GOR for this Payment Method when order placed offline?'
                )
                ->addColumn(
                    'bsv_online_prevent_repricing',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Prevent Repricing for BSV for this Payment Method when order placed online?'
                )
                ->addColumn(
                    'bsv_offline_prevent_repricing',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Prevent Repricing for BSV for this Payment Method when order placed offline?'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_remotelinks');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'pattern_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'primary' => true],
                    'Pattern Code'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Name'
                )
                ->addColumn(
                    'url_pattern',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Url Pattern'
                )
                ->addColumn(
                    'http_authorization',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Http Authorization'
                )
                ->addColumn(
                    'auth_user',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Auth User'
                )
                ->addColumn(
                    'auth_password',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Auth Password'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['pattern_code'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['pattern_code'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_erp_mapping_shippingmethod');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'shipping_method',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Shipping Method'
                )
                ->addColumn(
                    'shipping_method_code',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Shipping Method Code'
                )
                ->addColumn(
                    'erp_code',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_indexer_product');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'index_data_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Data Id'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created Date'
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_location');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'code'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Name'
                )
                ->addColumn(
                    'address1',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Address 1'
                )
                ->addColumn(
                    'address2',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Address 2'
                )
                ->addColumn(
                    'address3',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Address 3'
                )
                ->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'City'
                )
                ->addColumn(
                    'county',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'County'
                )
                ->addColumn(
                    'county_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'County code'
                )
                ->addColumn(
                    'country',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Country'
                )
                ->addColumn(
                    'postcode',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Postcode'
                )
                ->addColumn(
                    'telephone_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Phone'
                )
                ->addColumn(
                    'fax_number',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Fax'
                )
                ->addColumn(
                    'email_address',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Updated At'
                )
                ->addColumn(
                    'dummy',
                    Table::TYPE_SMALLINT,
                    6,
                    ['nullable' => false, 'default' => '0'],
                    'Dummy record flag'
                )
                ->addColumn(
                    'source',
                    Table::TYPE_TEXT,
                    3,
                    ['nullable' => false, 'default' => ''],
                    'Data Source'
                )
                ->addColumn(
                    'mobile_number',
                    Table::TYPE_TEXT,
                    15,
                    ['nullable' => false, 'default' => ''],
                    'Data Source'
                )
                ->addColumn(
                    'sort_order',
                    Table::TYPE_INTEGER
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['code']),
                    ['code']
                );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ecc_location_link');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,],
                    'ID'
                )
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Linked Entity ID'
                )
                ->addColumn(
                    'entity_type',
                    Table::TYPE_TEXT,
                    15,
                    ['nullable' => false],
                    'Linked Entity Type'
                )
                ->addColumn(
                    'location_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Erp Code'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Updated At'
                )
                ->addColumn(
                    'link_type',
                    Table::TYPE_TEXT,
                    1,
                    ['nullable' => false, 'default' => ''],
                    'Link Type'
                );

            $installer->getConnection()->createTable($table);
        }

        //install ecc_location_product
        $installer->getConnection()->dropTable('ecc_location_product');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_location_product')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );

        $table->addColumn('product_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Product ID'
        );

        $table->addColumn('location_code', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Location Code'
        );

        $table->addColumn('stock_status', Table::TYPE_TEXT, 255, array(), 'Stock Status');
        $table->addColumn('free_stock', Table::TYPE_DECIMAL, '16,4', array(), 'Free Stock');
        $table->addColumn('minimum_order_qty', Table::TYPE_DECIMAL, '16,4', array(), 'Minimum Order Qty');
        $table->addColumn('maximum_order_qty', Table::TYPE_DECIMAL, '16,4', array(), 'Maximum Order Qty');
        $table->addColumn('lead_time_days', Table::TYPE_INTEGER, 10, array(), 'Lead Time Days');
        $table->addColumn('lead_time_text', Table::TYPE_TEXT, 255, array(), 'Lead Time Text');
        $table->addColumn('supplier_brand', Table::TYPE_TEXT, 255, array(), 'Supplier Brand');
        $table->addColumn('tax_code', Table::TYPE_TEXT, 255, array(), 'Tax Code');
        $table->addColumn('manufacturers', Table::TYPE_TEXT, '64k', array(), 'Manufacturers');

        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, array(
            'default' => 0
        ), 'Created At'
        );
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, array(
            'default' => 0
        ), 'Updated At'
        );
        $table->addIndex(
            $installer->getIdxName(
                'ecc_location_product', array('product_id', 'location_code')
            ), array('product_id', 'location_code')
        );
        $installer->getConnection()->createTable($table);


        //install ecc_location_product_currency
        $installer->getConnection()->dropTable('ecc_location_product_currency');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_location_product_currency')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );

        $table->addColumn('product_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Product ID'
        );

        $table->addColumn('location_code', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Location Code'
        );

        $table->addColumn('currency_code', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Currency Code'
        );

        $table->addColumn('cost_price', Table::TYPE_DECIMAL, '16,4', array(), 'Cost Price');
        $table->addColumn('base_price', Table::TYPE_DECIMAL, '16,4', array(), 'Base Price');

        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, array(
            'default' => 0
        ), 'Created At'
        );
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, array(
            'default' => 0
        ), 'Updated At'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_location_product_currency'),
                ['location_code','currency_code']
            ),
            ['location_code','currency_code']
        );
        $installer->getConnection()->createTable($table);

        //install ecc_message_log
        $installer->getConnection()->dropTable('ecc_message_log');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_message_log')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');

        $table->addColumn('message_parent', Table::TYPE_TEXT, 10, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Message Parent');

        $table->addColumn('message_category', Table::TYPE_TEXT, 10, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Message Category');

        $table->addColumn('message_type', Table::TYPE_TEXT, 5, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Message Type');

        $table->addColumn('message_subject', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Message Subject');

        $table->addColumn('message_secondary_subject', Table::TYPE_TEXT, '64k', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Message Secondary Subject');

        $table->addColumn('start_datestamp', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Start Datestamp');

        $table->addColumn('end_datestamp', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'End Datestamp');

        $table->addColumn('duration', Table::TYPE_INTEGER, 5, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Message Duration in seconds');

        $table->addColumn('message_status', Table::TYPE_INTEGER, 2, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Message Status');

        $table->addColumn('status_code', Table::TYPE_TEXT, 4, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Status Code');

        $table->addColumn('status_description', Table::TYPE_TEXT, '64k', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Status Code');

        $table->addColumn('xml_in', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Xml In');

        $table->addColumn('xml_out', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Xml Out');

        $table->addColumn('cached', Table::TYPE_TEXT, 10, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Cached');

        $table->addColumn('url', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'url');
        $table->addColumn('store', Table::TYPE_TEXT, 100, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Store');

        $table->addColumn('erp_url', Table::TYPE_TEXT, 100, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'ERP URL request sent to');
        $installer->getConnection()->createTable($table);

        //install ecc_message_queue
        $installer->getConnection()->dropTable('ecc_message_queue');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_message_queue')
        );

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('message_id', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => true,
        ), 'Message Id');

        $table->addColumn('message_category', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Message Category');
        $table->addColumn('created_at', Table::TYPE_DECIMAL, '14,4', array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Magento ID');
        $installer->getConnection()->createTable($table);

        // install ecc_syn_log
        $installer->getConnection()->dropTable('ecc_syn_log');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_syn_log')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Row ID');

        $table->addColumn('message', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Message');

        $table->addColumn('from_date', Table::TYPE_TIMESTAMP, null, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'From Date');


        $table->addColumn('types', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Types');

        $table->addColumn('brands', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Brands');

        $table->addColumn('languages', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Languages');

        $table->addColumn('created_by_id', Table::TYPE_INTEGER, 10, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Created By');

        $table->addColumn('created_by_name', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Created By Name');

        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'default' => 0
        ), 'Created At');

        $table->addColumn('is_auto', Table::TYPE_BOOLEAN, null, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'default' => false
        ), 'Flag to say if this syn was triggered from the auto syn logger');
        $installer->getConnection()->createTable($table);

        //install ecc_access_right
        $installer->getConnection()->dropTable($installer->getTable('ecc_access_right'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_access_right'));
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');
        $table->addColumn('entity_name', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Erp Code');
        $table->addColumn('type', Table::TYPE_TEXT, 30, array(
            'nullable' => true,
            'unsigned' => true,
            'default' => 'customer',
        ), 'Access Right Type');

        $installer->getConnection()->createTable($table);

        // install ecc_access_group
        $installer->getConnection()->dropTable('ecc_access_group');
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_access_group'));
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');
        $table->addColumn('entity_name', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Entity Name');
        $table->addColumn('erp_account_id', Table::TYPE_INTEGER, null, array(
            'nullable' => true,
            'unsigned' => true,
            'default' => null,
        ), 'ERP Account ID');
        $table->addColumn('type', Table::TYPE_TEXT, 30, array(
            'nullable' => true,
            'unsigned' => true,
            'default' => 'customer',
        ), 'Access Group Type');
        $installer->getConnection()->createTable($table);

        //install ecc_access_group_right
        $installer->getConnection()->dropTable($installer->getTable('ecc_access_group_right'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_access_group_right'));
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');
        $table->addColumn('right_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Right ID');
        $table->addColumn('group_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Group ID');
        $installer->getConnection()->createTable($table);

        //install ecc_access_right_element
        $installer->getConnection()->dropTable($installer->getTable('ecc_access_right_element'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_access_right_element'));
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');

        $table->addColumn('right_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Right ID');

        $table->addColumn('element_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Element ID');
        $installer->getConnection()->createTable($table);

        // install ecc_access_element
        $installer->getConnection()->dropTable($installer->getTable('ecc_access_element'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_access_element'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');

        $table->addColumn('module', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Module');

        $table->addColumn('controller', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Controller');

        $table->addColumn('action', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Action');

        $table->addColumn('block', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Block');

        $table->addColumn('action_type', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Action Type ');

        $table->addColumn('excluded', Table::TYPE_INTEGER, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'default' => 0
        ), 'Excluded ');
        $table->addColumn('portal_excluded', Table::TYPE_INTEGER, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'default' => 0
        ), 'Excluded From B2b Portal');
        $installer->getConnection()->createTable($table);

        //install ecc_access_group_customer

        $installer->getConnection()->dropTable($installer->getTable('ecc_access_group_customer'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_access_group_customer'));

        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');
        $table->addColumn('group_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Group ID');
        $table->addColumn('customer_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Customer ID');

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('ecc_access_right', 'entity_id', 'ecc_access_right_element', 'right_id'),
            $installer->getTable('ecc_access_right_element'),
            'right_id',
            $installer->getTable('ecc_access_right'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('ecc_access_group', 'entity_id', 'ecc_access_group_customer', 'group_id'),
            $installer->getTable('ecc_access_group_customer'),
            'group_id',
            $installer->getTable('ecc_access_group'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('customer_entity', 'entity_id', 'ecc_access_group_customer', 'customer_id'),
            $installer->getTable('ecc_access_group_customer'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('ecc_access_right', 'entity_id', 'ecc_access_group_right', 'right_id'),
            $installer->getTable('ecc_access_group_right'),
            'right_id',
            $installer->getTable('ecc_access_right'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('ecc_access_group', 'entity_id', 'ecc_access_group_right', 'group_id'),
            $installer->getTable('ecc_access_group_right'),
            'group_id',
            $installer->getTable('ecc_access_group'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('ecc_erp_account', 'entity_id', 'ecc_access_group', 'erp_account_id'),
            $installer->getTable('ecc_access_group'),
            'erp_account_id',
            $installer->getTable('ecc_erp_account'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        //install ecc_erp_mapping_language
        $installer->getConnection()->dropTable('ecc_erp_mapping_language');
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_language'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('erp_code', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Erp Code');
        $table->addColumn('languages', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Magento Local Languages');
        $table->addColumn('language_codes', Table::TYPE_TEXT, 5000, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Magento Local Language Codes');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
            'default' => 0
        ), 'Store id value');
        $installer->getConnection()->createTable($table);

        //install ecc_file
        $installer->getConnection()->dropTable($installer->getTable('ecc_file'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_file'));

        $table->addColumn(
            'id', Table::TYPE_INTEGER, 10,
            array(
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ), 'ID'
        );

        $table->addColumn(
            'erp_id', Table::TYPE_TEXT, 255,
            array(
                'identity' => false,
                'nullable' => false,
                'primary' => false,
            ), 'Erp ID'
        );

        $table->addColumn(
            'filename', Table::TYPE_TEXT, 255,
            array(
                'identity' => false,
                'nullable' => false,
                'primary' => false,
            ), 'Filename'
        );

        $table->addColumn(
            'description', Table::TYPE_TEXT, '', array(
            'nullable' => true,
        ), 'Description'
        );
        $table->addColumn(
            'url', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Url'
        );

        $table->addColumn(
            'customer_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => true,
            'unsigned' => true,
        ), 'Customer ID'
        );

        $table->addColumn(
            'erp_account_id', Table::TYPE_INTEGER, 10,
            array(
                'nullable' => true,
                'unsigned' => true,
            ), 'ERP Account ID'
        );

        $table->addColumn(
            'source', Table::TYPE_TEXT, 5, array(
            'nullable' => true,
        ), 'File Source'
        );

        $table->addColumn(
            'created_at', Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default' => 0
        ), 'Created At'
        );

        $table->addColumn(
            'updated_at', Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default' => 0
        ), 'Updated At'
        );


        $table->addColumn(
            'action', Table::TYPE_TEXT, 1, array(
            'nullable' => false,
            'default' => ''
        ), 'Action'
        );

        $table->addColumn(
            'previous_data', Table::TYPE_TEXT, '4G', array(
            'nullable' => false,
            'default' => ''
        ), 'Previous Data'
        );


        $table->addForeignKey(
            $installer->getFkName($installer->getTable('ecc_erp_account'),'entity_id',$installer->getTable('ecc_file'),'erp_account_id'),
            'erp_account_id',
            $installer->getTable('ecc_erp_account'),
            'entity_id',
            Table::ACTION_SET_NULL,
            Table::ACTION_NO_ACTION);

        $table->addForeignKey(
            $installer->getFkName($installer->getTable('customer_entity'),'entity_id',$installer->getTable('ecc_file'),'customer_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_SET_NULL,
            Table::ACTION_NO_ACTION);
        $installer->getConnection()->createTable($table);

        //install ecc_erp_mapping_erporderstatus
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_erporderstatus'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_erporderstatus'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 55, array(
            'nullable' => false,
        ), 'Erp Order Code');
        $table->addColumn('status', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Erp Order status');
        $table->addColumn('state', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Erp Order State');
        $table->addColumn('store_id', Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');
        $installer->getConnection()->createTable($table);

        //install ecc_erp_mapping_erpquotestatus
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_erpquotestatus'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_erpquotestatus'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 55, array(
            'nullable' => false,
        ), 'Erp Quote Code');
        $table->addColumn('status', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Erp Quote status');
        $table->addColumn('state', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Erp Quote State');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');

        $installer->getConnection()->createTable($table);

        //install ecc_erp_mapping_invoicestatus

        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_invoicestatus'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_invoicestatus'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 55, array(
            'nullable' => false,
        ), 'Erp Code');
        $table->addColumn('status', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Invoice status');
        $table->addColumn('state', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Invoice State');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');

        $installer->getConnection()->createTable($table);

        //install ecc_erp_mapping_reasoncode
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_reasoncode'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_reasoncode'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 20, array(
            'nullable' => false,
        ), 'Reason Code');
        $table->addColumn('description', Table::TYPE_TEXT, 200, array(
            'nullable' => false
        ), 'Reason Code Description');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');
        $table->addColumn('type', Table::TYPE_TEXT, 1, array(
            'nullable' => true,
            'primary' => false,
        ), 'Not present or blank – all types, B – B2B sites only, C – B2C sites only');
        $installer->getConnection()->createTable($table);


        //install ecc_erp_mapping_reasoncode_accounts
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_reasoncode_accounts'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_reasoncode_accounts'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 20, array(
            'nullable' => false,
        ), 'Reason Code');
        $table->addColumn('erp_account', Table::TYPE_TEXT, null, array(
            'nullable' => false
        ), 'ERP Account');
        $installer->getConnection()->createTable($table);

        //install ecc_erp_mapping_rmastatus
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_rmastatus'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_rmastatus'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 55, array(
            'nullable' => false,
        ), 'Erp Code');
        $table->addColumn('status', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'RMA status');
        $table->addColumn('state', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'RMA State');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');
        $table->addColumn('status_text', Table::TYPE_TEXT, '4G', array(
            'nullable' => true,
        ), 'RMA Status Text displayed to customer');
        $table->addColumn('is_rma_deleted', Table::TYPE_BOOLEAN, null, array(
            'nullable' => true,
            'primary' => false,
            'default' => false
        ), 'Is RMA deleted when changed to this status');

        $installer->getConnection()->createTable($table);

        //install ecc_erp_mapping_servicecallstatus
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_servicecallstatus'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_servicecallstatus'));

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 55, array(
            'nullable' => false,
        ), 'Erp Code');
        $table->addColumn('status', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Service Call status');
        $table->addColumn('state', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'open'
        ), 'Service Call State');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');

        $installer->getConnection()->createTable($table);

        //install ecc_faq and ecc_faq_vote
        $installer->getConnection()->dropTable($installer->getTable('ecc_faq'));
        $installer->getConnection()->dropTable($installer->getTable('ecc_faq_vote'));

        $table = $installer->getConnection()
            ->newTable($installer->getTable('ecc_faq'))
            ->addColumn(
                'faqs_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'identity' => true, 'nullable' => false, 'primary' => true,],
                'Entity ID'
            )
            ->addColumn(
                'weight',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false,],
                'Weight'
            )
            ->addColumn(
                'question',
                Table::TYPE_TEXT,
                '1M',
                ['nullable' => true,],
                'Question'
            )
            ->addColumn(
                'answer',
                Table::TYPE_TEXT,
                '1M',
                ['nullable' => true, 'default' => null,],
                'Answer'
            )
            ->addColumn(
                'stores',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null,],
                'Stores'
            )
            ->addColumn(
                'useful',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => 0],
                'Useful votes'
            )
            ->addColumn(
                'useless',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => 0],
                'Useless votes'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                array('nullable' => true, 'default' => null,),
                'Creation Time'
            )
            ->addColumn(
                'keywords',
                Table::TYPE_TEXT,
                null,
                array('nullable' => true, 'default' => null,),
                'Keywords'
            )
            ->setComment('Faqs item');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('ecc_faq_vote'))
            ->addColumn(
                'vote_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'identity' => true, 'nullable' => false, 'primary' => true,],
                'Entity id'
            )
            ->addColumn(
                'faqs_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true,],
                'F.A.Q. ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_faq_vote'),
                    ['faqs_id']
                ),
                'faqs_id'
            )
            ->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('ecc_faq'),
                    'faqs_id',
                    $installer->getTable('ecc_faq_vote'),
                    'faqs_id'
                ),
                'faqs_id',
                $installer->getTable('ecc_faq'),
                'faqs_id',
                Table::ACTION_SET_NULL,
                Table::ACTION_RESTRICT
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,],
                'Customer ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('ecc_faq_vote'),
                    ['customer_id']
                ),
                'customer_id'
            )
            ->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    $installer->getTable('ecc_faq_vote'),
                    'customer_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_NO_ACTION,
                Table::ACTION_RESTRICT
            )
            ->addColumn(
                'value',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false,],
                'Value'
            );

        $installer->getConnection()->createTable($table);

        //install ecc_contract
        $installer->getConnection()->dropTable('ecc_contract');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_contract')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );

        $table->addColumn('sales_rep', Table::TYPE_TEXT, 255, array(), 'Sales Rep');

        $table->addColumn('contact_name', Table::TYPE_TEXT, 255, array(), 'Contact Name');

        $table->addColumn('purchase_order_number', Table::TYPE_TEXT, 255, array(), 'PO Humber');
        $table->addColumn('last_modified_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
            //'default' => 0
        ), 'Last Modified Date');

        $table->addColumn('contract_status', Table::TYPE_TEXT, 1, array(), 'Contract Status');
        $table->addColumn('last_used_time', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Last Used Time');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_contract'),
                array('list_id')
            ),
            'list_id');
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_contract'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_contract_product
        $installer->getConnection()->dropTable('ecc_contract_product');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_contract_product')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('contract_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Contract ID'
        );
        $table->addColumn('list_product_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List Product ID'
        );
        $table->addColumn('line_number', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Contract Line Number'
        );

        $table->addColumn('part_number', Table::TYPE_TEXT, 100, array(), 'Contract Part Number');

        $table->addColumn('status', Table::TYPE_TEXT, 1, array(), 'Contract Line Status');

        $table->addColumn('start_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Contract Line Start Date');
        $table->addColumn('end_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Contract Line End Date');
        $table->addColumn('min_order_qty', Table::TYPE_FLOAT, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Minimum Order Qty');
        $table->addColumn('max_order_qty', Table::TYPE_FLOAT, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Maximum Order Qty');
        $table->addColumn('is_discountable', Table::TYPE_BOOLEAN, null, array(
            'nullable' => true,
        ), 'Contract Product Is Discountable');

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_contract_product'),
                array('contract_id', 'list_product_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('contract_id', 'list_product_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_contract_product'),
                array('list_product_id')
            ),
            'list_product_id'
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_contract'),
                'id',
                $installer->getTable('ecc_contract_product'),
                'contract_id'),
            'contract_id',
            $installer->getTable('ecc_contract'), 'id',
            Table::ACTION_CASCADE
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list_product'),
                'id',
                $installer->getTable('ecc_contract_product'),
                'list_product_id'),
            'list_product_id',
            $installer->getTable('ecc_list_product'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list
        $installer->getConnection()->dropTable('ecc_list');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'List ECC ID'
        );

        $table->addColumn('erp_code', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'List Erp Code'
        );
        $table->addColumn('type', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'List Type'
        );
        $table->addColumn('title', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'List Title'
        );
        $table->addColumn('label', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Label for List'
        );
        $table->addColumn('settings', Table::TYPE_TEXT, 10, array(
            'nullable' => false,
        ), 'List Status Flags'
        );
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
        $table->addColumn('active', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '0'
        ), 'Active');
        $table->addColumn('source', Table::TYPE_TEXT, 30, array(
            'nullable' => false,
            'default' => 'web'
        ), 'Source (Customer / Web / ERP)');
        $table->addColumn('default_currency', Table::TYPE_TEXT, 4, array(
            'nullable' => true,
        ), 'Default Currency Code');
        $table->addColumn('conditions', Table::TYPE_TEXT, '4G', array(
            'nullable' => true,
            'default' => false
        ), 'Product Filter Conditions');
        $table->addColumn('erp_account_link_type', Table::TYPE_TEXT, 1, array(
            'nullable' => false,
            'default' => 'N'
        ), 'How are ERP Accounts linked?');
        $table->addColumn('is_dummy', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => 0
        ), 'Was this created by a sub message? (e.g. CUS)');
        $table->addColumn('notes', Table::TYPE_TEXT, '4G', array(
            'nullable' => true,
            'default' => false
        ), 'Notes Field');
        $table->addColumn('erp_override', Table::TYPE_TEXT, '4G', array(
            'nullable' => true,
            'default' => false
        ), 'Serialized array of data the ERP cannot override');
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
        $table->addColumn('description', Table::TYPE_TEXT, '4G', array(
            'nullable' => true,
            'default' => false
        ), 'Description');
        $table->addColumn('erp_accounts_exclusion', Table::TYPE_TEXT, 1, array(
            'nullable' => false,
            'default' => 'N'
        ), 'Erp Accounts Exclusion');
        $table->addColumn('owner_id', Table::TYPE_INTEGER, 11, array(
            'nullable' => true,
            'default' => null
        ), 'Erp Owner Id');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list'),
                array('active', 'start_date', 'end_date')
            ),
            array('active', 'start_date', 'end_date')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list'),
                array('type')
            ),
            'type'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list'),
                array('erp_account_link_type')
            ),
            'erp_account_link_type'
        );
        $installer->getConnection()->createTable($table);


        //install ecc_list_address
        $installer->getConnection()->dropTable('ecc_list_address');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_address')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('address_code', Table::TYPE_TEXT, 255, array(), 'ERP Address Code'
        );
        $table->addColumn('purchase_order_number', Table::TYPE_TEXT, 255, array(), 'Address PO Humber'
        );
        $table->addColumn('name', Table::TYPE_TEXT, 255, array(), 'Address Name'
        );
        $table->addColumn('address1', Table::TYPE_TEXT, 255, array(), 'Line 1'
        );
        $table->addColumn('address2', Table::TYPE_TEXT, 255, array(), 'Line 2'
        );
        $table->addColumn('address3', Table::TYPE_TEXT, 255, array(), 'Line 3'
        );
        $table->addColumn('city', Table::TYPE_TEXT, 255, array(), 'City'
        );

        $table->addColumn('county', Table::TYPE_TEXT, 255, array(), 'County'
        );

        $table->addColumn('country', Table::TYPE_TEXT, 255, array(), 'Country'
        );
        $table->addColumn('postcode', Table::TYPE_TEXT, 255, array(), 'Postcode'
        );

        $table->addColumn('telephone_number', Table::TYPE_TEXT, 255, array(), 'Telephone Number'
        );
        $table->addColumn('mobile_number', Table::TYPE_TEXT, 255, array(), 'Mobile Number'
        );

        $table->addColumn('fax_number', Table::TYPE_TEXT, 255, array(), 'Fax Number'
        );

        $table->addColumn('email_address', Table::TYPE_TEXT, 255, array(), 'Email Address'
        );

        $table->addColumn('activation_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'List Address Activation Date');
        $table->addColumn('expiry_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'List Address Expiry Date');

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_address'),
                array('activation_date', 'expiry_date', 'address_code', 'list_id')
            ),
            array('activation_date', 'expiry_date', 'address_code', 'list_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_address'),
                array('list_id')
            ),
            'list_id'
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_address'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_address_restriction
        $installer->getConnection()->dropTable('ecc_list_address_restriction');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_address_restriction')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );

        $table->addColumn('restriction_type', Table::TYPE_TEXT, 10, array(
            'nullable' => false,
        ), 'Restriction Type'
        );

        $table->addColumn('address_id', Table::TYPE_INTEGER, 10, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Address Id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_address_restriction'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list_address'),
                'id',
                $installer->getTable('ecc_list_address_restriction'),
                'address_id'),
            'address_id',
            $installer->getTable('ecc_list_address'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_brand
        $installer->getConnection()->dropTable('ecc_list_brand');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_brand')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('company', Table::TYPE_TEXT, 255, array(), 'Company'
        );
        $table->addColumn('site', Table::TYPE_TEXT, 255, array(), 'Site'
        );
        $table->addColumn('warehouse', Table::TYPE_TEXT, 255, array(), 'Warehouse'
        );
        $table->addColumn('group', Table::TYPE_TEXT, 255, array(), 'Group'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_brand'),
                array('list_id')
            ),
            'list_id'
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_brand'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_customer
        $installer->getConnection()->dropTable('ecc_list_customer');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_customer')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('customer_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Customer ID'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_customer'),
                array('list_id', 'customer_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('list_id', 'customer_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_customer'),
                array('customer_id')
            ),
            'customer_id'
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('customer_entity'),
                'entity_id',
                $installer->getTable('ecc_list_customer'),
                'customer_id'),
            'customer_id',
            $installer->getTable('customer_entity'), 'entity_id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_customer'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_erp_account
        $installer->getConnection()->dropTable('ecc_list_erp_account');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_erp_account')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('erp_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'ERP Account ID'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_erp_account'),
                array('list_id', 'erp_account_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('list_id', 'erp_account_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_erp_account'),
                array('erp_account_id')
            ),
            'erp_account_id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_erp_account'),
                'entity_id',
                $installer->getTable('ecc_list_erp_account'),
                'erp_account_id'),
            'erp_account_id',
            $installer->getTable('ecc_erp_account'), 'entity_id',
            Table::ACTION_CASCADE);

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_erp_account'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_label
        $installer->getConnection()->dropTable('ecc_list_label');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_label')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('website_id', Table::TYPE_SMALLINT, 5, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Website ID'
        );
        $table->addColumn('store_group_id', Table::TYPE_SMALLINT, 5, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Store Group ID'
        );
        $table->addColumn('store_id', Table::TYPE_SMALLINT, 5, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Store ID'
        );
        $table->addColumn('label', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Store Specific Label'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_label'),
                array('list_id', 'store_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('list_id', 'store_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_label'),
                array('website_id')
            ),
            'website_id'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_label'),
                array('store_group_id')
            ),
            'store_group_id'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_label'),
                array('store_id')
            ),
            'store_id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('store_group'),
                'group_id',
                $installer->getTable('ecc_list_label'),
                'store_group_id'),
            'store_group_id',
            $installer->getTable('store_group'), 'group_id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('store'),
                'store_id',
                $installer->getTable('ecc_list_label'),
                'store_id'),
            'store_id',
            $installer->getTable('store'), 'store_id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('store_website'),
                'website_id',
                $installer->getTable('ecc_list_label'),
                'website_id'),
            'website_id',
            $installer->getTable('store_website'), 'website_id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_label'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_product
        $installer->getConnection()->dropTable('ecc_list_product');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_product')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('sku', Table::TYPE_TEXT, 100, array(
            'nullable' => false,
        ), 'Product SKU'
        );
        $table->addColumn('qty', Table::TYPE_FLOAT, 255, array(
            'nullable' => true,
        ), 'Product Qty'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_product'),
                array('list_id', 'sku'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('list_id', 'sku'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_product'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_product_price
        $installer->getConnection()->dropTable('ecc_list_product_price');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_product_price')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_product_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List Product ID'
        );
        $table->addColumn('currency', Table::TYPE_TEXT, 4, array(
            'nullable' => false,
        ), 'Currency Code'
        );
        $table->addColumn('price', Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => false,
        ), 'Product Price for this Currency'
        );
        $table->addColumn('price_breaks', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
            'default' => false
        ), 'Serialized array of Price Breaks');
        $table->addColumn('value_breaks', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
            'default' => false
        ), 'Serialized array of Value Breaks');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_product_price'),
                array('list_product_id')
            ),
            'list_product_id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list_product'),
                'id',
                $installer->getTable('ecc_list_product_price'),
                'list_product_id'),
            'list_product_id',
            $installer->getTable('ecc_list_product'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_store_group
        $installer->getConnection()->dropTable('ecc_list_store_group');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_store_group')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('store_group_id', Table::TYPE_SMALLINT, 5, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Store ID'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_store_group'),
                array('list_id', 'store_group_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('list_id', 'store_group_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_store_group'),
                array('store_group_id')
            ),
            'store_group_id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('store_group'),
                'group_id',
                $installer->getTable('ecc_list_store_group'),
                'store_group_id'),
            'store_group_id',
            $installer->getTable('store_group'), 'group_id',
            Table::ACTION_CASCADE);

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_store_group'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_list_website
        $installer->getConnection()->dropTable('ecc_list_website');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_list_website')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('list_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'List ID'
        );
        $table->addColumn('website_id', Table::TYPE_SMALLINT, 5, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Website ID'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_website'),
                array('list_id', 'website_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('list_id', 'website_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_list_website'),
                array('website_id')
            ),
            'website_id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('store_website'),
                'website_id',
                $installer->getTable('ecc_list_website'),
                'website_id'),
            'website_id',
            $installer->getTable('store_website'), 'website_id',
            Table::ACTION_CASCADE);

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_list'),
                'id',
                $installer->getTable('ecc_list_website'),
                'list_id'),
            'list_id',
            $installer->getTable('ecc_list'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_quote
        $installer->getConnection()->dropTable('ecc_quote');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_quote')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );

        $table->addColumn('status_id', Table::TYPE_TEXT, 30, array(
            'nullable' => true,
        ), 'Status ID'
        );
        $table->addColumn('expires', Table::TYPE_DATE, 255, array(
            'nullable' => true,
        ), 'Expiry Date'
        );
        $table->addColumn('created_at', Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default' => 0
        ), 'Created At'
        );
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default' => 0
        ), 'Updated At'
        );

        $table->addColumn('send_customer_reminders', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Send Reminder Emails - Customer');
        $table->addColumn('show_prices', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Show Prices');
        $table->addColumn('is_private', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ), 'Private Message');
        $table->addColumn('is_visible', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ), 'Visible Message');
        $table->addColumn('quote_number', Table::TYPE_TEXT, 100, array(
            'nullable' => true,
        ), 'ERP Quote Number');
        $table->addColumn('send_customer_comments', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Send Comment Emails - Customer');
        $table->addColumn('send_admin_reminders', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Send Reminder Emails - Admin');
        $table->addColumn('send_admin_comments', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Send Comment Emails - Admin');
        $table->addColumn('send_customer_updates', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Send Update Emails - Customer');
        $table->addColumn('send_admin_updates', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '1'
        ), 'Send Update Emails - Admin');
        $table->addColumn('erp_account_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => true,
            'identity' => false,
            'unsigned' => true,
            'primary' => false,
        ), 'Erp Account ID');
        $table->addColumn('is_global', Table::TYPE_INTEGER, 11, array(
            'nullable' => true,
            'identity' => false,

            'primary' => false,
            'default' => '0'
        ), 'Is Quote global to ERP Account');
        $table->addColumn('currency_code', Table::TYPE_TEXT, 20, array(
            'nullable' => false,
        ), 'Quote Currency Code');
        $table->addColumn('created_by', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Created By');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 10, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Store ID');
        $table->addColumn('contract_code', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'ECC Contract Code');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote'),
                array('erp_account_id')
            ),
            array('erp_account_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote'),
                array('quote_number')
            ),
            'quote_number'
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_erp_account'),
                'entity_id',
                $installer->getTable('ecc_quote'),
                'erp_account_id'),
            'erp_account_id',
            $installer->getTable('ecc_erp_account'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_quote_customer
        $installer->getConnection()->dropTable('ecc_quote_customer');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_quote_customer')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );

        $table->addColumn('quote_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Quote Id'
        );
        $table->addColumn('customer_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Customer Id'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote_customer'),
                array('quote_id')
            ),
            'quote_id'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote_customer'),
                array('customer_id')
            ),
            'customer_id'
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('customer_entity'),
                'entity_id',
                $installer->getTable('ecc_quote_customer'),
                'customer_id'),
            'customer_id',
            $installer->getTable('customer_entity'), 'entity_id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_quote'),
                'entity_id',
                $installer->getTable('ecc_quote_customer'),
                'quote_id'),
            'quote_id',
            $installer->getTable('ecc_quote'), 'entity_id',
            Table::ACTION_CASCADE);

        $installer->getConnection()->createTable($table);


        //install ecc_quote_note
        $installer->getConnection()->dropTable('ecc_quote_note');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_quote_note')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );
        $table->addColumn('quote_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Quote Id'
        );
        $table->addColumn('admin_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Admin User Id'
        );
        $table->addColumn('note', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false
        ), 'Note');
        $table->addColumn('created_at', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Created At');

        $table->addColumn('erp_ref', Table::TYPE_TEXT, 100, array(
            'nullable' => true,
        ), 'ERP Reference'
        );
        $table->addColumn('is_private', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ), 'Private Message');
        $table->addColumn('is_visible', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ), 'Visible Message');

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote_note'),
                array('quote_id')
            ),
            array('quote_id')
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_quote'),
                'entity_id',
                $installer->getTable('ecc_quote_note'),
                'quote_id'),
            'quote_id',
            $installer->getTable('ecc_quote'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_quote_product
        $installer->getConnection()->dropTable('ecc_quote_product');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_quote_product')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );
        $table->addColumn('quote_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Quote Id'
        );
        $table->addColumn('product_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Product Id'
        );
        $table->addColumn('orig_qty', Table::TYPE_INTEGER, 11, array(
            'identity' => false,
            'unsigned' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Original Quantity'
        );
        $table->addColumn('orig_price', Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => false,
        ), 'Original Price');
        $table->addColumn('new_qty', Table::TYPE_INTEGER, 11, array(
            'identity' => false,
            'unsigned' => false,
            'nullable' => true,
            'primary' => false,
        ), 'New Quantity'
        );
        $table->addColumn('new_price', Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => true,
        ), 'New Price');
        $table->addColumn('note', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false
        ), 'Note');


        $table->addColumn('erp_note_ref', Table::TYPE_TEXT, 100, array(
            'nullable' => true,
        ), 'ERP Reference'
        );
        $table->addColumn('options', Table::TYPE_TEXT, '4G', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
            'default' => false
        ), 'Product Options');
        $table->addColumn('erp_line_number', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
            'default' => ''
        ), 'ERP Line Number');
        $table->addColumn('location_code', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Location Code');
        $table->addColumn('contract_code', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'ECC Contract Code');


        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote_product'),
                array('quote_id')
            ),
            array('quote_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_quote_product'),
                array('product_id')
            ),
            array('product_id')
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_quote'),
                'entity_id',
                $installer->getTable('ecc_quote_product'),
                'quote_id'),
            'quote_id',
            $installer->getTable('ecc_quote'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_reports_raw_data
        $installer->getConnection()->dropTable('ecc_reports_raw_data');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_reports_raw_data')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );

        $table->addColumn('store', Table::TYPE_TEXT, 100, array(
            'nullable' => false,
        ), 'Store id'
        );
        $table->addColumn('message_type', Table::TYPE_TEXT, 5, array(
            'nullable' => true,
        ), 'Message type'
        );
        $table->addColumn('message_status', Table::TYPE_TEXT, 20, array(
            'nullable' => true,
        ), 'Message status'
        );
        $table->addColumn('duration', Table::TYPE_INTEGER, 11, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'default' => '0'
        ), 'Duration'
        );
        $table->addColumn('time', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Message Time');
        $table->addColumn('messaging_log_id', Table::TYPE_INTEGER, 11, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Epicor Comm Messaging Log Id'
        );

        $table->addColumn('cached', Table::TYPE_TEXT, 10, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Cached');
        $installer->getConnection()->createTable($table);


        //install ecc_elements_paymentaccount
        $installer->getConnection()->dropTable('ecc_elements_paymentaccount');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_elements_paymentaccount')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );
        $table->addColumn('customer_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Customer Id'
        );
        $table->addColumn('payment_account_id', Table::TYPE_TEXT, 254, array(
            'nullable' => true,
        ), 'Payment Account Id'
        );
        $table->addColumn('card_type', Table::TYPE_TEXT, 12, array(
            'nullable' => true,
        ), 'Card Type'
        );
        $table->addColumn('last4', Table::TYPE_TEXT, 4, array(
            'nullable' => true,
        ), 'Last 4 Digits'
        );
        $table->addColumn('expiry_date', Table::TYPE_TIMESTAMP, null, array(), 'Expiry Date'
        );
        $table->addColumn('reuseable', Table::TYPE_BOOLEAN, null, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Re-Usable Token');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_elements_paymentaccount'),
                array('customer_id')
            ),
            array('customer_id')
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('customer_entity'),
                'entity_id',
                $installer->getTable('ecc_elements_paymentaccount'),
                'customer_id'),
            'customer_id',
            $installer->getTable('customer_entity'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);


        //install ecc_reports_raw_data
        $installer->getConnection()->dropTable('ecc_reports_raw_data');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_reports_raw_data')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );

        $table->addColumn('store', Table::TYPE_TEXT, 100, array(
            'nullable' => false,
        ), 'Store id'
        );
        $table->addColumn('message_type', Table::TYPE_TEXT, 5, array(
            'nullable' => true,
        ), 'Message type'
        );
        $table->addColumn('message_status', Table::TYPE_TEXT, 20, array(
            'nullable' => true,
        ), 'Message status'
        );
        $table->addColumn('duration', Table::TYPE_INTEGER, 11, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'default' => '0'
        ), 'Duration'
        );
        $table->addColumn('time', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
        ), 'Message Time');
        $table->addColumn('messaging_log_id', Table::TYPE_INTEGER, 11, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Epicor Comm Messaging Log Id'
        );

        $table->addColumn('cached', Table::TYPE_TEXT, 10, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Cached');
        $installer->getConnection()->createTable($table);


        //install ecc_elements_transaction
        $installer->getConnection()->dropTable('ecc_elements_transaction');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_elements_transaction')
        );
        $table->addColumn('entity_id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );
        $table->addColumn('quote_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Quote Id'
        );
        $table->addColumn('order_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => true,
            'primary' => false,
        ), 'Order Id'
        );
        $table->addColumn('error', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'error');
        $table->addColumn('transaction_setup_express_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'Transaction Setup Express Response Code');
        $table->addColumn('transaction_setup_express_response_message', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'transaction_setup_express_response_message');
        $table->addColumn('transaction_setup_id', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'transaction_setup_id');
        $table->addColumn('transaction_validation_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'transaction_validation_code');
        $table->addColumn('hosted_express_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'hosted_express_response_code');
        $table->addColumn('hosted_express_response_message', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'hosted_express_response_message');
        $table->addColumn('hosted_payment_status', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'hosted_payment_status');
        $table->addColumn('hosted_services_id', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'hosted_services_id');
        $table->addColumn('payment_account_id', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_id');
        $table->addColumn('last_four', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'last_four');
        $table->addColumn('hosted_validation_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'hosted_validation_code');
        $table->addColumn('payment_account_query_express_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_query_express_response_code');
        $table->addColumn('payment_account_query_express_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_query_express_response_code');
        $table->addColumn('payment_account_query_express_response_message', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_query_express_response_message');
        $table->addColumn('payment_account_query_services_id', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_query_services_id');
        $table->addColumn('payment_account_type', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_type');
        $table->addColumn('truncated_card_number', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'truncated_card_number');
        $table->addColumn('expiration_month', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'expiration_month');
        $table->addColumn('expiration_year', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'expiration_year');
        $table->addColumn('payment_account_reference_number', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_reference_number');
        $table->addColumn('payment_brand', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_brand');
        $table->addColumn('pass_updater_batch_status', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'pass_updater_batch_status');
        $table->addColumn('pass_updater_status', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'pass_updater_status');
        $table->addColumn('credit_card_auth_express_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'credit_card_auth_express_response_code');
        $table->addColumn('credit_card_auth_express_response_message', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'credit_card_auth_express_response_message');
        $table->addColumn('credit_card_auth_host_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'credit_card_auth_host_response_code');
        $table->addColumn('credit_card_auth_host_response_message', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'credit_card_auth_host_response_message');
        $table->addColumn('avs_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'avs_response_code');
        $table->addColumn('card_logo', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'card_logo');
        $table->addColumn('transaction_id', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'transaction_id');
        $table->addColumn('approval_number', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'approval_number');
        $table->addColumn('reference_number', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'reference_number');
        $table->addColumn('acquirer_data', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'acquirer_data');
        $table->addColumn('processor_name', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'processor_name');
        $table->addColumn('transaction_status', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'transaction_status');
        $table->addColumn('transaction_status_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'transaction_status_code');
        $table->addColumn('approved_amount', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'approved_amount');
        $table->addColumn('billing_address1', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'billing_address1');
        $table->addColumn('billing_zipcode', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'billing_zipcode');
        $table->addColumn('payment_account_delete_express_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_delete_express_response_code');
        $table->addColumn('payment_account_delete_express_response_message', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_delete_express_response_message');
        $table->addColumn('payment_account_delete_services_id', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'payment_account_delete_services_id');
        $table->addColumn('ref', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'ref');
        $table->addColumn('cvv_response_code', Table::TYPE_TEXT, 5000, array(
            'nullable' => true,
            'default' => false,
        ), 'cvv_response_code');
        $installer->getConnection()->createTable($table);


        //install ecc_salesrep_account
        $installer->getConnection()->dropTable('ecc_salesrep_account');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_salesrep_account')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );
        $table->addColumn('sales_rep_id', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Sales Rep Id'
        );

        $table->addColumn('name', Table::TYPE_TEXT, 255, array(), 'Sales Rep Name');
        $table->addColumn('created_at', Table::TYPE_DATETIME, null, array(
            'nullable' => false
        ), 'Created At'
        );
        $table->addColumn('updated_at', Table::TYPE_DATETIME, null, array(
            'nullable' => false
        ), 'Updated At'
        );
        $table->addColumn('catalog_access', Table::TYPE_TEXT, 1, array(), 'Catalog Acces');
        $table->addColumn('company', Table::TYPE_TEXT, 5000, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'column company');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_account'),
                array('sales_rep_id')
            ),
            array('sales_rep_id')
        );
        $installer->getConnection()->createTable($table);


        //install ecc_salesrep_hierarchy
        $installer->getConnection()->dropTable('ecc_salesrep_hierarchy');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_salesrep_hierarchy')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );
        $table->addColumn('child_sales_rep_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Child Id'
        );
        $table->addColumn('parent_sales_rep_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Parent Id'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_hierarchy'),
                array('child_sales_rep_account_id')
            ),
            array('child_sales_rep_account_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_hierarchy'),
                array('parent_sales_rep_account_id')
            ),
            array('parent_sales_rep_account_id')
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_salesrep_account'),
                'id',
                $installer->getTable('ecc_salesrep_hierarchy'),
                'child_sales_rep_account_id'),
            'child_sales_rep_account_id',
            $installer->getTable('ecc_salesrep_account'), 'id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_salesrep_account'),
                'id',
                $installer->getTable('ecc_salesrep_hierarchy'),
                'parent_sales_rep_account_id'),
            'parent_sales_rep_account_id',
            $installer->getTable('ecc_salesrep_account'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);

        //install ecc_salesrep_erp_account
        $installer->getConnection()->dropTable('ecc_salesrep_erp_account');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_salesrep_erp_account')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );
        $table->addColumn('sales_rep_account_id', Table::TYPE_INTEGER, 10, array(
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
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_erp_account'),
                array('sales_rep_account_id')
            ),
            array('sales_rep_account_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_erp_account'),
                array('erp_account_id')
            ),
            array('erp_account_id')
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_salesrep_account'),
                'id',
                $installer->getTable('ecc_salesrep_erp_account'),
                'sales_rep_account_id'),
            'sales_rep_account_id',
            $installer->getTable('ecc_salesrep_account'), 'id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_erp_account'),
                'entity_id',
                $installer->getTable('ecc_salesrep_erp_account'),
                'erp_account_id'),
            'erp_account_id',
            $installer->getTable('ecc_erp_account'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);




        //install ecc_salesrep_pricing_rule
        $installer->getConnection()->dropTable('ecc_salesrep_pricing_rule');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_salesrep_pricing_rule')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID'
        );
        $table->addColumn('name', Table::TYPE_TEXT, 255, array(), 'Pricing Rule Name');
        $table->addColumn('sales_rep_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Sale Rep Account Id'
        );
        $table->addColumn('from_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Start From Date');
        $table->addColumn('to_date', Table::TYPE_DATETIME, 255, array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
        ), 'Finish On Date');
        $table->addColumn('is_active', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ), 'Is Active'
        );
        $table->addColumn('priority', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Sort Order of Rules'
        );
        $table->addColumn('action_operator', Table::TYPE_TEXT, 255, array(), 'Pricing Rule Base On');
        $table->addColumn('action_amount', Table::TYPE_DECIMAL, '16,4', array(), 'Sale Rep Margin');
        $table->addColumn('conditions_serialized', Table::TYPE_TEXT, '4G', array(
            'nullable' => false,
        ), 'Conditions');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_pricing_rule'),
                array('sales_rep_account_id')
            ),
            array('sales_rep_account_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_pricing_rule'),
                array('is_active')
            ),
            array('is_active')
        );

        $installer->getConnection()->createTable($table);


        //install ecc_salesrep_pricing_rule_product
        $installer->getConnection()->dropTable('ecc_salesrep_pricing_rule_product');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_salesrep_pricing_rule_product')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID'
        );
        $table->addColumn('pricing_rule_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Pricing Rule Id'
        );
        $table->addColumn('product_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Pricing Id'
        );
        $table->addColumn('is_valid', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ), 'Is Valid'
        );
        $table->addColumn('store_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Store Id'
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_pricing_rule_product'),
                array('pricing_rule_id', 'product_id', 'store_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('pricing_rule_id', 'product_id', 'store_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_pricing_rule_product'),
                array('pricing_rule_id')
            ),
            array('pricing_rule_id')
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_salesrep_pricing_rule_product'),
                array('product_id')
            ),
            array('product_id')
        );
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_salesrep_pricing_rule'),
                'id',
                $installer->getTable('ecc_salesrep_pricing_rule_product'),
                'pricing_rule_id'),
            'pricing_rule_id',
            $installer->getTable('ecc_salesrep_pricing_rule'), 'id',
            Table::ACTION_CASCADE);
        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                $installer->getTable('ecc_salesrep_pricing_rule_product'),
                'product_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'), 'entity_id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->dropTable('ecc_erp_mapping_attributes');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_erp_mapping_attributes')
        );

        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            ), 'ID');
        $table->addColumn('attribute_code', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            'index' => true,
            ), 'Attribute Code');
        $table->addColumn('input_type', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Input Type');
        $table->addColumn('separator', Table::TYPE_TEXT, 255, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Separator');
        $table->addColumn('use_for_config', Table::TYPE_BOOLEAN, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Use For Config');
        $table->addColumn('quick_search', Table::TYPE_BOOLEAN, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Quick Search');
        $table->addColumn('advanced_search', Table::TYPE_BOOLEAN, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Advanced Search');
        $table->addColumn('search_weighting', Table::TYPE_SMALLINT, 5, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Search Weighting');
        $table->addColumn('use_in_layered_navigation', Table::TYPE_SMALLINT, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Use In Layered Navigation');
        $table->addColumn('search_results', Table::TYPE_BOOLEAN, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Search Results');
        $table->addColumn('visible_on_product_view', Table::TYPE_BOOLEAN, 1, array(
            'identity' => false,
            'nullable' => false,
            'primary' => false,
            ), 'Visible On Product View');

        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}