<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{


    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    )
    {
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
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
             $this->version_1_0_1($installer);
        }
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
             $this->version_1_0_2($installer);
        }
        if (version_compare($context->getVersion(), "1.0.4", "<")) {
             $this->version_1_0_4($installer);
        }
        if (version_compare($context->getVersion(), "1.0.5", "<")) {
             $this->version_1_0_5($installer);
        }
        if (version_compare($context->getVersion(), "1.0.6", "<")) {
             $this->version_1_0_6($installer);
        }
        if (version_compare($context->getVersion(), "1.0.7", "<")) {
             $this->version_1_0_7($installer);
        }
        if (version_compare($context->getVersion(), "1.0.9", "<")) {
             $this->version_1_0_9($installer);
        }
        if (version_compare($context->getVersion(), "1.1.4", "<")) {
             $this->version_1_1_4($installer);
        }
        if (version_compare($context->getVersion(), "1.1.6", "<")) {
            $this->version_1_1_6($installer);
        }
        if (version_compare($context->getVersion(), "1.1.7", "<")) {
            $this->version_1_1_7($installer);
        }
        if (version_compare($context->getVersion(), "1.2.1", "<")) {
            $this->version_1_2_1($installer);
        }
        if (version_compare($context->getVersion(), "1.2.2", "<")) {
            $this->version_1_2_2($installer);
        }
        if (version_compare($context->getVersion(), "1.2.3", "<")) {
            $this->version_1_2_3($installer);
        }
        $installer->endSetup();
    }
    
    protected function version_1_0_1(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'account_type') == true) {
                    $installer->getConnection()
                    ->changeColumn(
                        $tableName,
                        'account_type',    
                        'account_type',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '20',
                            'nullable' => false,
                            'default' => 'Customer',
                            'comment' => 'Account Type'
                        ]
                    );
            }
        }
    }
    protected function version_1_0_2(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'is_toggle_allowed') == false) {
                    $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'is_toggle_allowed',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Is Toggle Allowed, 2: Default, 0: Disabled, 1: Enabled'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'login_mode_type') == false) {
                    $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'login_mode_type',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 20,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => 'Login Mode Type , 2: Default, dealer: Dealer, shopper: End Customer'
                        ]
                    );
            }
        }
    }
    
    protected function version_1_0_4(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->dropTable('ecc_pac');
        $table_Epicor_Dealerconnect_ecc_pac_attribute = $installer->getConnection()->newTable($installer->getTable('ecc_pac'));
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
            'unsigned' => false,
            'auto_increment' => true
        ), 'Entity ID');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute->addColumn('attribute_class_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 60, array(
            'nullable' => false
        ), 'Class Id Of The Attribute');
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute->addColumn('description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
            'nullable' => true
        ), 'Description Of Attribute Class Id');
        
        $installer->getConnection()->createTable($table_Epicor_Dealerconnect_ecc_pac_attribute);
        
        $installer->getConnection()->dropTable('ecc_pac_attributes');
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class = $installer->getConnection()->newTable($installer->getTable('ecc_pac_attributes'));
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
            'unsigned' => false,
            'auto_increment' => true
        ), 'Entity ID');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addColumn('class_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'unsigned' => false
        ), 'Parent Class Id');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addColumn('attribute_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 60, array(
            'nullable' => true
        ), 'Attribute Id');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addColumn('description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
            'nullable' => true
        ), 'Description of Attribute Id');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addColumn('label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 90, array(
            'nullable' => true
        ), 'label');
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addColumn('datatype', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 90, array(
            'nullable' => false
        ), 'DataType of Attribute Id');
        
        

        $table_Epicor_Dealerconnect_ecc_pac_attribute_class->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_pac'),
                'entity_id',
                $installer->getTable('ecc_pac_attributes'),
                'class_id'),
            'class_id',
            $installer->getTable('ecc_pac'), 'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE);                  
        
        
        $installer->getConnection()->createTable($table_Epicor_Dealerconnect_ecc_pac_attribute_class);
        
        $installer->getConnection()->dropTable('ecc_pac_attributes_option');
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_values = $installer->getConnection()->newTable($installer->getTable('ecc_pac_attributes_option'));
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_values->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
            'unsigned' => false,
            'auto_increment' => true
        ), 'Entity Id');
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_values->addColumn('parent_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'unsigned' => false
        ), 'Parent Id of Attribute Option');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_values->addColumn('code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
            'nullable' => true
        ), 'Attribute Option Code');
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_values->addColumn('description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
            'nullable' => true
        ), 'Attribute Option Description');
        
        
        $table_Epicor_Dealerconnect_ecc_pac_attribute_values->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_pac_attributes'),
                'entity_id',
                $installer->getTable('ecc_pac_attributes_option'),
                'parent_id'),
            'parent_id',
            $installer->getTable('ecc_pac_attributes'), 'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE);     
        
        
        $installer->getConnection()->createTable($table_Epicor_Dealerconnect_ecc_pac_attribute_values);
    }    
    
    protected function version_1_0_5(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_pac_attributes');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'erp_searchable') == false) {
                    $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'erp_searchable',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 2,
                            'nullable' => true,
                            'default' => 'Y',
                            'comment' => ' Erp Searchable, Y:Yes, N: No'
                        ]
                    );
            }
        }
    }
    
    protected function version_1_0_6(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->dropTable('ecc_newcustomer_contact');
        $table_ecc_customer_contact = $installer->getConnection()->newTable($installer->getTable('ecc_newcustomer_contact'));
        
        $table_ecc_customer_contact->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
            'unsigned' => false,
            'auto_increment' => true
        ), 'Entity ID');
        
        $table_ecc_customer_contact->addColumn(
            'contact_email',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             90,
            array('nullable' => false),
            'Email of Contact');
        
          $table_ecc_customer_contact->addColumn(
            'is_toggle_allowed',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             1,
            array('nullable' => false),
            ' Is Toggle Allowed');
          
            $table_ecc_customer_contact->addColumn(
            'login_mode_type',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             20,
            array('nullable' => false),
            'Login Mode Type');
        
        $installer->getConnection()->createTable($table_ecc_customer_contact);
       
    }    
    
    protected function version_1_0_7(SchemaSetupInterface $installer) 
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'show_customer_price') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'show_customer_price', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Show Customer Price, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'show_margin') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'show_margin', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Show Margin, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }
        }
    }
    
    protected function version_1_0_9(SchemaSetupInterface $installer)
    {             
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'warranty_config') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'warranty_config', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Set Warranty Allowed, 2: Global Default, 0: No, 1: Yes'
                                ]
                );
            }
        }
        
        $installer->getConnection()->dropTable($installer->getTable('ecc_erp_mapping_warranty_config'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_erp_mapping_warranty_config'));        
        $table->addColumn('id', Table::TYPE_INTEGER, 10, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'ID');
        $table->addColumn('code', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Warranty Code');
        $table->addColumn('status', Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default' => 'yes'
        ), 'Warranty status');
        $table->addColumn('description', Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Warranty Description');
        $table->addColumn('store_id', Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
            'default' => 0
        ), 'Store id value');
        $installer->getConnection()->createTable($table); 
    }
    
    protected function version_1_1_4(SchemaSetupInterface $installer) 
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'bom_allow_orig_replace') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'bom_allow_orig_replace', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Allow, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }

            if ($installer->getConnection()->tableColumnExists($tableName, 'bom_allow_orig_custom_replace') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'bom_allow_orig_custom_replace', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Allow, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }
            
            if ($installer->getConnection()->tableColumnExists($tableName, 'bom_allow_mod_replace') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'bom_allow_mod_replace', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Allow, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }
            
            if ($installer->getConnection()->tableColumnExists($tableName, 'bom_allow_mod_custom_replace') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'bom_allow_mod_custom_replace', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Allow, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }
            
            if ($installer->getConnection()->tableColumnExists($tableName, 'bom_allow_add') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'bom_allow_add', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Allow, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }
            
            if ($installer->getConnection()->tableColumnExists($tableName, 'bom_allow_custom_add') == false) {
                $installer->getConnection()
                        ->addColumn(
                            $tableName, 'bom_allow_custom_add', [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 2,
                            'comment' => ' Allow, 2: Default, 0: Disabled, 1: Enabled'
                                ]
                );
            }
        }
    }

    protected function version_1_1_6(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'inventory_search_type') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'inventory_search_type',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 1,
                            'nullable' => true,
                            'default' => 3,
                            'comment' => ' Inventory Search Type, 3: Global Default, 0: Own Dealership Only, 1: All Dealership, 2: Dealer Groups'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'claim_inventory_search_type') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'claim_inventory_search_type',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 20,
                            'nullable' => true,
                            'default' => 3,
                            'comment' => ' Claim Inventory Search Type, 3: Global Default, 0: Own Dealership Only, 1: All Dealership, 2: Dealer Groups'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'inventory_dealer_groups') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'inventory_dealer_groups',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => false,
                            'comment' => ' Inventory Search Dealer Groups'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'claim_inventory_dealer_groups') == false) {
                $installer->getConnection()
                    ->addColumn(
                        $tableName,
                        'claim_inventory_dealer_groups',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => false,
                            'comment' => ' Claim Inventory Search Dealer Groups'
                        ]
                    );
            }
        }
    }

    protected function version_1_1_7(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_erp_account');
        if ($installer->tableExists($tableName)) {

            if ($installer->getConnection()->tableColumnExists($tableName, 'inventory_dealer_groups') == true) {
                $installer->getConnection()
                    ->modifyColumn(
                        $tableName,
                        'inventory_dealer_groups',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => ' Inventory Search Dealer Groups'
                        ]
                    );
            }
            if ($installer->getConnection()->tableColumnExists($tableName, 'claim_inventory_dealer_groups') == true) {
                $installer->getConnection()
                    ->modifyColumn(
                        $tableName,
                        'claim_inventory_dealer_groups',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => 255,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => ' Claim Inventory Search Dealer Groups'
                        ]
                    );
            }
        }
    }

    protected function version_1_2_1(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('ecc_dealer_claims_status');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()->newTable(
                $tableName
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'erp_account_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'ERP Account Number'
            )->addColumn(
                'status_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Status Code'
            )->addColumn(
                'count',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Count'
            )->addColumn(
                'extra_info',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Extra Info'
            )->setComment(
                'Dealer Claims Section'
            );
            $installer->getConnection()->createTable($table);
        }
    }
    protected function version_1_2_2(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'ecc_dealer_reminder'
         */

        $tableName = $installer->getTable('ecc_dealer_reminder');
        if ($installer->getConnection()->isTableExists($tableName) != true) {

            $table = $installer->getConnection()->newTable(
                $installer->getTable($tableName)
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
                'claims_due_today_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'claims Due Today'
            )->addColumn(
                'claims_due_this_week_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'claims Due This Week'
            )->addColumn(
                'upcoming_claims_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Upcoming claims'
            )->addColumn(
                'claims_upcoming_options',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'claims Upcoming Options'
            )->addColumn(
                'all_overdue_claims_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'All Overdue claims'
            )->addColumn(
                'all_overdue_claims_options',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'claims Overdue Options'
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
                'claims_due_today_sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'claims Due today sent at'
            )->addColumn(
                'claims_due_this_week_sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'claims Due this weeke sent At'
            )->addColumn(
                'upcoming_claims_sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'Upcoming claims Sent At'
            )->addColumn(
                'all_overdue_claims_sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'All Overdue claims Sent At'
            )->addColumn(
                'reminder_email_sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'Reminder Email Sent At'
            )->addColumn(
                'claims_last_cron_update',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => false, 'default' => '0000-00-00'],
                'claims Last Cron Update'
            )->setComment(
                'claims Dealer Reminder'
            );
            $installer->getConnection()->createTable($table);
        }

    }

    protected function version_1_2_3(SchemaSetupInterface $installer)
    {
        $collection = $this->customerFactory->create()->getCollection();

        $tableName = $installer->getTable('ecc_dealer_reminder');
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

}
