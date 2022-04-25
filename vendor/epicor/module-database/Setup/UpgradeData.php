<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Database\Setup;


use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Customer\Model\Customer;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config;

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
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    protected $eavConfig;

    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var Config
     */
    private $sequenceConfig;

    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $state,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Builder $sequenceBuilder,
        Config $sequenceConfig
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->eavConfig = $eavConfig;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
        try{
            $state->setAreaCode('frontend');
        }catch (\Magento\Framework\Exception\LocalizedException $e)
        { /* DO NOTHING, THE SARE CODE IS ALREADY SET */
        }
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->version1_0_1($customerSetup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->version1_0_2($customerSetup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->version1_0_4($eavSetup, $setup);
        }
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->version1_0_8($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->version1_1_0($customerSetup);
        }

        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $this->version1_1_5($setup);
        }

        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            $this->version1_1_7($setup);
        }
        if (version_compare($context->getVersion(), '1.1.9', '<')) {
            $this->version1_1_9($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->version1_2_0($setup);
        }
        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $this->version1_2_1($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $this->version1_2_2($setup);
        }
        if (version_compare($context->getVersion(), '1.2.4', '<')) {
            $this->version1_2_4($eavSetup);
        }

        if (version_compare($context->getVersion(), '1.2.8', '<')) {
            $this->version1_2_8($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->version1_3_0($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->version1_3_1($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.3.6', '<')) {
            $this->version1_3_6($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.3.7', '<')) {
            $this->version1_3_7($eavSetup, $setup);
        }
        if (version_compare($context->getVersion(), '1.3.8.7', '<')) {
            $this->version1_3_8_7($setup);
        }
        if (version_compare($context->getVersion(), '1.3.9', '<')) {
            $this->version1_3_9($setup);
        }
        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->version1_4_0($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.4.1', '<')) {
            $this->version1_4_1($customerSetup);
        }

        if (version_compare($context->getVersion(), '1.4.3', '<')) {
            $this->version1_4_3($eavSetup, $setup);
        }
        if (version_compare($context->getVersion(), '1.4.7', '<')) {
            $this->version1_4_7($eavSetup, $setup);
        }
        if (version_compare($context->getVersion(), '1.4.8', '<')) {
            $this->version1_4_8($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->version1_5_0($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.5.2', '<')) {
            $this->version1_5_2($eavSetup, $customerSetup);
        }
        if (version_compare($context->getVersion(), '1.5.4', '<')) {
            $this->version1_5_4($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.5.7', '<')) {
            $this->version1_5_7($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.5.8', '<')) {
            $this->version1_5_8($eavSetup);
        }
		if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->version1_6_0($setup);
        }
        if (version_compare($context->getVersion(), '1.6.2', '<')) {
            $this->version1_6_2($setup);
        }
        if (version_compare($context->getVersion(), '1.6.3', '<')) {
            $this->version1_6_3($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.6.4', '<')) {
            $this->version1_6_4($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.6.8', '<')) {
            $this->version1_6_8($setup);
        }
        if (version_compare($context->getVersion(), '1.6.9', '<')) {
            $this->version1_6_9($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $this->version1_7_0($setup);
        }
        if (version_compare($context->getVersion(), '1.7.1', '<')) {
            $this->version1_7_1();
        }
        if (version_compare($context->getVersion(), '1.7.2', '<')) {
            $this->version1_7_2($eavSetup);
        }
        if (version_compare($context->getVersion(), '1.7.3', '<')) {
            $this->version1_7_3($setup);
        }
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_0_1($customerSetup)
    {
        $attributes = [
            'ecc_salesrep_catalog_access',
            'ecc_erpaccount_id',
            'ecc_previous_erpaccount',
            'ecc_custom_address_allowed',
            'ecc_sales_rep_id',
            'ecc_sales_rep_account_id',
            'ecc_default_location_code',
            'ecc_contact_code',
            'ecc_function',
            'ecc_telephone_number',
            'ecc_fax_number',
            'ecc_supplier_erpaccount_id',
            'ecc_allow_masquerade',
            'ecc_allow_masq_cart_clear',
            'ecc_allow_masq_cart_reprice',
            'ecc_master_shopper',
            'ecc_erp_account_type'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }


        $entityAttributes = [
            'customer' => [
                'ecc_erp_account_type' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                ],
                'ecc_master_shopper' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                ],
                'ecc_previous_erpaccount' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                ],
                'ecc_erpaccount_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                ],
                'ecc_sales_rep_account_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                ],
                'ecc_supplier_erpaccount_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);

    }

    /**
     * @param array $entityAttributes
     * @param CustomerSetup $customerSetup
     * @return void
     */
    protected function upgradeAttributes(array $entityAttributes, $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }
    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_0_2($customerSetup)
    {
        $attributes = [
            'ecc_mobile_number',
            'ecc_instructions',
            'ecc_email'
        ];

        $usedInForms = array(
            'adminhtml_customer_address',
            'customer_address_edit',
            'customer_register_address'
        );

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer_address', $attributeCode);
            $attr->setData('used_in_forms', $usedInForms);
            $attr->save();
        }


        $attributes = [
            'ecc_erp_group_code',
            'ecc_erp_address_code',
            'ecc_is_registered',
            'ecc_is_delivery',
            'ecc_is_invoice'
        ];

        $usedInForms = array(
            'adminhtml_customer_address',
        );

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer_address', $attributeCode);
            $attr->setData('used_in_forms', $usedInForms);
            $attr->save();
        }

    }

    /**
     *
     * @param \Magento\Eav\Setup\EavSetupFactory  $eavSetup
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_0_4($eavSetup, $setup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_erp_images',
            'backend_model',
            'Epicor\Comm\Model\Eav\Attribute\Data\Erpimages'
        );

        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_previous_erp_images',
            'backend_model',
            'Epicor\Comm\Model\Eav\Attribute\Data\Erpimages'
        );

        /* update attribute of Catalog Category Entity Type ID = 3 */
        $entityTypeId = \Magento\Catalog\Model\Category::ENTITY;
        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_erp_images',
            'backend_model',
            'Epicor\Comm\Model\Eav\Attribute\Data\Erpimages'
        );
        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_previous_erp_images',
            'backend_model',
            'Epicor\Comm\Model\Eav\Attribute\Data\Erpimages'
        );

        $this->setupGridConfigValues($setup);
    }

    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     *
     * Added to compensate for known magento 2 issue which prevents serialized arrays being read from config.xml files (WSO-4267)
     * https://github.com/magento/magento2/issues/9038
     */
    private function setupGridConfigValues($setup) {

        $erp = false;
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'Epicor_Comm/licensing/erp\'');

        $erpInfo = $var->fetch();

        if (is_array($erpInfo) && $erpInfo['value']) {
            $erp = $erpInfo['value'];
        }

        $values = [
            'epicor_comm_enabled_messages/CRRS_request/grid_config' => 'a:7:{s:18:"_1421752377517_517";a:10:{s:6:"header";s:13:"Return Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"erp_returns_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752378350_350";a:10:{s:6:"header";s:12:"Created Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"rma_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752380109_109";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"returns_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Rmastatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752640999_999";a:10:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752644174_174";a:10:{s:6:"header";s:13:"Customer Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"customer_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752644536_536";a:10:{s:6:"header";s:9:"Invoice #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:54:"Epicor_Customerconnect_Block_List_Renderer_Linkinvoice";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752645110_110";a:10:{s:6:"header";s:6:"Case #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:15:"rma_case_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CUOS_request/grid_config' => 'a:8:{s:18:"_1380728508313_313";a:10:{s:6:"header";s:12:"Order Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728509643_643";a:10:{s:6:"header";s:12:"Purchased On";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728510155_155";a:10:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728510674_674";a:10:{s:6:"header";s:10:"Order Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"order_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728511234_234";a:10:{s:6:"header";s:13:"Order Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"order_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728511866_866";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728513297_297";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:22:"dealer_grand_total_inc";s:9:"filter_by";s:3:"erp";s:9:"condition";s:3:"LTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1523346346484_484";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erporderstatus";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erporderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CPHS_request/grid_config' => 'a:8:{s:18:"_1487172399198_198";a:10:{s:6:"header";s:12:"Product Code";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487172671846_846";a:10:{s:6:"header";s:15:"Unit Of Measure";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:20:"unit_of_measure_code";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487172685766_766";a:10:{s:6:"header";s:17:"Total Qty Ordered";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:17:"total_qty_ordered";s:9:"filter_by";s:4:"linq";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487172753934_934";a:10:{s:6:"header";s:17:"Last Ordered Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:17:"last_ordered_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487172834873_873";a:10:{s:6:"header";s:17:"Last Order Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:17:"last_order_number";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487172837686_686";a:10:{s:6:"header";s:20:"Last Tracking Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:20:"last_tracking_number";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487172917278_278";a:10:{s:6:"header";s:17:"Last Order Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erporderstatus";s:5:"index";s:19:"last_ordered_status";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erporderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1487177596376_376";a:10:{s:6:"header";s:17:"Last Packing Slip";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:17:"last_packing_slip";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CUSS_request/grid_config' => 'a:5:{s:18:"_1380790739687_687";a:9:{s:6:"header";s:13:"Shipment Date";s:4:"type";s:4:"date";s:5:"index";s:13:"shipment_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790741384_384";a:9:{s:6:"header";s:7:"Order #";s:4:"type";s:4:"text";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790741878_878";a:9:{s:6:"header";s:12:"Packing Slip";s:4:"type";s:4:"text";s:5:"index";s:12:"packing_slip";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:55:"Epicor_Customerconnect_Block_List_Renderer_Allshipments";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790742398_398";a:9:{s:6:"header";s:18:"Customer Reference";s:4:"type";s:4:"text";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790742952_952";a:9:{s:6:"header";s:15:"Delivery Method";s:4:"type";s:4:"text";s:5:"index";s:15:"delivery_method";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CUIS_request/grid_config' => 'a:9:{s:18:"_1380731654160_160";a:10:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731674478_478";a:10:{s:6:"header";s:12:"Invoice Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:12:"invoice_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:16:"_1380731675005_5";a:10:{s:6:"header";s:8:"Due Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"due_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731675565_565";a:10:{s:6:"header";s:11:"Our Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"our_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380731676086_86";a:10:{s:6:"header";s:12:"Your Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731677645_645";a:10:{s:6:"header";s:6:"Amount";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731678373_373";a:10:{s:6:"header";s:11:"Balance Due";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:17:"outstanding_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731678957_957";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:41:"customerconnect/erp_mapping_invoicestatus";s:5:"index";s:14:"invoice_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:56:"Epicor_Customerconnect_Block_List_Renderer_Invoicestatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1473771488748_748";a:10:{s:6:"header";s:9:"Contracts";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"contracts_contract_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:55:"Epicor_Customerconnect_Block_List_Renderer_ContractCode";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CUPS_request/grid_config' => 'a:5:{s:18:"_1380789337996_996";a:9:{s:6:"header";s:12:"Payment Date";s:4:"type";s:4:"date";s:5:"index";s:12:"payment_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380789354155_155";a:9:{s:6:"header";s:9:"Reference";s:4:"type";s:4:"text";s:5:"index";s:17:"payment_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380789354689_689";a:9:{s:6:"header";s:6:"Amount";s:4:"type";s:6:"number";s:5:"index";s:14:"payment_amount";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380789355242_242";a:9:{s:6:"header";s:12:"Order Number";s:4:"type";s:4:"text";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380789355810_810";a:9:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:54:"Epicor_Customerconnect_Block_List_Renderer_Linkinvoice";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CURS_request/grid_config' => 'a:10:{s:18:"_1380790101624_624";a:10:{s:6:"header";s:9:"Returns #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"returns_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790114387_387";a:10:{s:6:"header";s:4:"Line";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:4:"line";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790114941_941";a:10:{s:6:"header";s:4:"Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"rma_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790115445_445";a:10:{s:6:"header";s:12:"Product Code";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790115925_925";a:10:{s:6:"header";s:14:"Revision Level";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"revision_level";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790116757_757";a:10:{s:6:"header";s:11:"Qty Ordered";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:18:"quantities_ordered";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790117269_269";a:10:{s:6:"header";s:12:"Qty Returned";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:19:"quantities_returned";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790117813_813";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:37:"customerconnect/erp_mapping_rmastatus";s:5:"index";s:14:"returns_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Rmastatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790118365_365";a:10:{s:6:"header";s:7:"Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790118909_909";a:10:{s:6:"header";s:10:"Order Line";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:10:"order_line";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CUCS_request/grid_config' => 'a:9:{s:18:"_1380791119552_552";a:10:{s:6:"header";s:6:"Call #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"call_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791132266_266";a:10:{s:6:"header";s:9:"Call Type";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:9:"call_type";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791132870_870";a:10:{s:6:"header";s:14:"Requested Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:14:"requested_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791133366_366";a:10:{s:6:"header";s:14:"Scheduled Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:14:"scheduled_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791133950_950";a:10:{s:6:"header";s:11:"Actual Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:11:"actual_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791134630_630";a:10:{s:6:"header";s:13:"Call Duration";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:13:"call_duration";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380791135062_62";a:10:{s:6:"header";s:14:"Service Status";s:4:"type";s:7:"options";s:7:"options";s:45:"customerconnect/erp_mapping_servicecallstatus";s:5:"index";s:14:"service_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:60:"Epicor_Customerconnect_Block_List_Renderer_Servicecallstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791135606_606";a:10:{s:6:"header";s:8:"Invoiced";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:8:"invoiced";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380791136342_342";a:10:{s:6:"header";s:9:"Call Void";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:9:"call_void";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CRQS_request/grid_config' => 'a:9:{s:18:"_1404741622571_571";a:10:{s:6:"header";s:12:"Quote Number";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:12:"quote_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1404741624704_704";a:10:{s:6:"header";s:4:"Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"quote_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:5:"LT/GT";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1404741625304_304";a:10:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"description";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1404741625856_856";a:10:{s:6:"header";s:18:"Customer Reference";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1404741626232_232";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:5:"LT/GT";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1404741626808_808";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:22:"dealer_grand_total_inc";s:9:"filter_by";s:3:"erp";s:9:"condition";s:5:"LT/GT";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1455713631030_30";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erpquotestatus";s:5:"index";s:12:"quote_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erpquotestatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:15:"_1481124692_657";a:10:{s:6:"header";s:10:"Quote name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:27:"quote_delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1523007819130_130";a:10:{s:6:"header";s:9:"Contracts";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"contracts_contract_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:55:"Epicor_Customerconnect_Block_List_Renderer_ContractCode";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CCCS_request/grid_config' => 'a:3:{s:16:"_1474537054006_6";a:10:{s:6:"header";s:8:"Erp Code";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"account_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1474537149605_605";a:10:{s:6:"header";s:14:"Contract Title";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"contract_title";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1474537157539_539";a:10:{s:6:"header";s:16:"Contract Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:35:"delivery_addresses_delivery_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:67:"Epicor_Customerconnect_Block_Customer_List_Renderer_DeliveryAddress";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SPLS_request/grid_config' => 'a:8:{s:18:"_1380792275502_502";a:9:{s:6:"header";s:11:"Part Number";s:4:"type";s:4:"text";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792285534_534";a:9:{s:6:"header";s:15:"Cross Reference";s:4:"type";s:4:"text";s:5:"index";s:15:"cross_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792286124_124";a:9:{s:6:"header";s:20:"Cross Reference Type";s:4:"type";s:4:"text";s:5:"index";s:20:"cross_reference_type";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792286613_613";a:9:{s:6:"header";s:14:"Operation Code";s:4:"type";s:4:"text";s:5:"index";s:16:"operational_code";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792287157_157";a:9:{s:6:"header";s:14:"Effective Date";s:4:"type";s:4:"date";s:5:"index";s:14:"effective_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792287716_716";a:9:{s:6:"header";s:15:"Expiration Date";s:4:"type";s:4:"date";s:5:"index";s:15:"expiration_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792288220_220";a:9:{s:6:"header";s:15:"Base Unit Price";s:4:"type";s:4:"text";s:5:"index";s:5:"price";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792288837_837";a:9:{s:6:"header";s:3:"U/M";s:4:"type";s:4:"text";s:5:"index";s:20:"unit_of_measure_code";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SPOS_request/grid_config' => 'a:8:{s:18:"_1380793814806_806";a:10:{s:6:"header";s:9:"PO Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793893373_373";a:10:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793893954_954";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:48:"supplierconnect/config_source_orderstatusoptions";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:73:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Erporderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793894801_801";a:10:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793895433_433";a:10:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793895954_954";a:10:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793896473_473";a:10:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380793897018_18";a:10:{s:6:"header";s:9:"Confirmed";s:4:"type";s:7:"options";s:7:"options";s:50:"supplierconnect/config_source_confirmstatusoptions";s:5:"index";s:15:"order_confirmed";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Confirmed";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SPOS_request/newpogrid_config' => 'a:9:{s:18:"_1380794775648_648";a:9:{s:6:"header";s:7:"Confirm";s:4:"type";s:4:"text";s:5:"index";s:14:"new_po_confirm";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:65:"Epicor_Supplierconnect_Block_Customer_Orders_New_Renderer_Confirm";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798405_405";a:9:{s:6:"header";s:6:"Reject";s:4:"type";s:4:"text";s:5:"index";s:13:"new_po_reject";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_New_Renderer_Reject";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798930_930";a:9:{s:6:"header";s:13:"Our PO Number";s:4:"type";s:4:"text";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_New_Renderer_Linkpo";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794799626_626";a:9:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794800354_354";a:9:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380794801098_98";a:9:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794801650_650";a:9:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802290_290";a:9:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802866_866";a:9:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:5:"index";s:12:"order_status";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:70:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Orderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SPCS_request/grid_config' => 'a:8:{s:18:"_1381758946179_179";a:10:{s:6:"header";s:9:"PO Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758948320_320";a:10:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758948821_821";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:48:"supplierconnect/config_source_orderstatusoptions";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:70:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Orderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758949389_389";a:10:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758949981_981";a:10:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758950582_582";a:10:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758952319_319";a:10:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1381758953013_13";a:10:{s:6:"header";s:9:"Confirmed";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:15:"order_confirmed";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Confirmed";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SURS_request/grid_config' => 'a:10:{s:18:"_1380796025860_860";a:10:{s:6:"header";s:3:"RFQ";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:10:"rfq_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380796027060_60";a:10:{s:6:"header";s:4:"Line";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:4:"line";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380796027883_883";a:10:{s:6:"header";s:8:"Due Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"due_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380796037476_476";a:10:{s:6:"header";s:12:"Respond Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:12:"respond_date";s:9:"filter_by";s:4:"none";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380796038010_10";a:10:{s:6:"header";s:13:"Decision Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:13:"decision_date";s:9:"filter_by";s:4:"none";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380796038498_498";a:10:{s:6:"header";s:11:"Part Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380796039074_74";a:10:{s:6:"header";s:18:"PN Cross Reference";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:15:"cross_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380796039546_546";a:10:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"description";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1391593972052_52";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:46:"supplierconnect/config_source_rfqstatusoptions";s:5:"index";s:6:"status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1391593972666_666";a:10:{s:6:"header";s:8:"Response";s:4:"type";s:7:"options";s:7:"options";s:48:"supplierconnect/config_source_rfqresponseoptions";s:5:"index";s:8:"response";s:9:"filter_by";s:4:"linq";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SUIS_request/grid_config' => 'a:8:{s:18:"_1380797061839_839";a:10:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797063631_631";a:10:{s:6:"header";s:12:"Invoice Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:12:"invoice_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797064133_133";a:10:{s:6:"header";s:8:"Due Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"due_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797064549_549";a:10:{s:6:"header";s:9:"PO Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380797065069_69";a:10:{s:6:"header";s:21:"Supplier Order Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"supplier_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797065525_525";a:10:{s:6:"header";s:6:"Amount";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:11:"grand_total";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797066117_117";a:10:{s:6:"header";s:11:"Balance Due";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:11:"balance_due";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797066605_605";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:41:"customerconnect/erp_mapping_invoicestatus";s:5:"index";s:14:"invoice_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:74:"Epicor_Supplierconnect_Block_Customer_Invoices_List_Renderer_Invoicestatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'supplierconnect_enabled_messages/SUPS_request/grid_config' => 'a:5:{s:18:"_1380797566395_395";a:9:{s:6:"header";s:12:"Payment Date";s:4:"type";s:4:"date";s:5:"index";s:12:"payment_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797567947_947";a:9:{s:6:"header";s:15:"Check Reference";s:4:"type";s:4:"text";s:5:"index";s:17:"payment_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797568508_508";a:9:{s:6:"header";s:12:"Check Amount";s:4:"type";s:6:"number";s:5:"index";s:14:"payment_amount";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380797569058_58";a:9:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:72:"Epicor_Supplierconnect_Block_Customer_Payments_List_Renderer_Linkinvoice";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797569706_706";a:9:{s:6:"header";s:14:"Payment Amount";s:4:"type";s:5:"range";s:5:"index";s:22:"invoice_payment_amount";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
            'customerconnect_enabled_messages/CAPS_request/grid_config' => 'a:7:{s:18:"_1532759575359_359";a:10:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1532759585204_204";a:10:{s:6:"header";s:12:"Invoice Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:12:"invoice_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1532759592700_700";a:10:{s:6:"header";s:8:"Due Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"due_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1532759598879_879";a:10:{s:6:"header";s:14:"Invoice Amount";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:80:"Epicor_Customerconnect_Block_Customer_Arpayments_Invoices_Renderer_InvoiceAmount";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1532759608837_837";a:10:{s:6:"header";s:11:"Paid Amount";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:13:"payment_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:79:"Epicor_Customerconnect_Block_Customer_Arpayments_Invoices_Renderer_PaymentValue";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1532759631866_866";a:10:{s:6:"header";s:15:"Invoice Balance";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:17:"outstanding_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:81:"Epicor_Customerconnect_Block_Customer_Arpayments_Invoices_Renderer_InvoiceBalance";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1532847876309_309";a:10:{s:6:"header";s:7:"Ship To";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"delivery_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:82:"Epicor_Customerconnect_Block_Customer_Arpayments_Invoices_Renderer_DeliveryAddress";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}'
        ];

        if ($erp == 'e10') {
            $values['epicor_comm_enabled_messages/CRRS_request/grid_config'] = 'a:7:{s:18:"_1421752377517_517";a:10:{s:6:"header";s:13:"Return Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"erp_returns_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752378350_350";a:10:{s:6:"header";s:12:"Created Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"rma_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752380109_109";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"returns_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Rmastatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752640999_999";a:10:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752644174_174";a:10:{s:6:"header";s:13:"Customer Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"customer_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752644536_536";a:10:{s:6:"header";s:9:"Invoice #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:54:"Epicor_Customerconnect_Block_List_Renderer_Linkinvoice";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752645110_110";a:10:{s:6:"header";s:6:"Case #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:15:"rma_case_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';
            $values['customerconnect_enabled_messages/CUOS_request/grid_config'] = 'a:8:{s:18:"_1380728508313_313";a:10:{s:6:"header";s:12:"Order Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728509643_643";a:10:{s:6:"header";s:12:"Purchased On";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728510155_155";a:10:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728510674_674";a:10:{s:6:"header";s:10:"Order Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"order_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728511234_234";a:10:{s:6:"header";s:13:"Order Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"order_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728511866_866";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728513297_297";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:22:"dealer_grand_total_inc";s:9:"filter_by";s:3:"erp";s:9:"condition";s:3:"LTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1523346346484_484";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erporderstatus";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erporderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';
            $values['customerconnect_enabled_messages/CUIS_request/grid_config'] = 'a:9:{s:18:"_1380731654160_160";a:10:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731674478_478";a:10:{s:6:"header";s:12:"Invoice Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:12:"invoice_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:16:"_1380731675005_5";a:10:{s:6:"header";s:8:"Due Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"due_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731675565_565";a:10:{s:6:"header";s:11:"Our Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"our_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380731676086_86";a:10:{s:6:"header";s:12:"Your Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731677645_645";a:10:{s:6:"header";s:6:"Amount";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731678373_373";a:10:{s:6:"header";s:11:"Balance Due";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:17:"outstanding_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731678957_957";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:41:"customerconnect/erp_mapping_invoicestatus";s:5:"index";s:14:"invoice_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:56:"Epicor_Customerconnect_Block_List_Renderer_Invoicestatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1473771488748_748";a:10:{s:6:"header";s:9:"Contracts";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"contracts_contract_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:55:"Epicor_Customerconnect_Block_List_Renderer_ContractCode";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';
        }

        if ($erp == 'p21') {
            $values['epicor_comm_enabled_messages/CRRS_request/grid_config'] = 'a:7:{s:18:"_1421752377517_517";a:10:{s:6:"header";s:13:"Return Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"erp_returns_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752378350_350";a:10:{s:6:"header";s:12:"Created Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"rma_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752380109_109";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"returns_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Rmastatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752640999_999";a:10:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752644174_174";a:10:{s:6:"header";s:13:"Customer Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"customer_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752644536_536";a:10:{s:6:"header";s:9:"Invoice #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:54:"Epicor_Customerconnect_Block_List_Renderer_Linkinvoice";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1421752645110_110";a:10:{s:6:"header";s:6:"Case #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:15:"rma_case_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';
            $values['customerconnect_enabled_messages/CUOS_request/grid_config'] = 'a:8:{s:18:"_1380728508313_313";a:10:{s:6:"header";s:12:"Order Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728509643_643";a:10:{s:6:"header";s:12:"Purchased On";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728510155_155";a:10:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728510674_674";a:10:{s:6:"header";s:10:"Order Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"order_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728511234_234";a:10:{s:6:"header";s:13:"Order Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"order_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728511866_866";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380728513297_297";a:10:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:22:"dealer_grand_total_inc";s:9:"filter_by";s:3:"erp";s:9:"condition";s:3:"LTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1523346346484_484";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erporderstatus";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erporderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';
            $values['customerconnect_enabled_messages/CUIS_request/grid_config'] = 'a:9:{s:18:"_1380731654160_160";a:10:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731674478_478";a:10:{s:6:"header";s:12:"Invoice Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:12:"invoice_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:16:"_1380731675005_5";a:10:{s:6:"header";s:8:"Due Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"due_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731675565_565";a:10:{s:6:"header";s:11:"Our Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:16:"our_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380731676086_86";a:10:{s:6:"header";s:12:"Your Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731677645_645";a:10:{s:6:"header";s:6:"Amount";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731678373_373";a:10:{s:6:"header";s:11:"Balance Due";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:17:"outstanding_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380731678957_957";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:41:"customerconnect/erp_mapping_invoicestatus";s:5:"index";s:14:"invoice_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:56:"Epicor_Customerconnect_Block_List_Renderer_Invoicestatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1473771488748_748";a:10:{s:6:"header";s:9:"Contracts";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"contracts_contract_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:55:"Epicor_Customerconnect_Block_List_Renderer_ContractCode";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';
        }

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
     * @param \Magento\Eav\Setup\EavSetupFactory  $eavSetup
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_0_8($customerSetup)
    {
        $customerSetup->updateAttribute(
            'customer',
            'ecc_allow_masq_cart_reprice',
            'frontend_label',
            'Allowed to Reprice Cart before on Masquerading as Child Account'
        );
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_1_0($customerSetup)
    {
        $attributes = [
            'ecc_is_branch_pickup_allowed'
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
     *
     * Reset CCCS config after issue with
     */
    private function version1_1_5($setup) {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'customerconnect_enabled_messages/CCCS_request/grid_config',
            'value' => 'a:3:{s:16:"_1474537054006_6";a:8:{s:6:"header";s:8:"Erp Code";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"account_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1474537149605_605";a:8:{s:6:"header";s:14:"Contract Title";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"contract_title";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1474537157539_539";a:8:{s:6:"header";s:16:"Contract Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:35:"delivery_addresses_delivery_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:4:"LIKE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:67:"Epicor_Customerconnect_Block_Customer_List_Renderer_DeliveryAddress";}}',
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
     *
     * Update CRQS/CUOS config for dealersPortal
     */
    private function version1_1_7($setup) {

        $erp = false;
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'Epicor_Comm/licensing/erp\'');

        $erpInfo = $var->fetch();

        if (is_array($erpInfo) && $erpInfo['value']) {
            $erp = $erpInfo['value'];
        }
        $values = [
            'customerconnect_enabled_messages/CUOS_request/grid_config' => 'a:8:{s:18:"_1380728508313_313";a:8:{s:6:"header";s:12:"Order Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";}s:18:"_1380728509643_643";a:8:{s:6:"header";s:12:"Purchased On";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";}s:18:"_1380728510155_155";a:8:{s:6:"header";s:12:"Customer Ref";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1380728510674_674";a:8:{s:6:"header";s:10:"Order Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"order_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1380728511234_234";a:8:{s:6:"header";s:13:"Order Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:13:"order_address";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1380728511866_866";a:8:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";}s:18:"_1380728513297_297";a:8:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:22:"dealer_grand_total_inc";s:9:"filter_by";s:3:"erp";s:9:"condition";s:3:"LTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";}s:18:"_1523346346484_484";a:8:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erporderstatus";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erporderstatus";}}',
            'customerconnect_enabled_messages/CRQS_request/grid_config' => 'a:9:{s:18:"_1404741622571_571";a:8:{s:6:"header";s:12:"Quote Number";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:12:"quote_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";}s:18:"_1404741624704_704";a:8:{s:6:"header";s:4:"Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"quote_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:5:"LT/GT";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";}s:18:"_1404741625304_304";a:8:{s:6:"header";s:11:"Description";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:11:"description";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1404741625856_856";a:8:{s:6:"header";s:18:"Customer Reference";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:18:"customer_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1404741626232_232";a:8:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:14:"original_value";s:9:"filter_by";s:3:"erp";s:9:"condition";s:5:"LT/GT";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";}s:18:"_1404741626808_808";a:8:{s:6:"header";s:5:"Price";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:22:"dealer_grand_total_inc";s:9:"filter_by";s:3:"erp";s:9:"condition";s:5:"LT/GT";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Customerconnect_Block_List_Renderer_Currency";}s:17:"_1455713631030_30";a:8:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:42:"customerconnect/erp_mapping_erpquotestatus";s:5:"index";s:12:"quote_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:57:"Epicor_Customerconnect_Block_List_Renderer_Erpquotestatus";}s:15:"_1481124692_657";a:8:{s:6:"header";s:10:"Quote name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:27:"quote_delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";}s:18:"_1523358513970_970";a:8:{s:6:"header";s:9:"Contracts";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"contracts_contract_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:55:"Epicor_Customerconnect_Block_List_Renderer_ContractCode";}}',];
        foreach ($values as $path => $value) {
            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => $path,
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
    private function version1_1_9($customerSetup) {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_cuco_pending', [
            'group' => 'General',
            'label' => 'Is ECC CUCO Pending?',
            'type' => 'int',
            'input' => 'select',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required' => false,
            'user_defined' => false,
            'visible' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'default' => 0
        ]);
        $attributes = [
            'ecc_cuco_pending',
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
     *
     * Update web reference increment from store to default with max increment count
     */
    private function version1_2_0($setup) {
        $refCount = 0;

        //Get all stores
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $oldRfqCount = $this->scopeConfig->getValue('customerconnect/rfqs/increment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            if ($oldRfqCount && $oldRfqCount > $refCount) {
                $refCount = $oldRfqCount;
            }
        }

        // Save     
        $oldReferenceIncrement = $this->scopeConfig->getValue('customerconnect/reference/increment');
        if($oldReferenceIncrement && $oldReferenceIncrement > $refCount){
            $refCount = $oldReferenceIncrement;
        }
        $this->configWriter->save(
            'customerconnect/reference/increment', $refCount
        );

        // Clean config cache 
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_2_1($customerSetup) {

        $customerSetup->updateAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ecc_cuco_pending',
            'is_system',
            0
        );
    }

    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     *
     * Update web reference increment from store to default with max increment count
     */
    private function version1_2_2($setup) {
        $refCount = 0;

        $oldReferenceIncrement = $this->scopeConfig->getValue('customerconnect/reference/increment');
        if ($oldReferenceIncrement) {
            $refCount = $oldReferenceIncrement;
        }

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $oldRfqCount = $this->scopeConfig->getValue('customerconnect/rfqs/increment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            if ($oldRfqCount && $oldRfqCount > $refCount) {
                $refCount = $oldRfqCount;
            }
            $this->configWriter->delete('customerconnect/rfqs/increment', 'stores', $storeId);
        }

        // Save     
        $this->configWriter->save(
            'customerconnect/quote/increment', $refCount
        );

        // Save     
        $this->configWriter->save(
            'customerconnect/return/increment', $refCount
        );

        $this->configWriter->delete('customerconnect/reference/increment', 'default');

        // Clean config cache 
        $this->cacheTypeList->cleanType('config');
    }

    private function version1_2_4($eavSetup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $attribute = $this->eavConfig->getAttribute($entityTypeId, 'ecc_product_type');
        //$eavSetup->removeAttribute($entityTypeId, 'ecc_product_type');
        if (!$attribute || !$attribute->getAttributeId()) {
            $eavSetup->addAttribute(
                $entityTypeId,
                'ecc_product_type',
                [
                    'group' => 'General',
                    'label' => 'Product Type',
                    'type' => 'varchar',
                    'input' => 'select',
                    'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Eccproducttype',
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
                    'default' => ''
                ]
            );
        }
    }

    /**
     * Creates Product attribute
     *
     * @param EavSetup $installer
     */
    private function version1_2_8($eavSetup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_related_documents_synced');
        $eavSetup->addAttribute(
            $entityTypeId,
            'ecc_related_documents_synced',
            [
                'group' => 'General',
                'label' => 'ECC Related Documents Synced',
                'type' => 'int',
                'input' => 'boolean',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 192
            ]
        );
    }

    protected function version1_3_0($eavSetup)
    {

        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_pricing_sku');
        $eavSetup->addAttribute($entityTypeId, 'ecc_pricing_sku', [
            'group'                         => 'General',
            'label'                         => 'Pricing SKU',
            'type'                          => 'varchar',
            'input'                         => 'text',
            'visible'                       => true,
            'required'                      => false,
            'user_defined'                  => true,
            'searchable'                    => false,
            'filterable'                    => false,
            'comparable'                    => false,
            'visible_on_front'              => true,
            'visible_in_advanced_search'    => false,
            'used_in_product_listing'       => true,
            'global'                        => ScopedAttributeInterface::SCOPE_GLOBAL,
            'ecc_created_by'                => 'Y',
            'sort_order'                    => 4
        ]);
    }

    /**
     * Creates Product attribute
     *
     * @param EavSetup $installer
     */
    private function version1_3_1($eavSetup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_decimal_places');
        $eavSetup->addAttribute(
            $entityTypeId,
            'ecc_decimal_places',
            [
                'group' => 'General',
                'label' => 'Number of Decimal Places',
                'type' => 'int',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'ecc_created_by' => 'Y',
                'note' => 'Blank => Site Default; 0 => Only Integer; (1-4) => Given Decimal Places',
                'frontend_class' => 'validate-digits-range digits-range-0-4',
                'sort_order' => 185
            ]
        );
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_3_6($customerSetup) {
        $entityTypeId = \Magento\Customer\Model\Customer::ENTITY;
        $customerSetup->addAttribute($entityTypeId, 'ecc_web_enabled', [
            'group' => 'General',
            'label' => 'Web Enabled',
            'type' => 'int',
            'input' => 'text',
            'required' => false,
            'user_defined' => false,
            'visible' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'default' => 0,
            'system' => false,
        ]);
        $attributes = [
            'ecc_web_enabled',
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }

    private  function  version1_3_7($eavSetup, $setup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_pack_size',
            'used_in_product_listing',
            true
        );
    }

    /*
     * Set Location Grid Column config
     */
    private function version1_3_8_7($setup) {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $tableName = $setup->getTable('core_config_data');
        $query = 'SELECT value FROM ' . $tableName . ' WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_comm_locations/admin/grid_columns\'';
        $var = $writeConnection->query($query);
        $val = $var->fetch();

        if (is_array($val) && isset($val['value']) && $val['value']) {
            $value = $val['value'];
            if (strpos($value, 'location_visible') === false) {
                $value .= ',location_visible';
            }
            if (strpos($value, 'include_inventory') === false) {
                $value .= ',include_inventory';
            }
            if (strpos($value, 'show_inventory') === false) {
                $value .= ',show_inventory';
            }
            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'epicor_comm_locations/admin/grid_columns',
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
     *
     * Update SPLS config after issue with
     */
    private function version1_3_9($setup) {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPLS_request/grid_config',
            'value' => 'a:8:{s:18:"_1380792275502_502";a:9:{s:6:"header";s:11:"Part Number";s:4:"type";s:4:"text";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792285534_534";a:9:{s:6:"header";s:15:"Cross Reference";s:4:"type";s:4:"text";s:5:"index";s:15:"cross_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792286124_124";a:9:{s:6:"header";s:20:"Cross Reference Type";s:4:"type";s:4:"text";s:5:"index";s:20:"cross_reference_type";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792286613_613";a:9:{s:6:"header";s:14:"Operation Code";s:4:"type";s:4:"text";s:5:"index";s:16:"operational_code";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792287157_157";a:9:{s:6:"header";s:14:"Effective Date";s:4:"type";s:4:"date";s:5:"index";s:14:"effective_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792287716_716";a:9:{s:6:"header";s:15:"Expiration Date";s:4:"type";s:4:"date";s:5:"index";s:15:"expiration_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792288220_220";a:9:{s:6:"header";s:15:"Base Unit Price";s:4:"type";s:4:"text";s:5:"index";s:5:"price";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792288837_837";a:9:{s:6:"header";s:3:"UOM";s:4:"type";s:4:"text";s:5:"index";s:20:"unit_of_measure_code";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}',
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'),
            $data,
            ['value']
        );
    }


    private function version1_4_0(\Magento\Eav\Setup\EavSetup $customerSetup){

        $attributes = [
            'ecc_mobile_number',
            'ecc_contracts_filter',
            'ecc_prev_supplier_erpaccount',
            'ecc_previous_erpaccount',
            'ecc_allow_masq_cart_reprice',
            'ecc_allow_masq_cart_clear',
            'ecc_allow_masquerade'
        ];

        foreach($attributes as $attributeCode){
            $this->updateGroup($customerSetup, $attributeCode);
        }
    }

    private function updateGroup(\Magento\Eav\Setup\EavSetup $customerSetup, $attributeCode)
    {
        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode)
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();
        $customerSetup->addAttributeToGroup(Customer::ENTITY,'Default','General',$attributeCode);
    }

    private function version1_4_1(\Magento\Customer\Setup\CustomerSetup $installer)
    {
        $attributes = [
            ['code' => 'hazard_class', 'label' => 'Hazard Class'],
            ['code' => 'hazard_class_desc', 'label' => 'Hazard Class Description'],
            ['code' => 'hazard_code', 'label' => 'Hazard Code'],
            ['code' => 'id_number', 'label' => 'ID Number'],

        ];
        $this->addAttributes($attributes, $installer);
    }

    private function addAttributes($attributeData, \Magento\Customer\Setup\CustomerSetup $installer)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        foreach($attributeData as $attribute){
            if(!$installer->getAttributeId($entityTypeId, $attribute['code'])) {
                $installer->addAttribute(
                    $entityTypeId,
                    $attribute['code'],
                    [
                        'group' => 'General',
                        'label' => $attribute['label'],
                        'type' => 'varchar',
                        'input' => 'text',
                        'required' => false,
                        'user_defined' => false,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'visible_in_advanced_search' => false
                    ]
                );
            }
        }
    }

    private function version1_4_3($eavSetup, $setup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_pack_size',
            'used_in_product_listing',
            true
        );
    }

    private function version1_4_7($eavSetup, $setup)
    {
        $tb_eccLocationProduct = $setup->getTable('ecc_location_product');
        $tb_eccLocationProductCurrency = $setup->getTable('ecc_location_product_currency');
        $tb_catalogProductEntity = $setup->getTable('catalog_product_entity');

        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $writeConnection = $setup->getConnection('core_write');

        $queryFor_elp= 'DELETE '.$tb_eccLocationProduct.' 
FROM  '.$tb_eccLocationProduct.'
        LEFT JOIN
    '.$tb_catalogProductEntity.' ON  '.$tb_eccLocationProduct.'.product_id = '.$tb_catalogProductEntity.'.entity_id 
WHERE
    '.$tb_catalogProductEntity.'.entity_id  IS NULL;';

       $writeConnection->query($queryFor_elp);

       $queryFor_elpc = 'DELETE '.$tb_eccLocationProductCurrency.' 
FROM  '.$tb_eccLocationProductCurrency.'
        LEFT JOIN
    '.$tb_catalogProductEntity.' ON  '.$tb_eccLocationProductCurrency.'.product_id = '.$tb_catalogProductEntity.'.entity_id 
WHERE
    '.$tb_catalogProductEntity.'.entity_id  IS NULL;';

       $writeConnection->query($queryFor_elpc);

    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_4_8($customerSetup) {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_hide_price', [
            'group' => 'General',
            'label' => 'Hide Prices',
            'type' => 'int',
            'input' => 'select',
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\HidePriceOptions',
            'required' => false,
            'user_defined' => false,
            'visible' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'default' => 0,
            'system' => false
        ]);
        $attributes = [
            'ecc_hide_price',
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }

    private function version1_5_0($eavSetup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->updateAttribute(
            $entityTypeId,
            'ecc_uom_filter',
            'is_filterable',
            0
        );
    }
    private function version1_5_2($eavSetup, $customerSetup)
    {
        //redefine ecc_email and ecc_mobile_number as user_devined : 0
        $eavSetup->removeAttribute('customer_address', 'ecc_email');
        $eavSetup->addAttribute('customer_address', 'ecc_email', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Email',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 0,
            'visible_on_front' => 1,
            'system' => false
        ]);
        $eavSetup->removeAttribute('customer_address', 'ecc_mobile_number');
        $eavSetup->addAttribute('customer_address', 'ecc_mobile_number', [
                'type' => 'text',
                'input' => 'text',
                'label' => 'Mobile Number',
                'nullable' => true,
                'global' => 1,
                'visible' => 1,
                'required' => 0,
                'user_defined' => 0,
                'default' => ' ',
                'visible_on_front' => 1,
                'multiline_count' => 1,
                'system' => false
        ]);



        $attributes = [
            'ecc_mobile_number',
            'ecc_email'
        ];

        $usedInForms = array(
            'adminhtml_customer_address',
            'customer_address_edit',
            'customer_register_address'
        );

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer_address', $attributeCode);
            $attr->setData('used_in_forms', $usedInForms);
            $attr->save();
        }
    }

    private function version1_5_4($installer)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $installer->removeAttribute($entityTypeId, 'ecc_configurable_part_price');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_configurable_part_price',
            [
                'group' => 'General',
                'label' => 'Configurable Part Price',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true
            ]
        );
    }

    private function version1_5_7($customerSetup)
    {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_per_contact_id', [
            'group' => 'General',
            'label' => 'Personal Contact ID',
            'type' => 'text',
            'length' => 255,
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);
        $attributes = [
            'ecc_per_contact_id',
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }
    }
    private function version1_5_8($eavSetup)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $eavSetup->removeAttribute($entityTypeId, 'ecc_price_differential_display');
        $eavSetup->addAttribute($entityTypeId, 'ecc_price_differential_display', [
            'attribute_set'=> 'Default',
            'group' => 'General',
            'label' => 'Show Options Price Differential',
            'type' => 'int',
            'input' => 'select',
            'visible' => true,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 110,
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnoglobaloption',
            'system' => false,
            'default' => '2',
            'apply_to'=>'configurable',
            'ecc_created_by' => 'Y',
            'used_in_product_listing' => true,
            'note' => 'Applicable to versions magento 2.3.1 and above only',
        ]);

    }

    private function version1_6_0($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'cataloginventory/options/show_out_of_stock\'');

        $erpInfo = $var->fetch();
        if ($erpInfo != false && $erpInfo['value'] === 1) {
            return;
        }
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'cataloginventory/options/show_out_of_stock',
            'value' => 1,
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'),
            $data,
            ['value']
        );
    }

    private function version1_6_2($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        $defaultCompany = $this->scopeConfig->getValue('Epicor_Comm/licensing/company');
        if (!is_null($defaultCompany)
            && $defaultCompany != ''
        ) {
            $tableName = $setup->getTable('ecc_location');
            $updateSql = "UPDATE " . $tableName . " SET company = '" . $defaultCompany . "' WHERE company IS NULL";
            /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
            $writeConnection->rawQuery($updateSql);
        }
        $query = 'SELECT value FROM ' . $setup->getTable('core_config_data') . ' WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_comm_locations/admin/grid_columns\'';
        $var = $writeConnection->query($query);
        $val = $var->fetch();
        if (is_array($val) && isset($val['value']) && $val['value']) {
            $value = $val['value'];
            if (strpos($value, 'company') === false) {
                $value .= ',company';
            }
            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'epicor_comm_locations/admin/grid_columns',
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
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version1_6_3($customerSetup)
    {
        $attributes = [
            'ecc_per_contact_id',
            'ecc_custom_address_allowed',
            'ecc_supplier_erpaccount_id',
            'ecc_sales_rep_account_id',
            'ecc_erpaccount_id',
            'ecc_location_link_type',
            'ecc_default_contract_address',
            'ecc_default_contract',
            'ecc_contract_shipto_prompt',
            'ecc_contract_shipto_default',
            'ecc_contract_shipto_date',
            'ecc_contract_line_selection',
            'ecc_contract_line_prompt',
            'ecc_contract_line_always',
            'ecc_contract_header_selection',
            'ecc_contract_header_prompt',
            'ecc_contract_header_always',
            'ecc_contact_code'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('is_user_defined', 0);
            $attr->setData('is_visible', 0);
            $attr->save();
        }
    }
	
	/**
     * @param $installer
     */
    private function version1_6_4($installer)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $installer->addAttribute(
            $entityTypeId,
            'supplierpartnumber',
            [
                'group' => 'General',
                'label' => 'Supplier Part Number',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true
            ]
        );
    }

    /**
     * @param $setup
     * @throws \Zend_Db_Exception
     */
    private function version1_6_8($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'customerconnect_enabled_messages/CURS_request/grid_config',
            'value' => 'a:10:{s:18:"_1380790101624_624";a:10:{s:6:"header";s:9:"Returns #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"returns_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790114387_387";a:10:{s:6:"header";s:4:"Line";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:4:"line";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790114941_941";a:10:{s:6:"header";s:4:"Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:8:"rma_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790115445_445";a:10:{s:6:"header";s:12:"Product Code";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790115925_925";a:10:{s:6:"header";s:14:"Revision Level";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:14:"revision_level";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790116757_757";a:10:{s:6:"header";s:11:"Qty Ordered";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:18:"quantities_ordered";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790117269_269";a:10:{s:6:"header";s:12:"Qty Returned";s:4:"type";s:6:"number";s:7:"options";s:0:"";s:5:"index";s:19:"quantities_returned";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790117813_813";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:37:"customerconnect/erp_mapping_rmastatus";s:5:"index";s:14:"returns_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Rmastatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790118365_365";a:10:{s:6:"header";s:7:"Order #";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:12:"order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:52:"Epicor_Customerconnect_Block_List_Renderer_Linkorder";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380790118909_909";a:10:{s:6:"header";s:10:"Order Line";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:10:"order_line";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}'
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'),
            $data,
            ['value']
        );
    }

    /**
     * @param $installer
     */
    private function version1_6_9($installer)
    {
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $installer->removeAttribute($entityTypeId, 'is_ecc_discontinued');
        $installer->addAttribute(
            $entityTypeId,
            'is_ecc_discontinued',
            [
                'group' => 'General',
                'label' => 'Discontinued',
                'type' => 'int',
                'input' => 'boolean',
                'default' => '0',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true
            ]
        );

        $installer->removeAttribute($entityTypeId, 'is_ecc_non_stock');
        $installer->addAttribute(
            $entityTypeId,
            'is_ecc_non_stock',
            [
                'group' => 'General',
                'label' => 'Non Stock',
                'type' => 'int',
                'input' => 'boolean',
                'default' => '0',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true
            ]
        );
    }

    /**
     * Update substitute Config Path.
     *
     * @param $setup
     */
    private function version1_7_0($setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        $tableName = $setup->getTable('core_config_data');
        $oldPath   = 'epicor_product_config/substitute/enable';
        $newPath   = 'epicor_comm_enabled_messages/msq_request/triggers_linked_products_substitute';
        $query     = "SELECT * FROM ".$tableName." WHERE path = '".$oldPath."'";
        $var       = $writeConnection->query($query);
        $value     = $var->fetch();

        $newQuery     = "SELECT * FROM ".$tableName." WHERE path = '".$newPath."'";
        $newVar       = $writeConnection->query($newQuery);
        $newValue     = $newVar->fetch();

        if($newValue) {
            $updateSql = "DELETE FROM " . $tableName . " WHERE `path` = '" . $newPath . "'";
            /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
            $writeConnection->rawQuery($updateSql);
        }

        if($value) {
            $updateSql = "UPDATE " . $tableName . " SET path = '" . $newPath . "' WHERE path = '" . $oldPath . "'";
            /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
            $writeConnection->rawQuery($updateSql);
        }//endif

    }//end version1_7_0()

    /**
     * Creates Sequence for Quotes
     */
    private function version1_7_1()
    {
        $startValue = $this->scopeConfig->getValue('customerconnect/quote/increment');
        if (!$startValue) {
            $startValue = $this->sequenceConfig->get('startValue');
        } else {
            $startValue++;
        }
        $entityType = "eccquote";
        try {
            $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($startValue)
                ->setStoreId(0)
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType($entityType)
                ->create();
        } catch (AlreadyExistsException $e) {
            return;
        } catch (\Exception $e) {
            return;
        }
        return;
    }

    /**
     * Setting the Default value of ecc_mobile number to null
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetup
     */
    private function version1_7_2($eavSetup)
    {
        $attributeId = $eavSetup->getAttributeId('customer_address', 'ecc_mobile_number');
        if ($attributeId) {
            $eavSetup->updateAttribute('customer_address',
                'ecc_mobile_number',
                'default_value'
            );
        }
    }

    /**
     * Update Config Path from stk_mapping and apply to new msq_request options
     *
     * @param $setup
     */
    private function version1_7_3($setup)
    {
        $stk_mapping_array = ['currencies_update', 'lead_time_days_update', 'lead_time_text_update',
            'free_stock_update', 'product_manage_stock_update', 'product_max_order_qty_update',
            'product_min_order_qty_update', 'locations_update', 'location_stock_status',
            'location_free_stock', 'location_minimum_order_qty', 'location_maximum_order_qty',
            'location_lead_time_days', 'location_lead_time_text', 'location_pricing'
        ];
        $oldPath = 'epicor_comm_field_mapping/stk_mapping';
        $newPath = 'epicor_comm_enabled_messages/msq_request';

        $writeConnection = $setup->getConnection('core_write');
        $stkValuesToCopy = [] ;

        //retrieve all values for all stores for the relevant mapping array
        foreach($stk_mapping_array as $sqlValue){

            $query     = "SELECT * FROM ".'core_config_data'." WHERE path = '".$oldPath."/".$sqlValue."'";
            $var       = $writeConnection->query($query);
            $value     = $var->fetchAll();
            foreach($value as $val){
                $stkValuesToCopy[] = ['row' => $val, 'key'=>$sqlValue];
            }
        }

        //write each stk mapping array value to the new path
        foreach ($stkValuesToCopy as $newValue) {
            $requiredPath = $newPath . "/" . $newValue['key'];
            $this->configWriter->save($requiredPath, $newValue['row']['value'],
                $newValue['row']['scope'], $newValue['row']['scope_id']);
        }
    }//end version1_7_3()
}
