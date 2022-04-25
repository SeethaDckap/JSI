<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private static $connectionName = 'checkout';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.7.3', '<')) {
            $this->version_1_0_7_3($installer);
        }
        
        if (version_compare($context->getVersion(), '1.0.7.4', '<')) {
            $this->version_1_0_7_4($installer);
        }
        
        if (version_compare($context->getVersion(), '1.0.7.5', '<')) {
            $this->version_1_0_7_5($installer);
        }

        if (version_compare($context->getVersion(), '1.0.7.6', '<')) {
            $this->version_1_0_7_6($installer);
        }

        if (version_compare($context->getVersion(), '1.0.7.7', '<')) {
            $this->version_1_0_7_7($installer);
        }

        if (version_compare($context->getVersion(), '1.0.7.8', '<')) {
            $this->version_1_0_7_8($installer);
        }

        if (version_compare($context->getVersion(), '1.0.8.1', '<')) {
            $this->version_1_0_8_1($installer);
        }
       

        $installer->endSetup();
    }
	
    protected function version_1_0_7_3(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'quote'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_quote')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
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
            'converted_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Converted At'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Is Active'
        )->addColumn(
            'items_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Items Count'
        )->addColumn(
            'items_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Items Qty'
        )->addColumn(
            'base_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Base Currency Code'
        )->addColumn(
            'store_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Store Currency Code'
        )->addColumn(
            'quote_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Quote Currency Code'
        )->addColumn(
            'grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Grand Total'
        )->addColumn(
            'base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Base Grand Total'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Customer Group Id'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Email'
        )->addColumn(
            'customer_prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Customer Prefix'
        )->addColumn(
            'customer_firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Firstname'
        )->addColumn(
            'customer_middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Customer Middlename'
        )->addColumn(
            'customer_lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Lastname'
        )->addColumn(
            'customer_suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Customer Suffix'
        )->addColumn(
            'customer_dob',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Customer Dob'
        )->addColumn(
            'customer_note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Note'
        )->addColumn(
            'customer_note_notify',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Customer Note Notify'
        )->addColumn(
            'remote_ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Remote Ip'
        )->addColumn(
            'global_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Global Currency Code'
        )->addColumn(
            'customer_gender',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Gender'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal'
        )->addColumn(
            'base_subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Subtotal'
        )->addColumn(
            'ecc_arpayments_allocated_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Allocated Amount'
        )->addColumn(
            'ecc_arpayments_amountleft',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Amount Left'
        )->addColumn(
            'ecc_arpayments_ispayment',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Is Active'
        )->addIndex(
            $installer->getIdxName('ar_quote', ['customer_id', 'store_id', 'is_active']),
            ['customer_id', 'store_id', 'is_active']
        )->addIndex(
            $installer->getIdxName('ar_quote', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ar_quote', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Quote'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'quote_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_quote_address')
        )->addColumn(
            'address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Address Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quote Id'
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Updated At'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'customer_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Address Id'
        )->addColumn(
            'address_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Address Type'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Prefix'
        )->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Firstname'
        )->addColumn(
            'middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Middlename'
        )->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Lastname'
        )->addColumn(
            'suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Suffix'
        )->addColumn(
            'company',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Company'
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Street'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'City'
        )->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Region'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Region Id'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Postcode'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            [],
            'Country Id'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Phone Number'
        )->addColumn(
            'ecc_mobile_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Mobile Number'
        )->addColumn(
            'fax',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Fax'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Subtotal'
        )->addColumn(
            'base_subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Subtotal'
        )->addColumn(
            'grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Grand Total'
        )->addColumn(
            'base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Grand Total'
        )->addColumn(
            'customer_notes',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Customer Notes'
        )->addIndex(
            $installer->getIdxName('ar_quote_address', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('ar_quote_address', 'quote_id', 'ar_quote', 'entity_id'),
            'quote_id',
            $installer->getTable('ar_quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Quote Address'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ar_quote_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_quote_item')
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Item Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quote Id'
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Updated At'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Sku'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Description'
        )->addColumn(
            'additional_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Data'
        )->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Qty'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Price'
        )->addColumn(
            'custom_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Custom Price'
        )->addColumn(
            'row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Row Total'
        )->addColumn(
            'base_row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Row Total'
        )->addIndex(
            $installer->getIdxName('ar_quote_item', ['quote_id']),
            ['quote_id']
        )->addIndex(
            $installer->getIdxName('ar_quote_item', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ar_quote_item', 'quote_id', 'ar_quote', 'entity_id'),
            'quote_id',
            $installer->getTable('ar_quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ar_quote_item', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Quote Item'
        );
        $installer->getConnection()->createTable($table);

       

        /**
         * Create table 'ar_quote_payment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_quote_payment')
        )->addColumn(
            'payment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Payment Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Quote Id'
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Updated At'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Method'
        )->addColumn(
            'cc_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Type'
        )->addColumn(
            'cc_number_enc',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Number Enc'
        )->addColumn(
            'cc_last_4',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Last 4'
        )->addColumn(
            'cc_cid_enc',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Cid Enc'
        )->addColumn(
            'cc_owner',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Owner'
        )->addColumn(
            'cc_exp_month',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'default' => null, 'nullable' => true],
            'Cc Exp Month'
        )->addColumn(
            'cc_exp_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Cc Exp Year'
        )->addColumn(
            'cc_ss_owner',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Ss Owner'
        )->addColumn(
            'cc_ss_start_month',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Cc Ss Start Month'
        )->addColumn(
            'cc_ss_start_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Cc Ss Start Year'
        )->addColumn(
            'po_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Po Number'
        )->addColumn(
            'additional_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Data'
        )->addColumn(
            'cc_ss_issue',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Cc Ss Issue'
        )->addColumn(
            'additional_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Information'
        )->addColumn(
            'ecc_elements_payment_account_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Ecc Elements Payment Account Id'
        )->addColumn(
            'ecc_elements_processor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Ecc Elements Processor Id'
        )->addColumn(
            'ecc_elements_transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Ecc Elements Transaction Id'
        )->addColumn(
            'ecc_cc_cvv_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Credit Card CVV Status'
        )->addColumn(
            'ecc_cc_auth_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Credit Card Authorization Code'
        )->addColumn(
            'ecc_ccv_token',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'CCV Token'
        )->addColumn(
            'ecc_cvv_token',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'CVV Token'
        )->addColumn(
            'ecc_cre_transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Cre Transaction Id'
        )->addColumn(
            'ecc_site_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Site Url'
        )->addColumn(
            'ecc_is_saved',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Is Saved Token'
        )->addIndex(
            $installer->getIdxName('ar_quote_payment', ['quote_id']),
            ['quote_id']
        )->addForeignKey(
            $installer->getFkName('ar_quote_payment', 'quote_id', 'ar_quote', 'entity_id'),
            'quote_id',
            $installer->getTable('ar_quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Quote Payment'
        );
        $installer->getConnection()->createTable($table);
        
         /**
         * Create table 'ar_sales_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_order')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'state',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'State'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Status'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Grand Total'
        )->addColumn(
            'grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Grand Total'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal'
        )->addColumn(
            'total_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Total Paid'
        )->addColumn(
            'base_total_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Total Paid'
        )->addColumn(
            'total_qty_ordered',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Total Qty Ordered'
        )->addColumn(
            'customer_note_notify',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Customer Note Notify'
        )->addColumn(
            'billing_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Billing Address Id'
        )->addColumn(
            'customer_group_id',
            'integer',
            null,
            [],
            'Customer Group Id'
        )->addColumn(
            'email_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Email Sent'
        )->addColumn(
            'send_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Send Email'
        )->addColumn(
            'quote_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Quote Address Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Quote Id'
        )->addColumn(
            'shipping_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Shipping Address Id'
        )->addColumn(
            'customer_dob',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Customer Dob'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Increment Id'
        )->addColumn(
            'base_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Base Currency Code'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Email'
        )->addColumn(
            'customer_firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Firstname'
        )->addColumn(
            'customer_lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Lastname'
        )->addColumn(
            'customer_middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Middlename'
        )->addColumn(
            'customer_prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Customer Prefix'
        )->addColumn(
            'customer_suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Customer Suffix'
        )->addColumn(
            'customer_taxvat',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Customer Taxvat'
        )->addColumn(
            'global_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Global Currency Code'
        )->addColumn(
            'order_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Order Currency Code'
        )->addColumn(
            'remote_ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Remote Ip'
        )->addColumn(
            'store_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Store Currency Code'
        )->addColumn(
            'store_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Store Name'
        )->addColumn(
            'x_forwarded_for',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'X Forwarded For'
        )->addColumn(
            'customer_note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Customer Note'
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
            'total_item_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Total Item Count'
        )->addColumn(
            'customer_gender',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Customer Gender'
        )->addColumn(
            'ecc_arpayments_allocated_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Allocated Amount'
        )->addColumn(
            'ecc_arpayments_amountleft',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Amount Left'
        )->addColumn(
            'ecc_arpayments_ispayment',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'ecc_caap_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Ecc CAAP Message'
        )->addColumn(
            'ecc_caap_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'CAAP Sent'
        )->addColumn(
            'erp_arpayments_order_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Erp Order Numer'
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['status']),
            ['status']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['state']),
            ['state']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName(
                'ar_sales_order',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['customer_id']),
            ['customer_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['quote_id']),
            ['quote_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['send_email']),
            ['send_email']
        )->addIndex(
            $installer->getIdxName('ar_sales_order', ['email_sent']),
            ['email_sent']
        )->addForeignKey(
            $installer->getFkName('ar_sales_order', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName('ar_sales_order', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Order'
        );
        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'ar_sales_order_grid'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_order_grid')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Status'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'store_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Store Name'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Grand Total'
        )->addColumn(
            'base_total_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Total Paid'
        )->addColumn(
            'grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Grand Total'
        )->addColumn(
            'total_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Total Paid'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Increment Id'
        )->addColumn(
            'base_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Base Currency Code'
        )->addColumn(
            'order_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Order Currency Code'
        )->addColumn(
            'shipping_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Name'
        )->addColumn(
            'billing_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Name'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Updated At'
        )->addColumn(
            'billing_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Address'
        )->addColumn(
            'shipping_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Address'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Email'
        )->addColumn(
            'customer_group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Group'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Name'
        )->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Payment Method'
        )->addColumn(
            'total_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Total Refunded'
        )->addColumn(
            'ecc_arpayments_allocated_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Allocated Amount'
        )->addColumn(
            'ecc_arpayments_amountleft',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Amount Left'
        )->addColumn(
            'ecc_arpayments_ispayment',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'ecc_caap_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Ecc CAAP Message'
        )->addColumn(
            'ecc_caap_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'CAAP Sent'
        )->addColumn(
            'erp_arpayments_order_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Erp Order Numer'
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['status']),
            ['status']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['base_grand_total']),
            ['base_grand_total']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['base_total_paid']),
            ['base_total_paid']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['grand_total']),
            ['grand_total']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['total_paid']),
            ['total_paid']
        )->addIndex(
            $installer->getIdxName(
                'ar_sales_order_grid',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['shipping_name']),
            ['shipping_name']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['billing_name']),
            ['billing_name']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['customer_id']),
            ['customer_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_grid', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName(
                'ar_sales_order_grid',
                [
                    'increment_id',
                    'billing_name',
                    'shipping_name',
                    'shipping_address',
                    'billing_address',
                    'customer_name',
                    'customer_email'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [
                'increment_id',
                'billing_name',
                'shipping_name',
                'shipping_address',
                'billing_address',
                'customer_name',
                'customer_email'
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Sales Flat Order Grid'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ar_sales_order_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_order_address')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Parent Id'
        )->addColumn(
            'customer_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Customer Address Id'
        )->addColumn(
            'quote_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Quote Address Id'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Region Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Customer Id'
        )->addColumn(
            'fax',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Fax'
        )->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Region'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Postcode'
        )->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Lastname'
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Street'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'City'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Phone Number'
        )->addColumn(
            'ecc_mobile_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Mobile Number'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            [],
            'Country Id'
        )->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Firstname'
        )->addColumn(
            'address_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Address Type'
        )->addColumn(
            'prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Prefix'
        )->addColumn(
            'middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Middlename'
        )->addColumn(
            'suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Suffix'
        )->addColumn(
            'company',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Company'
        )->addIndex(
            $installer->getIdxName('ar_sales_order_address', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('ar_sales_order_address', 'parent_id', 'ar_sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('ar_sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Order Address'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ar_sales_order_status_history'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_order_status_history')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent Id'
        )->addColumn(
            'is_customer_notified',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Is Customer Notified'
        )->addColumn(
            'is_visible_on_front',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Visible On Front'
        )->addColumn(
            'comment',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Comment'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Status'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'entity_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true],
            'Shows what entity history is bind to.'
        )->addIndex(
            $installer->getIdxName('ar_sales_order_status_history', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_status_history', ['created_at']),
            ['created_at']
        )->addForeignKey(
            $installer->getFkName('ar_sales_order_status_history', 'parent_id', 'ar_sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('ar_sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Order Status History'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ar_sales_order_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_order_item')
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Item Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Order Id'
        )->addColumn(
            'quote_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Quote Item Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
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
            'product_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Product Options'
        )->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Sku'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Description'
        )->addColumn(
            'additional_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Data'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Price'
        )->addColumn(
            'base_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Price'
        )->addColumn(
            'row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Row Total'
        )->addColumn(
            'base_row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Row Total'
        )->addIndex(
            $installer->getIdxName('ar_sales_order_item', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_order_item', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ar_sales_order_item', 'order_id', 'ar_sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('ar_sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ar_sales_order_item', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Order Item'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ar_sales_order_payment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_order_payment')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent Id'
        )->addColumn(
            'base_shipping_captured',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Captured'
        )->addColumn(
            'shipping_captured',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Captured'
        )->addColumn(
            'amount_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Amount Refunded'
        )->addColumn(
            'base_amount_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Paid'
        )->addColumn(
            'amount_canceled',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Amount Canceled'
        )->addColumn(
            'base_amount_authorized',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Authorized'
        )->addColumn(
            'base_amount_paid_online',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Paid Online'
        )->addColumn(
            'base_amount_refunded_online',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Refunded Online'
        )->addColumn(
            'base_shipping_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Amount'
        )->addColumn(
            'shipping_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Amount'
        )->addColumn(
            'amount_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Amount Paid'
        )->addColumn(
            'amount_authorized',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Amount Authorized'
        )->addColumn(
            'base_amount_ordered',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Ordered'
        )->addColumn(
            'base_shipping_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Refunded'
        )->addColumn(
            'shipping_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Refunded'
        )->addColumn(
            'base_amount_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Refunded'
        )->addColumn(
            'amount_ordered',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Amount Ordered'
        )->addColumn(
            'base_amount_canceled',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Amount Canceled'
        )->addColumn(
            'quote_payment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Quote Payment Id'
        )->addColumn(
            'additional_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Data'
        )->addColumn(
            'cc_exp_month',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            12,
            [],
            'Cc Exp Month'
        )->addColumn(
            'cc_ss_start_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            12,
            [],
            'Cc Ss Start Year'
        )->addColumn(
            'echeck_bank_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Echeck Bank Name'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Method'
        )->addColumn(
            'cc_debug_request_body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Debug Request Body'
        )->addColumn(
            'cc_secure_verify',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Secure Verify'
        )->addColumn(
            'protection_eligibility',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Protection Eligibility'
        )->addColumn(
            'cc_approval',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Approval'
        )->addColumn(
            'cc_last_4',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Cc Last 4'
        )->addColumn(
            'cc_status_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Status Description'
        )->addColumn(
            'echeck_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Echeck Type'
        )->addColumn(
            'cc_debug_response_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Debug Response Serialized'
        )->addColumn(
            'cc_ss_start_month',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Cc Ss Start Month'
        )->addColumn(
            'echeck_account_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Echeck Account Type'
        )->addColumn(
            'last_trans_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Last Trans Id'
        )->addColumn(
            'cc_cid_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Cid Status'
        )->addColumn(
            'cc_owner',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Cc Owner'
        )->addColumn(
            'cc_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Type'
        )->addColumn(
            'po_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Po Number'
        )->addColumn(
            'cc_exp_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            ['nullable' => true, 'default' => null],
            'Cc Exp Year'
        )->addColumn(
            'cc_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            [],
            'Cc Status'
        )->addColumn(
            'echeck_routing_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Echeck Routing Number'
        )->addColumn(
            'account_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Account Status'
        )->addColumn(
            'anet_trans_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Anet Trans Method'
        )->addColumn(
            'cc_debug_response_body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Debug Response Body'
        )->addColumn(
            'cc_ss_issue',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Ss Issue'
        )->addColumn(
            'echeck_account_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Echeck Account Name'
        )->addColumn(
            'cc_avs_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Avs Status'
        )->addColumn(
            'cc_number_enc',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Cc Number Enc'
        )->addColumn(
            'cc_trans_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Trans Id'
        )->addColumn(
            'address_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Address Status'
        )->addColumn(
            'additional_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Information'
        )->addColumn(
            'ecc_elements_payment_account_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Ecc Elements Payment Account Id'
        )->addColumn(
            'ecc_elements_processor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Ecc Elements Processor Id'
        )->addColumn(
            'ecc_elements_transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Ecc Elements Transaction Id'
        )->addColumn(
            'ecc_cc_cvv_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Credit Card CVV Status'
        )->addColumn(
            'ecc_cc_auth_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Credit Card Authorization Code'
        )->addColumn(
            'ecc_ccv_token',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'CCV Token'
        )->addColumn(
            'ecc_cvv_token',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'CVV Token'
        )->addColumn(
            'ecc_cre_transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Cre Transaction Id'
        )->addColumn(
            'ecc_site_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Site Url'
        )->addColumn(
            'ecc_is_saved',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Is Saved Token'
        )->addIndex(
            $installer->getIdxName('ar_sales_order_payment', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('ar_sales_order_payment', 'parent_id', 'ar_sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('ar_sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Order Payment'
        );
        $installer->getConnection()->createTable($table);
        
         /**
         * Create table 'ar_sales_payment_transaction'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ar_sales_payment_transaction')
        )->addColumn(
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Transaction Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Parent Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Order Id'
        )->addColumn(
            'payment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Payment Id'
        )->addColumn(
            'txn_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Txn Id'
        )->addColumn(
            'parent_txn_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Parent Txn Id'
        )->addColumn(
            'txn_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            [],
            'Txn Type'
        )->addColumn(
            'is_closed',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Closed'
        )->addColumn(
            'additional_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
            '64K',
            [],
            'Additional Information'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName(
                'ar_sales_payment_transaction',
                ['order_id', 'payment_id', 'txn_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_id', 'payment_id', 'txn_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ar_sales_payment_transaction', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('ar_sales_payment_transaction', ['payment_id']),
            ['payment_id']
        )->addForeignKey(
            $installer->getFkName('ar_sales_payment_transaction', 'order_id', 'ar_sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('ar_sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ar_sales_payment_transaction',
                'parent_id',
                'ar_sales_payment_transaction',
                'transaction_id'
            ),
            'parent_id',
            $installer->getTable('ar_sales_payment_transaction'),
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ar_sales_payment_transaction', 'payment_id', 'ar_sales_order_payment', 'entity_id'),
            'payment_id',
            $installer->getTable('ar_sales_order_payment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Payment Transaction'
        );
        $installer->getConnection()->createTable($table);
        
        $tableNameErp = $installer->getTable('ecc_erp_account');
        if ($installer->getConnection()->tableColumnExists($tableNameErp, 'is_arpayments_allowed') == false) {
            $installer->getConnection()
                    ->addColumn(
                        $tableNameErp, 'is_arpayments_allowed', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 1,
                        'nullable' => true,
                        'default' => 2,
                        'comment' =>'AR Payments Allowed, 2: Global Default, 0: No, 1: Yes, 3:Yes, no disputes'
                        ]
            );            
        }
        if ($installer->getConnection()->tableColumnExists($tableNameErp, 'is_invoice_edit') == false) {
            $installer->getConnection()
                    ->addColumn(
                        $tableNameErp, 'is_invoice_edit', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 1,
                        'nullable' => true,
                        'default' => 2,
                        'comment' =>'AR Payments Invoice Edit Allowed, 2: Global Default, 0: No, 1: Yes'
                        ]
            );    
        }        
    }
	
    protected function version_1_0_7_4(SchemaSetupInterface $installer)
    {
        $tablePairs = [
            ['oldName' => 'ar_quote', 'newName' => 'ecc_ar_quote'],
            ['oldName' => 'ar_quote_address', 'newName' => 'ecc_ar_quote_address'],
            ['oldName' => 'ar_quote_item', 'newName' => 'ecc_ar_quote_item'],
            ['oldName' => 'ar_quote_payment', 'newName' => 'ecc_ar_quote_payment'],
            ['oldName' => 'ar_sales_order', 'newName' => 'ecc_ar_sales_order'],
            ['oldName' => 'ar_sales_order_address', 'newName' => 'ecc_ar_sales_order_address'],
            ['oldName' => 'ar_sales_order_grid', 'newName' => 'ecc_ar_sales_order_grid'],
            ['oldName' => 'ar_sales_order_item', 'newName' => 'ecc_ar_sales_order_item'],
            ['oldName' => 'ar_sales_order_payment', 'newName' => 'ecc_ar_sales_order_payment'],
            ['oldName' => 'ar_sales_order_status_history', 'newName' => 'ecc_ar_sales_order_status_history'],
            ['oldName' => 'ar_sales_payment_transaction', 'newName' => 'ecc_ar_sales_payment_transaction']
        ];
        $installer->getConnection()->renameTablesBatch($tablePairs);
    }
    
    protected function version_1_0_7_5(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('quote');
        if ($installer->getConnection()->tableColumnExists($tableName, 'arpayments_quote') == false) {
            $installer->getConnection()
                ->addColumn(
                    $tableName,
                    'arpayments_quote',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'default' => '0',
                        'comment' => 'Ar Payments Quote'
                    ]
                );
        }      
        
        $tableName1 = $installer->getTable('sales_order');
        if ($installer->getConnection()->tableColumnExists($tableName1, 'arpayments_quote') == false) {
            $installer->getConnection()
                ->addColumn(
                    $tableName1,
                    'arpayments_quote',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'default' => '0',
                        'comment' => 'Ar Payments Quote'
                    ]
                );
        }         
        
    }

    protected function version_1_0_7_6(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_preq_queue'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_preq_queue')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'request_config',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Request Config'
        )->addColumn(
            'response_config',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Response Config'
        )->addColumn(
            'entity_document',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Entity Document'
        )->addColumn(
            'email_params',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Email Parameteres'
        )->addColumn(
            'account_number',
            Table::TYPE_TEXT,
            null,
            [],
            'Erp Account Number'
        )->addColumn(
            'ready_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false, 'default' => '0'],
            'Ready Status'
        )->setComment(
            'PREQ Message queue'
        );
        $installer->getConnection()->createTable($table);
    }

    protected function version_1_0_7_7(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'misc_view_type') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'misc_view_type',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => false,
                            'default' => 2,
                            'comment' => ' View Miscellaneous Charges?, 2: Default, 0: Contracted, 1: Expanded'
                        ]
                    );
            }
        }
    }

    protected function version_1_0_7_8(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_mapping_misc');
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
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                );

            $installer->getConnection()->createTable($table);
        }
    }

    /**
     * Adds new column to table ecc_erp_account for central payment collection flag
     *
     * @param SchemaSetupInterface $installer
     **/
    protected function version_1_0_8_1(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'is_central_collection') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'is_central_collection',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => ' Is Central Collection'
                        ]
                    );
            }
        }
    }
}
