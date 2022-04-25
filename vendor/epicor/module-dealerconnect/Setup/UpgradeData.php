<?php


namespace Epicor\Dealerconnect\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
      
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        
         /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
         $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

         /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
         
         if (version_compare($context->getVersion(), '1.0.3', '<')) {
             $this->version1_0_3($customerSetup);
         }
         if (version_compare($context->getVersion(), '1.0.7', '<')) {
             $this->version1_0_7($eavSetup);
         }
         if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->version1_0_8($customerSetup);
         }
         if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->version1_0_9($customerSetup);
         } 
         if (version_compare($context->getVersion(), '1.0.10', '<')) {
            $this->version1_0_10($customerSetup);
         }
         if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->version1_1_0($setup);
         }
         if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->version1_1_1($setup);
         }
         if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->version1_1_2($setup);
         }
         if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $this->version1_1_3($setup);
         }
         if (version_compare($context->getVersion(), '1.1.4', '<')) {
            $this->version1_1_4($customerSetup);
         }
         if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $this->version1_1_5($setup);
         }
        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            $this->version1_1_6($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.1.9', '<')) {
            $this->version1_1_9($setup);
        }
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->version1_2_0($customerSetup);
        }
        $setup->endSetup();
    }
    
     /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_0_3($customerSetup)
    {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_login_mode_type', [
            'group' => 'General',
            'label' => 'Login Mode Type',
            'type' => 'varchar',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 7,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\LoginModeTypeOptions',
            'default' => '2',
            'system' => false
        ]);
        
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_is_toggle_allowed', [
            'group' => 'General',
            'label' => 'Is Toggle Allowed',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 8,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\IsToggleAllowedOptions',
            'default' => '2',
            'system' => false
        ]);
      
         $attributes = [
            'ecc_login_mode_type',
            'ecc_is_toggle_allowed'
        ];
        
        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
        
    }
    
    /**
     * Creates Product attribute
     * 
     * @param EavSetup $installer
     */
    protected function version1_0_7(EavSetup $eavSetup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_return_type');
        $eavSetup->addAttribute(
            $entityTypeId,
            'ecc_return_type',
            [
                'group' => 'General',
                'label' => 'Return Type',
                'type' => 'int',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'sort_order' => 125,
                'option' =>
                    [
                        'values' =>
                            [
                                'C' => 'Credit',
                                'S' => 'Replace',
                            ]
                    ]
            ]
        );
    }
    
    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_0_8($customerSetup) {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_show_customer_price', [
            'group' => 'General',
            'label' => 'Show Customer Price',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 9,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showCusPriceOptions',
            'default' => '2',
            'system' => false
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_show_margin', [
            'group' => 'General',
            'label' => 'Show Margin',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 10,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);

        $attributes = [
            'ecc_show_customer_price',
            'ecc_show_margin'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }
    
    
    private function version1_0_9($eavSetup)
    {
        $entityTypeId = \Magento\Customer\Model\Customer::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_warranty_config');
        $eavSetup->addAttribute($entityTypeId, 'ecc_warranty_config', [
            'group' => 'General',
            'label' => 'Set Warranty Allowed ?',
            'type' => 'int',
            'input' => 'select',
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnonulloption',
            'required' => false,
            'user_defined' => false,
            'visible' => true,
            'default' => 2,
            'is_system' => 0,
            'sort_order' => 12,
            'system' => false
        ]);
        $attributes = [
            'ecc_warranty_config',
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $eavSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }     
    }  
    
    private function version1_0_10($eavSetup)
    {
        $entityTypeId = \Magento\Customer\Model\Customer::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_warranty_config');
        $eavSetup->addAttribute($entityTypeId, 'ecc_warranty_config', [
            'group' => 'General',
            'label' => 'Set Warranty Allowed ?',
            'type' => 'int',
            'input' => 'select',
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnonulloption',
            'required' => false,
            'user_defined' => false,
            'visible' => true,
            'default' => 2,
            'is_system' => 0,
            'sort_order' => 12,
            'position' => 22,
            'system' => false
        ]);
        $attributes = [
            'ecc_warranty_config',
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $eavSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }     
    } 

    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_1_0($setup)
    {
        $this->setupGridConfigValues($setup);
    }
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * 
     * Added to compensate for known magento 2 issue which prevents serialized arrays being read from config.xml files (WSO-6282)
     * https://github.com/magento/magento2/issues/9038
     */
    private function setupGridConfigValues($setup) 
    {
        $erp = false;
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'Epicor_Comm/licensing/erp\'');

        $erpInfo = $var->fetch();
        
        if (is_array($erpInfo) && $erpInfo['value']) {
            $erp = $erpInfo['value'];
        }

        $values = [
            'dealerconnect_enabled_messages/DEIS_request/grid_config' => 'a:5:{s:18:"_1524386972672_672";a:12:{s:6:"header";s:21:"Identification Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"identification_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1524386980528_528";a:12:{s:6:"header";s:12:"Order Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1524469278128_128";a:12:{s:6:"header";s:12:"Product Code";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:17:"_1524469287074_74";a:12:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"description";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536738863709_709";a:12:{s:6:"header";s:17:"Bill Of Materials";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:17:"bill_of_materials";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"0";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}}'
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
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_1_1($setup)
    {
        $this->updateGridConfigValues($setup);
    }
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * 
     * Added to compensate for known magento 2 issue which prevents serialized arrays being read from config.xml files (WSO-6282)
     * https://github.com/magento/magento2/issues/9038
     */
    private function updateGridConfigValues($setup) 
    {
        $erp = false;
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'dealerconnect_enabled_messages/DEBM_request/grid_config_additional\'');

        $erpInfo = $var->fetch();
        if ($erpInfo == false) {
            return;
        }
        
        $value = 'a:6:{s:18:"_1537177911824_824";a:12:{s:6:"header";s:6:"Expand";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:6:"expand";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"0";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:17:"_1536726977017_17";a:12:{s:6:"header";s:7:"Product";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"new_product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536726987842_842";a:12:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"transdescription";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536727007997_997";a:12:{s:6:"header";s:13:"Part Replaced";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"original_product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536727019225_225";a:12:{s:6:"header";s:13:"Serial Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:17:"new_serial_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1537177921830_830";a:12:{s:6:"header";s:7:"Reorder";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:7:"reorder";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"0";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}}';
        
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'dealerconnect_enabled_messages/DEBM_request/grid_config_additional',
            'value' => $value,
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'),
            $data,
            ['value']
        );
    }
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_1_2($setup)
    {
        $this->updateDebmGridConfigValues($setup);
    }
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * 
     * Added to compensate for known magento 2 issue which prevents serialized arrays being read from config.xml files (WSO-6282)
     * https://github.com/magento/magento2/issues/9038
     */
    private function updateDebmGridConfigValues($setup) 
    {
        $erp = false;
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'dealerconnect_enabled_messages/DEBM_request/grid_config_additional\'');

        $erpInfo = $var->fetch();
        if ($erpInfo == false) {
            return;
        }
        
        $value = 'a:6:{s:18:"_1537177911824_824";a:12:{s:6:"header";s:6:"Expand";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:6:"expand";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"0";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:17:"_1536726977017_17";a:12:{s:6:"header";s:7:"Product";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"new_product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536726987842_842";a:12:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"description";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536727007997_997";a:12:{s:6:"header";s:13:"Part Replaced";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"original_product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536727019225_225";a:12:{s:6:"header";s:13:"Serial Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:17:"new_serial_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1537177921830_830";a:12:{s:6:"header";s:7:"Reorder";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:7:"reorder";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"0";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}}';
        
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'dealerconnect_enabled_messages/DEBM_request/grid_config_additional',
            'value' => $value,
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'),
            $data,
            ['value']
        );
    }
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_1_3($setup) 
    {
        $this->removeReorderFromDebm($setup);
    }

    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * 
     * Added to compensate for known magento 2 issue which prevents serialized arrays being read from config.xml files (WSO-6282)
     * https://github.com/magento/magento2/issues/9038
     */
    private function removeReorderFromDebm($setup) 
    {
        $columnConfig = unserialize($this->scopeConfig->getValue('dealerconnect_enabled_messages/DEBM_request/grid_config_additional'));
        if($columnConfig){
            foreach ($columnConfig as $key => $column) {
                if ($column['index'] == 'reorder') {
                    unset($columnConfig[$key]);
                }
            }  
            $value = serialize($columnConfig);
            if (empty($value)) {
                $value = 'a:5:{s:18:"_1537177911824_824";a:12:{s:6:"header";s:6:"Expand";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:6:"expand";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"0";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:17:"_1536726977017_17";a:12:{s:6:"header";s:7:"Product";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"new_product_code";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536726987842_842";a:12:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"description";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536727007997_997";a:12:{s:6:"header";s:13:"Part Replaced";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"original_product_code";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}s:18:"_1536727019225_225";a:12:{s:6:"header";s:13:"Serial Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:17:"new_serial_number";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";s:13:"pacattributes";s:0:"";s:12:"datatypejson";s:0:"";}}';
            }

            $erp = false;
            $writeConnection = $setup->getConnection('core_write');
            /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
            $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'dealerconnect_enabled_messages/DEBM_request/grid_config_additional\'');

            $erpInfo = $var->fetch();
            if ($erpInfo == false) {
                return;
            }
            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'dealerconnect_enabled_messages/DEBM_request/grid_config_additional',
                'value' => $value,
            ];

            $writeConnection->insertOnDuplicate(
                $setup->getTable('core_config_data'), $data, ['value']
            );
        }
    }
    
    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_1_4($customerSetup) {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_bom_allow_orig_replace', [
            'group' => 'General',
            'label' => 'BOM Allow replacement of originial parts',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 13,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_bom_allow_orig_cus_replace', [
            'group' => 'General',
            'label' => 'BOM Allow replacement with custom parts of originial parts',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 14,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);
        
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_bom_allow_mod_replace', [
            'group' => 'General',
            'label' => 'BOM Allow replacement of modified parts',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 15,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);
        
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_bom_allow_mod_cus_replace', [
            'group' => 'General',
            'label' => 'BOM Allow replacement with custom parts of modified parts',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 16,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);
        
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_bom_allow_add', [
            'group' => 'General',
            'label' => 'BOM Allow addition of parts',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 17,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);
        
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_bom_allow_cus_add', [
            'group' => 'General',
            'label' => 'BOM Allow addition of custom parts',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 18,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\showMarginOptions',
            'default' => '2',
            'system' => false
        ]);

        $attributes = [
            'ecc_bom_allow_orig_replace',
            'ecc_bom_allow_orig_cus_replace',
            'ecc_bom_allow_mod_replace',
            'ecc_bom_allow_mod_cus_replace',
            'ecc_bom_allow_add',
            'ecc_bom_allow_cus_add'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_1_5($setup) 
    {
        $this->addDealerWarrantyInfoToDebm($setup);
    }
    
    /**
     * 
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * 
     * https://github.com/magento/magento2/issues/9038
     */
    private function addDealerWarrantyInfoToDebm($setup) 
    {
        $columnConfig = unserialize($this->scopeConfig->getValue('dealerconnect_enabled_messages/DEBM_request/grid_informationconfig'));
        $dWarrantyCode = array(
            "header" => "Dealer Warranty Code",
            "index" => "dealer_warranty_code",
            "hiddenpac" => ""
        );
        $dWarrantyComment = array(
            "header" => "Dealer Warranty Comment",
            "index" => "dealer_warranty_comment",
            "hiddenpac" => ""
        );
        $dWarrantyStart = array(
            "header" => "Dealer Warranty Start",
            "index" => "dealer_warranty_start_date",
            "hiddenpac" => ""
        );
        $dWarrantyExp = array(
            "header" => "Dealer Warranty Expiration",
            "index" => "dealer_warranty_expiration_date",
            "hiddenpac" => ""
        );
        array_push($columnConfig, $dWarrantyCode, $dWarrantyComment, $dWarrantyStart, $dWarrantyExp);
        $value = serialize($columnConfig);
        if (empty($value)) {
            $value = 'a:18:{s:18:"_1543472742641_641";a:3:{s:6:"header";s:11:"Material ID";s:5:"index";s:11:"material_id";s:9:"hiddenpac";s:0:"";}s:18:"_1536735653907_907";a:3:{s:6:"header";s:12:"Product Code";s:5:"index";s:12:"product_code";s:9:"hiddenpac";s:0:"";}s:18:"_1536735683975_975";a:3:{s:6:"header";s:11:"Description";s:5:"index";s:11:"description";s:9:"hiddenpac";s:0:"";}s:18:"_1536735694147_147";a:3:{s:6:"header";s:10:"Job Number";s:5:"index";s:7:"job_num";s:9:"hiddenpac";s:0:"";}s:18:"_1536735703649_649";a:3:{s:6:"header";s:17:"Assembly Sequence";s:5:"index";s:12:"assembly_seq";s:9:"hiddenpac";s:0:"";}s:18:"_1536735713985_985";a:3:{s:6:"header";s:3:"UOM";s:5:"index";s:20:"unit_of_measure_code";s:9:"hiddenpac";s:0:"";}s:18:"_1536735721849_849";a:3:{s:6:"header";s:14:"Serial Numbers";s:5:"index";s:28:"serial_numbers_serial_number";s:9:"hiddenpac";s:0:"";}s:18:"_1536735736104_104";a:3:{s:6:"header";s:11:"Lot Numbers";s:5:"index";s:22:"lot_numbers_lot_number";s:9:"hiddenpac";s:0:"";}s:17:"_1536735778077_77";a:3:{s:6:"header";s:8:"Quantity";s:5:"index";s:8:"quantity";s:9:"hiddenpac";s:0:"";}s:16:"_1536735788009_9";a:3:{s:6:"header";s:15:"Revision Number";s:5:"index";s:12:"revision_num";s:9:"hiddenpac";s:0:"";}s:18:"_1536735798232_232";a:3:{s:6:"header";s:13:"Warranty Code";s:5:"index";s:13:"warranty_code";s:9:"hiddenpac";s:0:"";}s:18:"_1536735810262_262";a:3:{s:6:"header";s:16:"Warranty Comment";s:5:"index";s:16:"warranty_comment";s:9:"hiddenpac";s:0:"";}s:18:"_1536735822267_267";a:3:{s:6:"header";s:14:"Warranty Start";s:5:"index";s:14:"warranty_start";s:9:"hiddenpac";s:0:"";}s:18:"_1536735831307_307";a:3:{s:6:"header";s:19:"Warranty Expiration";s:5:"index";s:19:"warranty_expiration";s:9:"hiddenpac";s:0:"";}i:0;a:3:{s:6:"header";s:20:"Dealer Warranty Code";s:5:"index";s:20:"dealer_warranty_code";s:9:"hiddenpac";s:0:"";}i:1;a:3:{s:6:"header";s:23:"Dealer Warranty Comment";s:5:"index";s:23:"dealer_warranty_comment";s:9:"hiddenpac";s:0:"";}i:2;a:3:{s:6:"header";s:21:"Dealer Warranty Start";s:5:"index";s:21:"dealer_warranty_start";s:9:"hiddenpac";s:0:"";}i:3;a:3:{s:6:"header";s:26:"Dealer Warranty Expiration";s:5:"index";s:26:"dealer_warranty_expiration";s:9:"hiddenpac";s:0:"";}}';
        }

        $erp = false;
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'dealerconnect_enabled_messages/DEBM_request/grid_informationconfig\'');

        $erpInfo = $var->fetch();
        if ($erpInfo == false) {
            return;
        }
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'dealerconnect_enabled_messages/DEBM_request/grid_informationconfig',
            'value' => $value,
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_1_6($customerSetup)
    {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_inventory_search', [
            'group' => 'General',
            'label' => 'Inventory Search',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 19,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\inventoryOptions',
            'default' => '3',
            'system' => false
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_claim_inventory_search', [
            'group' => 'General',
            'label' => 'Claim Inventory Search',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 20,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\inventoryOptions',
            'default' => '3',
            'system' => false
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_dealer_group', [
            'group' => 'General',
            'label' => 'Dealer Group',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 21,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\DealerGroup',
            'system' => false
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_claim_dealer_group', [
            'group' => 'General',
            'label' => 'Claim Dealer Group',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 22,
            'source' => 'Epicor\Dealerconnect\Model\Eav\Attribute\Data\DealerGroup',
            'system' => false
        ]);

    }

    /**
     * Remove DCLS grid info from all scope,
     * wrong grid array key sync added by config.xml.
     *
     * @param $setup
     */
    private function version1_1_9($setup)
    {
        $connection = $setup->getConnection();


        $connection->delete(
            $setup->getTable('core_config_data'),
            ['path = ?' => 'dealerconnect_enabled_messages/DCLS_request/grid_config']
        );
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_2_0($customerSetup)
    {
        $customerSetup->updateAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_dealer_group', [
            'default' => 0
        ]);

        $customerSetup->updateAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_claim_dealer_group', [
            'default' => 0
        ]);

        $attributes = [
            'ecc_dealer_group',
            'ecc_claim_dealer_group',
            'ecc_claim_inventory_search',
            'ecc_inventory_search'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }
}
