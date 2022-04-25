<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Database\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Epicor\Comm\Ui\DataProviders\Product\Form\Modifier\Related;
use Epicor\Comm\Model\Catalog\Product\Link;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->version_1_0_0($installer);
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->version_1_0_5($installer);
        }
        
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->version_1_0_6($installer);
        }        
        
        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->version_1_0_7($installer);
        }   
        
        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->version_1_0_9($installer);
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->version1_1_2($installer);
        }  
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $this->version1_1_3($installer);
        }
        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            $this->version1_1_4($installer);
        }
        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            $this->version1_1_6($installer);
        }
        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $this->version1_1_8($installer);
        }
        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $this->version1_2_2($installer);
        }
        if (version_compare($context->getVersion(), '1.2.3', '<')) {
            $this->version1_2_3($installer);
        }
        if (version_compare($context->getVersion(), '1.2.5', '<')) {
            $this->version1_2_5($installer);
        }
        if (version_compare($context->getVersion(), '1.2.6', '<')) {
            $this->version1_2_6($installer);
        }    
        if (version_compare($context->getVersion(), '1.2.7', '<')) {
            $this->version1_2_7($installer);
        }
        if (version_compare($context->getVersion(), '1.2.9', '<')) {
            $this->version1_2_9($installer);
        }
        if (version_compare($context->getVersion(), '1.3.2', '<')) {
            $this->version1_3_2($installer);
        }
        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $this->version1_3_3($installer);
        }
        if (version_compare($context->getVersion(), '1.3.4', '<')) {
            $this->version1_3_4($installer);
        }
        if (version_compare($context->getVersion(), '1.3.5', '<')) {
            $this->version1_3_5($installer);
        }
        if (version_compare($context->getVersion(), '1.3.8', '<')) {
            $this->version1_3_8($installer);
        }
        if (version_compare($context->getVersion(), '1.3.8.6', '<')) {
            $this->version1_3_8_6($installer);
        }
        if (version_compare($context->getVersion(), '1.3.8.7', '<')) {
            $this->version1_3_8_7($installer);
        }
        if (version_compare($context->getVersion(), '1.4.1', '<')) {
            $this->version1_4_1($installer);
        }
        if (version_compare($context->getVersion(), '1.4.2', '<')) {
            $this->version1_4_2($installer);
        }
        if (version_compare($context->getVersion(), '1.4.4', '<')) {
            $this->version1_4_4($installer);
        }
        if (version_compare($context->getVersion(), '1.4.5', '<')) {
            $this->version1_4_5($installer);
        }
        if (version_compare($context->getVersion(), '1.4.6', '<')) {
            $this->version1_4_6($installer);
        }
        if (version_compare($context->getVersion(), '1.4.9', '<')) {
            $this->version1_4_9($installer);
        }
        if (version_compare($context->getVersion(), '1.5.1', '<')) {
            $this->version1_5_1($installer);
        }
        if (version_compare($context->getVersion(), '1.5.3', '<')) {
            $this->version1_5_3($installer);
        }
        if (version_compare($context->getVersion(), '1.5.5', '<')) {
            $this->version1_5_5($installer);
        }
        if (version_compare($context->getVersion(), '1.5.6', '<')) {
            $this->version1_5_6($installer);
        }
        if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->version1_6_0($installer);
        }
        if (version_compare($context->getVersion(), '1.6.1', '<')) {
            $this->version1_6_1($installer);
        }
        if (version_compare($context->getVersion(), '1.6.5', '<')) {
            $this->version1_6_5($installer);
        }
        if (version_compare($context->getVersion(), '1.6.6', '<')) {
            $this->version1_6_6($installer);
        }
        if (version_compare($context->getVersion(), '1.6.7', '<')) {
            $this->version1_6_7($installer);
        }
        if (version_compare($context->getVersion(), '1.7.4', '<')) {
            $this->version1_7_4($installer);
        }



        $installer->endSetup();
    }

    protected function version_1_0_0(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('quote');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_required_date') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_required_date',
                        [
                            'type' => Table::TYPE_DATE,
                            'nullable' => false,
                            'default' => '0000-00-00',
                            'comment' => 'Require Date'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_customer_order_ref') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_customer_order_ref',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'Customer Order Ref'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_is_dda_date') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_is_dda_date',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'comment' => 'Is DDA Date'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_next_delivery_date') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_next_delivery_date',
                        [
                            'type' => Table::TYPE_DATE,
                            'comment' => 'Next Delivery Date'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_goods_total') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_goods_total',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Goods Total'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_goods_total_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_goods_total_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Goods Total Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_carriage_amount') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_carriage_amount',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Carriage Amount'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_carriage_amount_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_carriage_amount_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Carriage Amount Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_grand_total') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_grand_total',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Grand Total'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_grand_total_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_grand_total_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Grand Total Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_basket_erp_quote_number') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_basket_erp_quote_number',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 20,
                            'comment' => 'Basket Erp Quote Number'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_erp_account_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_erp_account_id',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 11,
                            'comment' => 'ERP Account'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_delivery_type') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_delivery_type',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 25,
                            'default' => '',
                            'comment' => 'Delivery type, full / partial'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_quote_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_quote_id',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 10,
                            'unsigned' => true,
                            'comment' => 'Epicor Quote Id'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_erp_quote_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_erp_quote_id',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 20,
                            'comment' => 'Erp Quotes Id'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_lowest_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_lowest_price',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '16,4',
                            'default' => '0.0000',
                            'comment' => 'Lowest Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_customer_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_customer_id',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 11,
                            'comment' => 'Sales Rep Customer ID'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_chosen_customer_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_chosen_customer_id',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 11,
                            'comment' => 'Sales Rep Chosen Customer ID'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_chosen_customer_info') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_chosen_customer_info',
                        [
                            'type' => Table::TYPE_TEXT,
                            'comment' => 'Sales Rep Chosen Customer Info'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_contract_code') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_contract_code',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'ECC Contract Code'
                        ]
                    );
            }
        }

        $tableName = $installer->getTable('quote_item');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_price',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Item Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_price_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_price_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Item Price Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_line_value') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_line_value',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Line Value'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_line_value_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_line_value_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Line Value Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_original_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_original_price',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'Original Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_line_comment') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_line_comment',
                        [
                            'type' => Table::TYPE_TEXT,
                            'comment' => 'ERP Account'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_required_date') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_required_date',
                        [
                            'type' => Table::TYPE_DATE,
                            'comment' => 'Required Date'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_delivery_deferred') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_delivery_deferred',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'default' => '0',
                            'comment' => 'Deferred delivery'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_location_code') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_location_code',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'ECC Location Code'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_location_name') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_location_name',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'ECC Location Name'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_gqr_line_number') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_gqr_line_number',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'ECC GQR Line Number'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_msq_base_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_msq_base_price',
                        [
                            'type' => Table::TYPE_FLOAT,
                            'comment' => 'ECC MSQ Base Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_price',
                        [
                            'type' => Table::TYPE_FLOAT,
                            'comment' => 'Sales Rep Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_discount') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_discount',
                        [
                            'type' => Table::TYPE_FLOAT,
                            'comment' => 'Sales Rep Discount'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_salesrep_rule_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_salesrep_rule_price',
                        [
                            'type' => Table::TYPE_FLOAT,
                            'comment' => 'Sales Rep Rule Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_contract_code') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_contract_code',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'ECC Contract Code'
                        ]
                    );
            }
        }

        $tableName = $installer->getTable('quote_address');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_erp_address_code') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_erp_address_code',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' => 'Erp address code'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_goods_total') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_goods_total',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Goods Total'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_goods_total_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_goods_total_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Goods Total Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_carriage_amount') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_carriage_amount',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Carriage Amount'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_carriage_amount_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_carriage_amount_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Carriage Amount Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_grand_total') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_grand_total',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Grand Total'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_grand_total_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_grand_total_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Grand Total Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_mobile_number') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_mobile_number',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 15,
                            'comment' => 'Mobile Phone'
                        ]
                    );
            }
        }

        $tableName = $installer->getTable('quote_address_item');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_price',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Item Price'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_price_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_price_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Item Price Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_line_value') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_line_value',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Line Value'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_bsv_line_value_inc') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_bsv_line_value_inc',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'BSV Line Value Incl. Tax'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_original_price') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_original_price',
                        [
                            'type' => Table::TYPE_DECIMAL,
                            'length' => '12,4',
                            'comment' => 'Original Price'
                        ]
                    );
            }
        }

        $connection = $installer->getConnection();

        //sales_order table
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_gor_sent');
        $connection->addColumn(
            $installer->getTable('sales_order'), 'ecc_gor_sent',
            [
                'identity' => false,
                'nullable' => false,
                'primary' => false,
                'type' => Table::TYPE_SMALLINT,
                'length' => 1,
                'default' => 0,
                'comment' => 'Gor Sent'
            ]);
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_gor_message');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_gor_message',
            array(
                'identity' => false,
                'nullable' => false,
                'primary' => false,
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'default' => 'Order Not Sent',
                'comment' => 'Gor Message'
            ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_erp_order_number');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_erp_order_number',
            array(
                'identity' => false,
                'nullable' => true,
                'primary' => false,
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'ERP Order Number'
            ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_last_sod_update');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_last_sod_update', array(
            'identity' => false,
            'nullable' => true,
            'primary' => false,
            'type' => Table::TYPE_DATE,
            'comment' => 'Last update from ERP'
        ));

        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_required_date');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_required_date',
            array(
                'type' => Table::TYPE_DATE,
                'nullable' => false,
                'default' => '0000-00-00 00:00:00',
                'comment' => 'Require Date',
            ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_initial_grand_total');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_initial_grand_total', array(
            'type' => Table::TYPE_DECIMAL,
            'comment' => 'Initial Grand Total',
            'length' => '12,4',
            'nullable' => true
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_device_used');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_device_used', array(
            'type' => Table::TYPE_TEXT,
            'comment' => 'Device Used',
            'nullable' => true,
            'length' => 255
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_customer_order_ref');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_customer_order_ref', array(
            'type' => Table::TYPE_TEXT,
            'comment' => 'Customer Order Ref',
            'nullable' => true,
            'length' => 255
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_bsv_goods_total');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_bsv_goods_total', array(
            'type' => Table::TYPE_DECIMAL,
            'comment' => 'BSV Goods Total',
            'nullable' => true,
            'length' => '12,4'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_bsv_goods_total_inc');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_bsv_goods_total_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => true,
            'comment' => 'BSV Goods Total Incl. Tax'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_bsv_carriage_amount');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_bsv_carriage_amount', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => true,
            'comment' => 'BSV Carriage Amount'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_bsv_carriage_amount_inc');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_bsv_carriage_amount_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => true,
            'comment' => 'BSV Carriage Amount Incl. Tax'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_bsv_grand_total');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_bsv_grand_total', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => true,
            'comment' => 'BSV Grand Total'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_bsv_grand_total_inc');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_bsv_grand_total_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => true,
            'comment' => 'BSV Grand Total Incl. Tax'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_basket_erp_quote_number');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_basket_erp_quote_number', array(
            'type' => Table::TYPE_TEXT,
            'length' => '20',
            'nullable' => true,
            'comment' => 'Basket Erp Quote Number'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_print_pick_note');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_print_pick_note',
            array(
                'identity' => false,
                'nullable' => false,
                'primary' => false,
                'type' => Table::TYPE_SMALLINT,
                'length' => 1,
                'default' => 0,
                'comment' => 'Printed Pick Note'
            ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_erp_account_id');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_erp_account_id', array(
            'type' => Table::TYPE_INTEGER,
            'length' => 11,
            'comment' => 'ERP Account'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_delivery_type');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_delivery_type', array(
            'type' => Table::TYPE_TEXT,
            'length' => 25,
            'comment' => 'Delivery type, full / partial',
            'default' => ''
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_quote_id');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_quote_id', array(
            'type' => Table::TYPE_INTEGER,
            'length' => 10,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'Epicor Quotes Id'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_erp_quote_id');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_erp_quote_id', array(
            'type' => Table::TYPE_TEXT,
            'length' => 20,
            'nullable' => true,
            'comment' => 'Erp Quotes Id'
        ));

        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_salesrep_customer_id');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_salesrep_customer_id', array(
            'type' => Table::TYPE_INTEGER,
            'comment' => 'Sales Rep Customer ID'
        ));
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_salesrep_chosen_customer_id');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_salesrep_chosen_customer_id', array(
            'type' => Table::TYPE_INTEGER,
            'comment' => 'Sales Rep Chosen Customer ID'
        ));

        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_salesrep_chosen_customer_info');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_salesrep_chosen_customer_info', array(
            'type' => Table::TYPE_TEXT,
            'length' => '64k',
            'comment' => 'Sales Rep Chosen Customer Info'
        ));

        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_contract_code');
        $connection->addColumn($installer->getTable('sales_order'), 'ecc_contract_code', array(
            'type' => Table::TYPE_TEXT,
            'length' => '255',
            'comment' => 'ECC Contract Code'
        ));

        //sales_order_item
        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_bsv_price');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_bsv_price', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Item Price'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_bsv_price_inc');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_bsv_price_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Item Price Incl. Tax'
        ));


        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_bsv_line_value');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_bsv_line_value', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Line Value'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_bsv_line_value_inc');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_bsv_line_value_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Line Value Incl. Tax'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_original_price');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_original_price', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'Original Price'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_line_comment');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_line_comment', array(
            'type' => Table::TYPE_TEXT,
            'length' => '64k',
            'comment' => 'ECC Line Comment'
        ));
        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_required_date');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_required_date', array(
            'type' => Table::TYPE_DATE,
            'comment' => 'Required Date'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_delivery_deferred');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_delivery_deferred', array(
            'type' => Table::TYPE_BOOLEAN,
            'length' => null,
            'comment' => 'Deferred delivery',
            'default' => 0
        ));
        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_location_code');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_location_code', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'ECC Location Code'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_location_name');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_location_name', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'ECC Location Name'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_gqr_line_number');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_gqr_line_number', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'ECC GQR Line Number'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_salesrep_price');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_salesrep_price', array(
            'type' => Table::TYPE_FLOAT,
            'comment' => 'Sales Rep Price'
        ));

        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_salesrep_discount');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_salesrep_discount', array(
            'type' => Table::TYPE_FLOAT,
            'comment' => 'Sales Rep Discount'
        ));
        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_salesrep_rule_price');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_salesrep_rule_price', array(
            'type' => Table::TYPE_FLOAT,
            'comment' => 'Sales Rep Rule Price'
        ));
        $connection->dropColumn($installer->getTable('sales_order_item'), 'ecc_contract_code');
        $connection->addColumn($installer->getTable('sales_order_item'), 'ecc_contract_code', array(
            'type' => Table::TYPE_TEXT,
            'length' => '255',
            'comment' => 'ECC Contract Code'
        ));


        //sales_order_address
        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_erp_address_code');
        $connection->addColumn($installer->getTable('sales_order_address'),
            'ecc_erp_address_code',
            array(
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Erp address code'
            ));

        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_bsv_goods_total');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_bsv_goods_total', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Goods Total'
        ));


        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_bsv_goods_total_inc');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_bsv_goods_total_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Goods Total Incl. Tax'
        ));
        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_bsv_carriage_amount');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_bsv_carriage_amount', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Carriage Amount'
        ));

        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_bsv_carriage_amount_inc');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_bsv_carriage_amount_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Carriage Amount Incl. Tax'
        ));

        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_bsv_grand_total');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_bsv_grand_total', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Grand Total'
        ));

        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_bsv_grand_total_inc');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_bsv_grand_total_inc', array(
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'BSV Grand Total Incl. Tax'
        ));

        $connection->dropColumn($installer->getTable('sales_order_address'), 'ecc_mobile_number');
        $connection->addColumn($installer->getTable('sales_order_address'), 'ecc_mobile_number', array(
            'type' => Table::TYPE_TEXT,
            'length' => '15',
            'comment' => 'Mobile Phone'
        ));

        //catalog_product_option
        $connection->dropColumn($installer->getTable('catalog_product_option'), 'ecc_code');
        $connection->addColumn($installer->getTable('catalog_product_option'), 'ecc_code', array(
            'type' => Table::TYPE_TEXT,
            'length' => '255',
            'comment' => 'Code From ERP'
        ));

        $connection->dropColumn($installer->getTable('catalog_product_option'), 'ecc_default_value');
        $connection->addColumn($installer->getTable('catalog_product_option'), 'ecc_default_value', array(
            'type' => Table::TYPE_TEXT,
            'length' => '255',
            'comment' => 'Default Value'
        ));

        $connection->dropColumn($installer->getTable('catalog_product_option'), 'ecc_validation_code');
        $connection->addColumn($installer->getTable('catalog_product_option'), 'ecc_validation_code', array(
            'type' => Table::TYPE_TEXT,
            'length' => '255',
            'comment' => 'Validation Code'
        ));

        //store_group
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_company');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_company', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Company'
        ));
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_site');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_site', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Site'
        ));
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_warehouse');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_warehouse', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Warehouse'
        ));
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_group');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_group', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Group'
        ));
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_brandimage');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_brandimage', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Brand Image'
        ));
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_storeswitcher');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_storeswitcher', array(
            'type' => Table::TYPE_SMALLINT,
            'length' => 1,
            'comment' => 'Use in Store Switcher',
            'default' => 1,
        ));
        $connection->dropColumn($installer->getTable('store_group'), 'ecc_allowed_customer_types');
        $connection->addColumn($installer->getTable('store_group'), 'ecc_allowed_customer_types', array(
            'nullable' => true,
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Allowed Customer Types',
            'default' => null
        ));

        //store_website
        $connection->dropColumn($installer->getTable('store_website'), 'ecc_company');
        $connection->addColumn($installer->getTable('store_website'), 'ecc_company', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Company'
        ));
        $connection->dropColumn($installer->getTable('store_website'), 'ecc_site');
        $connection->addColumn($installer->getTable('store_website'), 'ecc_site', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Site'
        ));
        $connection->dropColumn($installer->getTable('store_website'), 'ecc_warehouse');
        $connection->addColumn($installer->getTable('store_website'), 'ecc_warehouse', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Warehouse'
        ));
        $connection->dropColumn($installer->getTable('store_website'), 'ecc_group');
        $connection->addColumn($installer->getTable('store_website'), 'ecc_group', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Group'
        ));
        $connection->dropColumn($installer->getTable('store_website'), 'ecc_allowed_customer_types');
        $connection->addColumn($installer->getTable('store_website'), 'ecc_allowed_customer_types', array(
            'nullable' => true,
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Allowed Customer Types',
            'default' => 'all'
        ));
        
        $connection->dropColumn($installer->getTable('eav_attribute_set'), 'ecc_created_by');
        $connection->addColumn($installer->getTable('eav_attribute_set'), 'ecc_created_by', array(
            'nullable' => true,
            'type' => Table::TYPE_TEXT,
            'length' => 10,
            'comment' => 'Created By',
            'default' => 'N'
        ));
        
        $connection->dropColumn($installer->getTable('eav_attribute'), 'ecc_created_by');
        $connection->addColumn($installer->getTable('eav_attribute'), 'ecc_created_by', array(
            'nullable' => true,
            'type' => Table::TYPE_TEXT,
            'length' => 10,
            'comment' => 'Created By',
            'default' => 'N'
        ));
    }

    protected function version_1_0_5(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->dropColumn($installer->getTable('catalog_eav_attribute'), 'ecc_used_in_search_ordering');
        $connection->addColumn($installer->getTable('catalog_eav_attribute'), 'ecc_used_in_search_ordering', array(
            'type' => Table::TYPE_SMALLINT,
            'length' => 1,
            'comment' => 'Used in search ordering',
            'default' => 0
        ));

        $connection->dropColumn($installer->getTable('catalog_eav_attribute'), 'ecc_weighting');
        $connection->addColumn($installer->getTable('catalog_eav_attribute'), 'ecc_weighting', array(
            'nullable' => true,
            'unsigned' => true,
            'type' => Table::TYPE_INTEGER,
            'length' => 10,
            'comment' => 'Search Weighting',
            'default' => 1
        ));
    }

    protected function version_1_0_6(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('quote_payment');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_elements_payment_account_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_elements_payment_account_id',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '256',
                            'nullable' => true,
                            'comment' => 'Elements Payment Account Id'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_elements_processor_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_elements_processor_id',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '256',
                            'nullable' => true,
                            'comment' => 'Elements Processor Id'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_elements_transaction_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_elements_transaction_id',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 10,
                            'nullable' => true,
                            'unsigned' => true,
                            'comment' => 'Elements Transaction Id'
                        ]
                    );
            }  


            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_cc_cvv_status') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_cc_cvv_status',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 20,
                            'nullable' => true,
                            'comment' => 'Credit Card CVV Status'
                        ]
                    );
            }  


            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_cc_auth_code') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_cc_auth_code',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => true,
                            'comment' => 'Credit Card Authorization Code'
                        ]
                    );
            }  
        }

        //sales_order payment table for elements
        $tableName = $installer->getTable('sales_order_payment');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_elements_payment_account_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_elements_payment_account_id',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '256',
                            'nullable' => true,
                            'comment' => 'Elements Payment Account Id'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_elements_processor_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_elements_processor_id',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '256',
                            'nullable' => true,
                            'comment' => 'Elements Processor Id'
                        ]
                    );
            }



            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_elements_transaction_id') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_elements_transaction_id',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 10,
                            'nullable' => true,
                            'unsigned' => true,
                            'comment' => 'Elements Transaction Id'
                        ]
                    );
            }  


            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_cc_cvv_status') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_cc_cvv_status',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 20,
                            'nullable' => true,
                            'comment' => 'Credit Card CVV Status'
                        ]
                    );
            }  


            if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_cc_auth_code') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'ecc_cc_auth_code',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => true,
                            'comment' => 'Credit Card Authorization Code'
                        ]
                    );
            }  

        } 
    }    

    
    protected function version_1_0_7(SchemaSetupInterface $installer)
    {
        $conn = $installer->getConnection();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_esdm_token')
        );
        /* @var $table Varien_Db_Ddl_Table */
        $table->addColumn('entity_id', Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
                ), 'Entity ID');

        $table->addColumn('customer_id', Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true,
                ), 'Customer Id');

        $table->addColumn('ccv_token', Table::TYPE_TEXT, 54, array(), 'CCV Token');
        $table->addColumn('cvv_token', Table::TYPE_TEXT, 54, array(), 'CVV Token');

        $table->addColumn('card_type', Table::TYPE_TEXT, 12, array(
            'nullable' => true,
                ), 'Card Type');

        $table->addColumn('last_four', Table::TYPE_TEXT, 4, array(), 'Last 4 Digits');

        $table->addColumn('expiry_date', Table::TYPE_TIMESTAMP, null, array(), 'Expiry Date');

        $table->addColumn('reuseable', Table::TYPE_BOOLEAN, null, array(
            'default' => false,
            'nullable' => false,
                ), 'Re-Usable Token');

        $conn->createTable($table);
        
        $conn->dropColumn($installer->getTable('sales_order_payment'), 'ecc_ccv_token');
        $conn->addColumn($installer->getTable('sales_order_payment'), 'ecc_ccv_token', array(
            'type' => Table::TYPE_TEXT,
            'length' => 54,
            'comment' => 'CCV Token'
        ));
        
        $conn->dropColumn($installer->getTable('sales_order_payment'), 'ecc_cvv_token');
        $conn->addColumn($installer->getTable('sales_order_payment'), 'ecc_cvv_token', array(
            'type' => Table::TYPE_TEXT,
            'length' => 54,
            'comment' => 'CVV Token'
        ));
        
        $conn->dropColumn($installer->getTable('quote_payment'), 'ecc_ccv_token');
        $conn->addColumn($installer->getTable('quote_payment'), 'ecc_ccv_token', array(
            'type' => Table::TYPE_TEXT,
            'length' => 54,
            'comment' => 'CCV Token'
        ));
        
        $conn->dropColumn($installer->getTable('quote_payment'), 'ecc_cvv_token');
        $conn->addColumn($installer->getTable('quote_payment'), 'ecc_cvv_token', array(
            'type' => Table::TYPE_TEXT,
            'length' => 54,
            'comment' => 'CVV Token'
        ));
    }     
    
    
    protected function version_1_0_9(SchemaSetupInterface $installer)
    {
        $conn = $installer->getConnection();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_cre_token')
        );
        /* @var $table Varien_Db_Ddl_Table */
        $table->addColumn('entity_id', Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
                ), 'Entity ID');

        $table->addColumn('customer_id', Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true,
                ), 'Customer Id');

        $table->addColumn('ccv_token', Table::TYPE_TEXT, 54, array(), 'CCV Token');
        $table->addColumn('cvv_token', Table::TYPE_TEXT, 54, array(), 'CVV Token');

        $table->addColumn('card_type', Table::TYPE_TEXT, 12, array(
            'nullable' => true,
                ), 'Card Type');

        $table->addColumn('last_four', Table::TYPE_TEXT, 4, array(), 'Last 4 Digits');

        $table->addColumn('expiry_date', Table::TYPE_TIMESTAMP, null, array(), 'Expiry Date');

        $table->addColumn('reuseable', Table::TYPE_BOOLEAN, null, array(
            'default' => false,
            'nullable' => false,
                ), 'Re-Usable Token');

        $conn->createTable($table);
        
        $conn->dropColumn($installer->getTable('sales_order_payment'), 'cre_transaction_id');
        $conn->addColumn($installer->getTable('sales_order_payment'), 'cre_transaction_id', array(
            'type' => Table::TYPE_TEXT,
            'length' => 256,
            'comment' => 'Cre Transaction Id'
        ));
        
        $conn->dropColumn($installer->getTable('quote_payment'), 'cre_transaction_id');
        $conn->addColumn($installer->getTable('quote_payment'), 'cre_transaction_id', array(
            'type' => Table::TYPE_TEXT,
            'length' => 256,
            'comment' => 'Cre Transaction Id'
        ));

    }  
    
    
    
    protected function version1_1_2(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->changeColumn(
        $installer->getTable('ecc_syn_log'),
            'types',
            'types',
            [
             'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             'length' => '4G'
            ]
        );
          

    }
    
    
    protected function version1_1_3(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->changeColumn(
        $installer->getTable('ecc_elements_transaction'),
            'order_id',
            'order_id',
            [
             'type' =>  Table::TYPE_TEXT,
             'length' => '100',
             'comment' => 'Order Id'
            ]
        );
        
        $installer->getConnection()->changeColumn(
        $installer->getTable('ecc_elements_transaction'),
            'billing_address1',
            'billing_address',
            [
             'type' =>  Table::TYPE_TEXT,
             'comment' => 'Billing Address'
            ]
        );        
    }    
    protected function version1_1_4(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $eavTable = $setup->getTable('catalog_eav_attribute');
        // Check if the column exists
        if ($setup->getConnection()->tableColumnExists($eavTable, 'ecc_weighting') == true) {
            $connection->dropColumn($setup->getTable($eavTable), 'ecc_weighting');
        }
    }    
    protected function version1_1_6(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $eavTable = $setup->getTable('catalog_eav_attribute');
        
        // Check if the column exists
        if ($setup->getConnection()->tableColumnExists($eavTable, 'ecc_used_in_search_ordering') == true) {
            $connection->dropColumn($setup->getTable($eavTable), 'ecc_used_in_search_ordering');
        }
    }
    protected function version1_1_8(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('sales_shipment_item');
        if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_erp_shipment_number') == false) {
            $installer->getConnection()
                ->addColumn(
                    $tableName,
                    'ecc_erp_shipment_number',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'ECC ERP Shipment Number'
                    ]
                );
        }
    }
    
    protected function version1_2_2(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_customer_return');
        if ($installer->getConnection()->tableColumnExists($tableName, 'web_returns_number') == false) {
            $installer->getConnection()
                ->addColumn(
                    $tableName,
                    'web_returns_number',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'ECC ERP Web Returns Number',
                        'after' => 'erp_returns_number'
                    ]
                );
        }               
    }
    
    protected function version1_2_3(SchemaSetupInterface $installer)
    {             
        $tableName = $installer->getTable('ecc_quote');
        if ($installer->getConnection()->tableColumnExists($tableName, 'reference') == false) {
            $installer->getConnection()
                ->addColumn(
                    'ecc_quote',
                    'reference',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'web Reference',
                        'after' => 'entity_id'
                    ]
                );
        }
    }
    
    protected function version1_2_5(SchemaSetupInterface $installer) 
    {
        $conn = $installer->getConnection();
        
        $conn->dropColumn($installer->getTable('quote_payment'), 'ecc_site_url');
        $conn->addColumn($installer->getTable('quote_payment'), 'ecc_site_url', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Site Url'
        ));
        
        $conn->dropColumn($installer->getTable('sales_order_payment'), 'ecc_site_url');
        $conn->addColumn($installer->getTable('sales_order_payment'), 'ecc_site_url', array(
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Site Url'
        ));
        
        $conn->dropColumn($installer->getTable('quote_payment'), 'ecc_is_saved');
        $conn->addColumn($installer->getTable('quote_payment'), 'ecc_is_saved', array(
            'type' => Table::TYPE_BOOLEAN,
            'default' => false,
            'comment' => 'Is Saved Token'
        ));
        
        $conn->dropColumn($installer->getTable('sales_order_payment'), 'ecc_is_saved');
        $conn->addColumn($installer->getTable('sales_order_payment'), 'ecc_is_saved', array(
            'type' => Table::TYPE_BOOLEAN,
            'default' => false,
            'comment' => 'Is Saved Token'
        ));
    }
    protected function version1_2_6(SchemaSetupInterface $installer) 
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'sou_invoice_options') == false) {
                    $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'sou_invoice_options',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => null,
                            'comment' => 'Send Invoice Email on SOU raiseInvoice'
                        ]
                    );
            }
        }
    }    
    protected function version1_2_7(SchemaSetupInterface $installer) 
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'sou_shipment_options') == false) {
                    $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'sou_shipment_options',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => null,
                            'comment' => 'Send Shipment Email when SOU sends new shipment details'
                        ]
                    );
            }
        }    
        $tableName = $installer->getTable('sales_shipment_track');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'url') == false) {
                    $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'url',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => true,
                            'default' => null,
                            'comment' => 'Tracking URL'
                        ]
                    );
            }
        }
    } 
    
    protected function version1_2_9(SchemaSetupInterface $installer) 
    {
        //sales_order table
        $connection = $installer->getConnection();
        $connection->dropColumn($installer->getTable('sales_order'), 'ecc_gor_sent_count');
        $connection->addColumn(
	$installer->getTable('sales_order'), 'ecc_gor_sent_count',
	[
		'identity' => false,
		'nullable' => false,
		'primary' => false,
		'type' => Table::TYPE_SMALLINT,
		'length' => 1,
		'default' => 0,
		'comment' => 'Gor Sent Count',
		'after' => 'ecc_gor_message'
	]);
    }

    protected function version1_3_2(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('ecc_customer_return_line'),
            'qty_ordered',
            'qty_ordered',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('ecc_customer_return_line'),
            'qty_returned',
            'qty_returned',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
            ]
        );
    }

    protected function version1_3_3(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addIndex(
            $installer->getTable('ecc_message_log'),
            $installer->getIdxName('ecc_message_log', ['message_category']),
            ['message_category']
        );
    }

    protected function version1_3_4(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addIndex(
            $installer->getTable('ecc_location_product_currency'),
            $installer->getIdxName('ecc_location_product_currency', ['product_id']),
            ['product_id']
        );
    }

    /**
     * Float custom column create issue from 2.3.0
     * Type(10,0) should remove from column
     *
     * @param SchemaSetupInterface $installer
     */
    protected function version1_3_5(SchemaSetupInterface $installer)
    {
        $FloatUpdateData = array(
            "quote_item" => array(
                "ecc_msq_base_price" => "ECC MSQ Base Price",
                "ecc_salesrep_price" => "Sales Rep Price",
                "ecc_salesrep_discount" => "Sales Rep Discount",
                "ecc_salesrep_rule_price" => "Sales Rep Rule Price"
            ),
            "sales_order_item" => array(
                "ecc_salesrep_price" => "Sales Rep Price",
                "ecc_salesrep_discount" => "Sales Rep Discount",
                "ecc_salesrep_rule_price" => "Sales Rep Rule Price"
            ),
            "ecc_erp_account" => array(
                "balance" => "Balance",
                "credit_limit" => "Credit Limit",
                "unallocated_cash" => "Unallocated Cash",
                "last_payment_value" => "	Last Payment Value"
            ),
            "ecc_contract_product" => array(
                "min_order_qty" => "Minimum Order Qty",
                "max_order_qty" => "Maximum Order Qty"
            ),
            "ecc_list_product" => array(
                "qty" => "Product Qty"
            )
        );
        $connection = $installer->getConnection();
        foreach ($FloatUpdateData as $tableName => $columns) {
            foreach ($columns as $columnName => $comment) {
                $query = "ALTER TABLE $tableName MODIFY $columnName float NULL COMMENT '$comment'";
                $connection->query($query);
            }
        }
    }

    protected function version1_3_8(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_mapping_products');
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
                    'product_sku',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Product SKU'
                )
                ->addColumn(
                    'product_uom',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => true],
                    'Product UOM'
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

    private function version1_3_8_6(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_list');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'customer_exclusion') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'customer_exclusion',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => false,
                            'default' => 'N',
                            'comment' => 'customer exclusion flag'
                        ]
                    );
            }
        }
    }

    private function version1_3_8_7(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_location');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'location_visible') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'location_visible',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'length' => 1,
                            'default' => 1,
                            'comment' => 'Location Visible'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'include_inventory') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'include_inventory',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'length' => 1,
                            'default' => 1,
                            'comment' => 'Include Inventory'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'show_inventory') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'show_inventory',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'length' => 1,
                            'default' => 1,
                            'comment' => 'Show Inventory'
                        ]
                    );
            }
        }

        // Install Related Location Table
        $relatedlocationsTableName = $installer->getTable('ecc_location_relatedlocations');
        if ($installer->getConnection()->isTableExists($relatedlocationsTableName) != true) {
            $relatedlocationsTable = $installer->getConnection()->newTable(
                $relatedlocationsTableName
            );
            $relatedlocationsTable
                ->addColumn('id', Table::TYPE_INTEGER, 10, array(
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ), 'ID'
                )
                ->addColumn('location_id',Table::TYPE_INTEGER, 10, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Location ID'
                )
                ->addColumn('related_location_id',Table::TYPE_INTEGER, 10, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Related Location ID'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['id', 'location_id']),
                    ['id', 'location_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $relatedlocationsTableName,
                        'location_id',
                        $tableName,
                        'id'
                    ),
                    'location_id',
                    $tableName,
                    'id',
                    Table::ACTION_CASCADE
                );
            $installer->getConnection()->createTable($relatedlocationsTable);
        }

        // Install Location Groups table
        $groupingsLocationTableName = $installer->getTable('ecc_location_groups');
        if ($installer->getConnection()->isTableExists($groupingsLocationTableName) != true) {
            $groupingsLocationTable = $installer->getConnection()->newTable(
                $groupingsLocationTableName
            );
            $groupingsLocationTable
                ->addColumn('id', Table::TYPE_INTEGER, 10, array(
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ), 'ID'
                )
                ->addColumn('group_name',Table::TYPE_TEXT, 255, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Group Name'
                )
                ->addColumn('group_expandable',Table::TYPE_BOOLEAN, 1, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Group Expandable'
                )
                ->addColumn('show_aggregate_stock',Table::TYPE_BOOLEAN, 1, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Show Aggregate Stock'
                )
                ->addColumn('enabled',Table::TYPE_BOOLEAN, 1, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Enabled'
                )
                ->addColumn('order',Table::TYPE_INTEGER, 10, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Order'
                );
            $installer->getConnection()->createTable($groupingsLocationTable);
        }

        // Install Location mapping to Groups table
        $groupLocationsTableName = $installer->getTable('ecc_location_grouplocations');
        if ($installer->getConnection()->isTableExists($groupLocationsTableName) != true) {
            $groupLocationsTable = $installer->getConnection()->newTable(
                $groupLocationsTableName
            );
            $groupLocationsTable
                ->addColumn('id', Table::TYPE_INTEGER, 10, array(
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                ), 'ID'
                )
                ->addColumn('group_id', Table::TYPE_INTEGER, 10, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Group ID'
                )
                ->addColumn('group_location_id', Table::TYPE_INTEGER, 10, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Group Location ID'
                )
                ->addColumn('position', Table::TYPE_INTEGER, 10, array(
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false,
                ), 'Position'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['id', 'group_id']),
                    ['id', 'group_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        $groupLocationsTableName,
                        'group_id',
                        $groupingsLocationTableName,
                        'id'
                    ),
                    'group_id',
                    $groupingsLocationTableName,
                    'id',
                    Table::ACTION_CASCADE
                );
            $installer->getConnection()->createTable($groupLocationsTable);
        }
    }

    protected function version1_4_1(SchemaSetupInterface $installer)
    {
        $tab_ecclocationProduct = $installer->getTable('ecc_location_product');
        $tab_ecclocationProductCurrency = $installer->getTable('ecc_location_product_currency');
        $tab_catalogProductEntity = $installer->getTable('catalog_product_entity');

        if ($installer->tableExists($tab_ecclocationProduct)) {
            $installer->getConnection()->addForeignKey(
                $installer->getFkName($tab_catalogProductEntity, 'entity_id', $tab_ecclocationProduct, 'product_id'),
                $tab_ecclocationProduct,
                'product_id',
                $tab_catalogProductEntity,
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE);
        }
        if ($installer->tableExists($tab_ecclocationProductCurrency)) {
            $installer->getConnection()->addForeignKey(
                $installer->getFkName($tab_catalogProductEntity, 'entity_id', $tab_ecclocationProductCurrency, 'product_id'),
                $tab_ecclocationProductCurrency,
                'product_id',
                $tab_catalogProductEntity,
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE);
        }
    }
    protected function version1_4_2(SchemaSetupInterface $installer)
    {
        //install ecc_dealer_groups
        $installer->getConnection()->dropTable('ecc_dealer_groups');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_dealer_groups')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Dealer Group ID'
        );
        $table->addColumn('title', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Dealer Group Title'
        );
        $table->addColumn('code', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Group Code'
        );
        $table->addColumn('active', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => '0'
        ), 'Active');
        $table->addColumn('description', Table::TYPE_TEXT, '4G', array(
            'nullable' => true,
            'default' => false
        ), 'Description');
        $table->addColumn('dealer_accounts_exclusion', Table::TYPE_TEXT, 1, array(
            'nullable' => false,
            'default' => 'N'
        ), 'Dealer Accounts Exclusion');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_dealer_groups'),
                array('active')
            ),
            array('active')
        );
        $installer->getConnection()->createTable($table);

        //install ecc_dealer_groups_accounts
        $installer->getConnection()->dropTable('ecc_dealer_groups_accounts');
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_dealer_groups_accounts')
        );
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Incremental ID'
        );

        $table->addColumn('group_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Group ID'
        );
        $table->addColumn('dealer_account_id', Table::TYPE_INTEGER, 10, array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary' => false,
        ), 'Dealer Account ID'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_dealer_groups_accounts'),
                array('group_id', 'dealer_account_id'),
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('group_id', 'dealer_account_id'),
            array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_dealer_groups_accounts'),
                array('dealer_account_id')
            ),
            'dealer_account_id'
        );

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_erp_account'),
                'entity_id',
                $installer->getTable('ecc_dealer_groups_accounts'),
                'dealer_account_id'),
            'dealer_account_id',
            $installer->getTable('ecc_erp_account'), 'entity_id',
            Table::ACTION_CASCADE);

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_dealer_groups'),
                'id',
                $installer->getTable('ecc_dealer_groups_accounts'),
                'group_id'),
            'group_id',
            $installer->getTable('ecc_dealer_groups'), 'id',
            Table::ACTION_CASCADE);
        $installer->getConnection()->createTable($table);
    }

    private function version1_4_4(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');

        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'min_order_amount_flag') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'min_order_amount_flag',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 0,
                            'comment' => 'Min Order Amount Flag'
                        ]
                    );
            }
        }
    }

    protected function version1_4_5(SchemaSetupInterface $installer)
    {
        $tableNameErp = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableNameErp)) {
            if ($installer->getConnection()->tableColumnExists($tableNameErp, 'allowed_shipstatus_methods') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableNameErp, 'allowed_shipstatus_methods', [
                            'type' => Table::TYPE_TEXT,
                            'length' => '4G',
                            'nullable' => true,
                            'default' => null,
                            'unsigned' => true,
                            'primary' => false,
                            'comment' => 'Serialized array of ship status allowed for ERP account.',
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableNameErp,
                    'allowed_shipstatus_methods_exclude') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableNameErp, 'allowed_shipstatus_methods_exclude', [
                            'type' => Table::TYPE_TEXT,
                            'length' => '4G',
                            'nullable' => true,
                            'default' => null,
                            'unsigned' => true,
                            'primary' => false,
                            'comment' => 'Serialized array of ship status not allowed for ERP account.',
                        ]
                    );
            }
        }

        /* START of add column to quote and sales_order table(s) */
        if (!$installer->getConnection()->tableColumnExists($installer->getTable('quote'),
            'ecc_ship_status_erpcode')) {
            $installer->run("ALTER TABLE `{$installer->getTable('quote')}` ADD `ecc_ship_status_erpcode` VARCHAR(255) NOT NULL;");
        }
        if (!$installer->getConnection()->tableColumnExists($installer->getTable('quote'),
            'ecc_additional_reference')) {
            $installer->run("ALTER TABLE `{$installer->getTable('quote')}` ADD `ecc_additional_reference` VARCHAR(255) NOT NULL;");
        }
        if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales_order'),
            'ecc_ship_status_erpcode')) {
            $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `ecc_ship_status_erpcode` VARCHAR(255) NOT NULL;");
        }
        if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales_order'),
            'ecc_additional_reference')) {
            $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `ecc_additional_reference` VARCHAR(255) NOT NULL;");
        }
        /* END of add column to quote and sales_order table(s) */

        $tableName = $installer->getTable('ecc_erp_mapping_shippingstatus');
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
                    'shipping_status_code',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => false],
                    'ERP Ship status code'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    200,
                    ['nullable' => false],
                    'Ship Status Description'
                )
                ->addColumn(
                    'status_help',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Ship Status Help'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'default' => '0'],
                    'Store id value'
                )
                ->addColumn(
                    'is_default',
                    Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'default' => '1'],
                    'Default'
                );
            $installer->getConnection()->createTable($table);
        }
    }

    protected function version1_4_6(SchemaSetupInterface $installer)
    {
        $tableNameErp = $installer->getTable('sales_order');
        if ($installer->tableExists($tableNameErp)) {
            if ($installer->getConnection()->tableColumnExists($tableNameErp, 'ecc_gor_flow') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableNameErp, 'ecc_gor_flow', [
                            'type' => Table::TYPE_SMALLINT,
                            'size' => 5,
                            'nullable' => true,
                            'unsigned' => true,
                            'comment' => 'Ecc Gor Flow column shows Gor already sent for order',
                        ]
                    );
            }

        }
    }

    protected function version1_4_9(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'hide_price_options') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'hide_price_options',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => true,
                            'comment' => 'Hide Price Options, NULL: Default, 0: Disabled, 1: Enabled'
                        ]
                    );
            }
        }
    }

    public function version1_5_1(SchemaSetupInterface $setup)
    {
        $setup->startSetup();

        $quote = 'quote';
        $orderTable = 'sales_order';

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'hide_prices',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'Hide Price Options, NULL: Default, 0: Disabled, 1: Enabled'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'hide_prices',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'Hide Price Options, NULL: Default, 0: Disabled, 1: Enabled'
                ]
            );

        $setup->endSetup();
    }

    protected function version1_5_3(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('sales_invoice');
        if ($installer->getConnection()->tableColumnExists($tableName, 'ecc_erp_invoice_number') == false) {
            $installer->getConnection()
                ->addColumn(
                    $tableName,
                    'ecc_erp_invoice_number',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'ECC ERP Invoice Number'
                    ]
                );
        }
    }

    protected function version1_5_5(SchemaSetupInterface $installer)
    {
        if (!$installer->getConnection()->tableColumnExists($installer->getTable('ecc_list_product'),
            'location_code')) {
            $installer->run("ALTER TABLE `{$installer->getTable('ecc_list_product')}` ADD `location_code` VARCHAR(255) NULL;");
        }
    }

    protected function version1_5_6(SchemaSetupInterface $installer)
    {
        //updating Idexes for table 'ecc_list_product'
        $connection = $installer->getConnection();
        $existingForeignKeys = $connection->getForeignKeys(
            $installer->getTable('ecc_list_product')
        );

        foreach ($existingForeignKeys as $key) {
            $connection->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }

        $connection->dropIndex(
            $installer->getTable('ecc_list_product'),
            $installer->getIdxName(
                $installer->getTable('ecc_list_product'),
                ['list_id', 'sku'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            )
        );

        $connection->addIndex(
            $installer->getTable('ecc_list_product'),
            $installer->getIdxName(
                $installer->getTable('ecc_list_product'),
                ['list_id', 'sku', 'location_code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['list_id', 'sku', 'location_code'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        foreach ($existingForeignKeys as $key) {
            $connection->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }
    }
    protected function version1_6_0(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_mapping_attributes');
        if ($installer->tableExists($tableName)) {

            if ($installer->getConnection()->tableColumnExists($tableName, 'use_for_config') == true) {
                $installer->getConnection()->dropColumn($tableName, 'use_for_config');
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'quick_search') == true) {
                $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'quick_search',
                        'is_searchable',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Use in Search'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'search_weighting') == true) {
                $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'search_weighting',
                        'search_weight',
                        [
                            'type' => Table::TYPE_INTEGER,
                            'length' => 2,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Use in Search'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'advanced_search') == true) {
                $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'advanced_search',
                        'is_visible_in_advanced_search',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Advanced Search'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'use_in_layered_navigation') == true) {
                $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'use_in_layered_navigation',
                        'is_filterable',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Used in Layered Navigation'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'search_results') == true) {
                $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'search_results',
                        'is_filterable_in_search',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Used in Search Results Layered Navigation'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'visible_on_product_view') == true) {
                $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'visible_on_product_view',
                        'is_visible_on_front',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Visible on Catalog Pages on Storefront'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'is_comparable') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'is_comparable',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Comparable on Storefront'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'position') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'position',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'length' => 5,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Position'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'is_used_for_promo_rules') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'is_used_for_promo_rules',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Use for Promo Rule Conditions'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'is_html_allowed_on_front') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'is_html_allowed_on_front',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Allow HTML Tags on Storefront'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'used_in_product_listing') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'used_in_product_listing',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Used in Product Listing'
                        ]
                    );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'used_for_sort_by') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'used_for_sort_by',
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'length' => 1,
                            'identity' => false,
                            'nullable' => false,
                            'primary' => false,
                            'comment' => 'Used for Sorting in Product Listing'
                        ]
                    );
            }

        }
        $installer->endSetup();

    }

    protected function version1_6_1(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_location');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'company') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'company',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => true,
                            'comment' => 'Company',
                            'after' => 'name'
                        ]
                    );
                $installer->getConnection()
                    ->addIndex(
                        $tableName,
                        $installer->getIdxName($tableName, ['company']),
                        ['company']
                    );
            }
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function version1_6_5(SchemaSetupInterface $installer)
    {
        if (!$installer->getConnection()->tableColumnExists('ecc_list', 'is_position_order_set')) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('ecc_list'),
                    'is_position_order_set',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => false,
                        'default' => 0,
                        'primary' => false,
                        'comment' => 'flag to determine if the list uses the default order direction set in the config'
                    ]
                );

            $installer->endSetup();
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function version1_6_6(SchemaSetupInterface $installer)
    {
        if (!$installer->getConnection()->tableColumnExists('ecc_list_product', 'list_position')) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('ecc_list_product'),
                    'list_position',
                    [
                        'type' => Table::TYPE_BIGINT,
                        'size' => 10,
                        'unsigned' => true,
                        'nullable' => true,
                        'default' => null,
                        'primary' => false,
                        'comment' => 'Position to allow sorting'
                    ]
                );

            $installer->endSetup();
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function version1_6_7(SchemaSetupInterface $installer)
    {
        /**
         * Install product link types in table (catalog_product_link_type)
         */
        $catalogProductLinkTypeData = [
            'link_type_id' => Link::LINK_TYPE_SUBSTITUTE,
            'code' => Related::DATA_SCOPE_SUBSTITUTE
        ];

        $isInsert = $installer->getConnection()->insertOnDuplicate(
            $installer->getTable('catalog_product_link_type'),
            $catalogProductLinkTypeData
        );

        /**
         * install product link attributes position
         * in table catalog_product_link_attribute
         */
        if($isInsert) {
            $catalogProductLinkAttributeData = [
                'link_type_id' => Link::LINK_TYPE_SUBSTITUTE,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ];

            $installer->getConnection()->insert(
                $installer->getTable('catalog_product_link_attribute'),
                $catalogProductLinkAttributeData
            );
        }

        $installer->endSetup();
    }

    /**
     * Add new tracking url column in to
     * shipping method mapping table.
     *
     * @param SchemaSetupInterface $installer
     */
    protected function version1_7_4(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_mapping_shippingmethod');
        if ($installer->tableExists($tableName)
            && !$installer->getConnection()
                ->tableColumnExists($tableName, 'tracking_url')
        ) {

            $installer->getConnection()
                ->addColumn(
                    $tableName,
                    'tracking_url',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'comment'  => 'Tracking Url',
                        'after'    => 'shipping_method_code',
                    ]
                );

        }
    }

}