<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Setup;

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

    public function __construct(WriterInterface $configWriter)
    {
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.6.1.1', '<')) {
            $this->version_1_0_6_1_1($installer);
        }
        if (version_compare($context->getVersion(), '1.0.6.1.2', '<')) {
            $this->version_1_0_6_1_2($installer);
        }
        if (version_compare($context->getVersion(), '1.0.6.2.0', '<')) {
            $this->version_1_0_6_2_0($installer);
        }
        if (version_compare($context->getVersion(), '1.0.6.3.1', '<')) {
            $this->version_1_0_6_3_1($installer);
        }
        if (version_compare($context->getVersion(), '1.0.6.3.2', '<')) {
            $this->setupGridConfigValues($installer);
        }
        if (version_compare($context->getVersion(), '1.0.6.3.4', '<')) {
            $this->version_1_0_6_3_4($installer);
        }
        $installer->endSetup();
    }

    protected function version_1_0_6_1_1(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_supplier_reminder'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_supplier_reminder')
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
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'account_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Account Id'
        )->addColumn(
            'rfqs_due_today_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Rfqs Due Today'
        )->addColumn(
            'rfqs_due_this_week_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Rfqs Due This Week'
        )->addColumn(
            'all_open_rfqs_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'All Open Rfqs'
        )->addColumn(
            'rfqs_open_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Rfqs Open Options'
        )->addColumn(
            'upcoming_rfqs_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Upcoming Rfqs'
        )->addColumn(
            'rfqs_upcoming_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Rfqs Upcoming Options'
        )->addColumn(
            'all_overdue_rfqs_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'All Overdue Rfqs'
        )->addColumn(
            'all_overdue_rfqs_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Rfqs Overdue Options'
        )->addColumn(
            'reminder_expiry_date_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Reminder Enable'
        )->addColumn(
            'reminder_expiry_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => false, 'default' => '0000-00-00'],
            'Reminder Expiry Date'
        )->addColumn(
            'email_reminder_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Email Reminder Enable'
        )->addColumn(
            'order_po_line_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Enable Po Line'
        )->addColumn(
            'order_open_po_enable',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Enable Order Po'
        )->addColumn(
            'order_open_po_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'order po options'
        )->addColumn(
            'order_po_line_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'order po line options'
        )->addColumn(
            'rfqs_due_today_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Rfqs Due today sent at'
        )->addColumn(
            'rfqs_due_this_week_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Rfqs Due this weeke sent At'
        )->addColumn(
            'all_open_rfqs_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'All Open Rfqs Sent At'
        )->addColumn(
            'upcoming_rfqs_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Upcoming Rfqs Sent At'
        )->addColumn(
            'all_overdue_rfqs_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'All Overdue Rfqs Sent At'
        )->addColumn(
            'reminder_email_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Reminder Email Sent At'
        )->addColumn(
            'order_open_po_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Po  Sent At'
        )->addColumn(
            'order_open_po_line_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
            'Po Line Sent At'
        )->addColumn(
            'rfqs_last_cron_update',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => false, 'default' => '0000-00-00'],
            'Rfqs Last Cron Update'
        )->setComment(
            'RFQS Supplier Reminder'
        );
        $installer->getConnection()->createTable($table);

    }


    protected function version_1_0_6_1_2(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_supplier_reminder'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_supplier_dashboard')
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
            'enable_rfqs_supplier',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Enable Rfq Section'
        )->addColumn(
            'rfqs_filter',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'rfqs_filter'
        )->addColumn(
            'enable_rfqs_supplier_grid',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '1'],
            'enable_rfqs_supplier_grid'
        )->addColumn(
            'rfqs_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'rfqs_from'
        )->addColumn(
            'rfqs_supplier_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'rfqs_supplier_count'
        )->addColumn(
            'enable_order_supplier',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '1'],
            'Enable order Section'
        )->addColumn(
            'order_filter',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'order_filter'
        )->addColumn(
            'enable_order_supplier_grid',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '1'],
            'enable_order_supplier_grid'
        )->addColumn(
            'order_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'order_from'
        )->addColumn(
            'order_supplier_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'order_supplier_count'
        )->addColumn(
            'enable_invoice_supplier_grid',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '1'],
            'enable_invoice_supplier_grid'
        )->addColumn(
            'invoice_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'invoice_from'
        )->addColumn(
            'invoice_supplier_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'invoice_supplier_count'
        )->addColumn(
            'enable_payment_supplier_grid',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '1'],
            'enable_payment_supplier_grid'
        )->addColumn(
            'payment_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'payment_from'
        )->addColumn(
            'payment_supplier_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'payment_supplier_count'
        )->setComment(
            'Supplier Dashboard'
        );
        $installer->getConnection()->createTable($table);

    }

    protected function version_1_0_6_2_0($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        $values = [
            'supplierconnect_enabled_messages/SPOS_request/newpogrid_config' => 'a:9:{s:18:"_1380794775648_648";a:9:{s:6:"header";s:7:"Confirm";s:4:"type";s:4:"text";s:5:"index";s:14:"new_po_confirm";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:65:"Epicor_Supplierconnect_Block_Customer_Orders_New_Renderer_Confirm";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798405_405";a:9:{s:6:"header";s:6:"Reject";s:4:"type";s:4:"text";s:5:"index";s:13:"new_po_reject";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_New_Renderer_Reject";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798930_930";a:9:{s:6:"header";s:13:"Our PO Number";s:4:"type";s:4:"text";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_New_Renderer_Linkpo";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794799626_626";a:9:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794800354_354";a:9:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380794801098_98";a:9:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794801650_650";a:9:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802290_290";a:9:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802866_866";a:9:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:5:"index";s:12:"order_status";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:70:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Orderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
        ];
        foreach ($values as $path => $value) {
            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => $path,
                'value' => $value,
            ];

            $writeConnection->insertOnDuplicate(
                $setup->getTable('core_config_data'),
                $data,
                ['value']
            );
        }
    }


    protected function version_1_0_6_3_1(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_supplier_reminder'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('ecc_customfields_mapping')) != true) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ecc_customfields_mapping')
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
                'message_section',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'message_section'
            )->addColumn(
                'custom_fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'custom_fields'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )->setComment(
                'custom fields mapping for Grids'
            );
            $installer->getConnection()->createTable($table);
        }

    }

    private function setupGridConfigValues($setup) {

        $values = [
            'epicor_comm_enabled_messages/CRRS_request/grid_config' ,
            'customerconnect_enabled_messages/CUOS_request/grid_config',
            'customerconnect_enabled_messages/CPHS_request/grid_config',
            'customerconnect_enabled_messages/CUIS_request/grid_config',
            'customerconnect_enabled_messages/CUSS_request/grid_config',
            'customerconnect_enabled_messages/CUPS_request/grid_config',
            'customerconnect_enabled_messages/CURS_request/grid_config',
            'customerconnect_enabled_messages/CUCS_request/grid_config',
            'customerconnect_enabled_messages/CRQS_request/grid_config',
            'customerconnect_enabled_messages/CCCS_request/grid_config',
            'customerconnect_enabled_messages/CAPS_request/grid_config',
            'supplierconnect_enabled_messages/SPLS_request/grid_config',
            'supplierconnect_enabled_messages/SPOS_request/grid_config',
            'supplierconnect_enabled_messages/SPOS_request/newpogrid_config',
            'supplierconnect_enabled_messages/SPCS_request/grid_config',
            'supplierconnect_enabled_messages/SURS_request/grid_config',
            'supplierconnect_enabled_messages/SUIS_request/grid_config',
            'supplierconnect_enabled_messages/SUPS_request/grid_config',
            'dealerconnect_enabled_messages/DCLS_request/grid_config',
        ];

        $writeConnection = $setup->getConnection('core_write');
        $readConnection = $setup->getConnection('core_read');
        $tableName =$setup->getTable('core_config_data');
        foreach ($values as $path => $value) {
            $var = $readConnection->query('SELECT config_id,value,path,scope_id,scope FROM '.$tableName.' WHERE path = "'.$value.'"');
            $erpInfo = $var->fetchAll();
            if(count($erpInfo) > 0) {
                foreach ($erpInfo as $valueinfos) {
                    if(!empty($valueinfos['value'])) {
                        $exist = true;
                        $array = unserialize($valueinfos['value']);
                        foreach($array as $key => &$val) {
                            if (!isset($array[$key]['visible'])) {
                                $exist = false;
                                $array[$key]['visible'] = '1';
                            }
                            if (!isset($array[$key]['showfilter'])) {
                                $exist = false;
                                $array[$key]['showfilter'] = '1';
                            }
                            if (!$exist) {
                                $serializeArray = serialize($array);
                                $this->configWriter->save($valueinfos['path'], $serializeArray,$valueinfos['scope'],$valueinfos['scope_id']);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function version_1_0_6_3_4(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_supplier_reminder');
        if ($installer->getConnection()->isTableExists($tableName) == true) {
            $installer->startSetup();
            $installer->getConnection()->addColumn(
                $installer->getTable('ecc_supplier_dashboard'),
                'enable_summary_section',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'default' => '1',
                    'comment' => 'Enable Summary Section']);
            $installer->endSetup();
        }
    }


}
