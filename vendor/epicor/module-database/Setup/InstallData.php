<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Database\Setup;


use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallData implements InstallDataInterface
{

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $installer */
        $installer = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->version_1_0_0($installer);
        }
    }

    protected function version_1_0_0(EavSetup $installer)
    {
        $entityTypeId = \Magento\Catalog\Model\Category::ENTITY;

        $installer->removeAttribute($entityTypeId, 'ecc_erp_code');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_code',
            [
                'group' => 'General Information',
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'input' => 'text',
                'label' => 'ERP Code',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_erp_images');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_images',
            [
                'group' => 'General Information',
                'type' => 'text',
                'backend' => 'Epicor\Comm\Model\Eav\Attribute\Backend\Serialized',
                'label' => 'ERP Images',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                //'input_renderer' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpimages\Category',
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_erp_images_last_processed');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_images_last_processed',
            [
                'group' => 'General Information',
                'label' => 'Last ERP Image process time for this category',
                'type' => 'datetime',
                'input' => 'date',
                'default' => null,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_erp_images_processed');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_images_processed',
            [
                'group' => 'General Information',
                'label' => 'Images synced from ERP',
                'type' => 'int',
                'input' => 'select',
                'default' => '0',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_is_new');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_is_new',
            [
                'group' => 'General',
                'label' => 'Is New',
                'type' => 'int',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
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
                'default' => 1
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_previous_erp_images');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_previous_erp_images',
            [
                'group' => 'General Information',
                'type' => 'text',
                'backend' => 'Epicor\Comm\Model\Eav\Attribute\Backend\Serialized',
                'label' => 'Previous ERP Images',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
            ]
        );

        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;

        $installer->removeAttribute($entityTypeId, 'ecc_condition');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_condition',
            [
                'group' => 'General',
                'label' => 'Condition',
                'type' => 'varchar',
                'input' => 'select',
                'default' => 'new',
                'source' => '',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_configurator');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_configurator',
            [
                'group' => 'General',
                'type' => 'int',
                'label' => 'Configurator Product',
                'input' => 'boolean',
                'class' => '',
                'default' => 0,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                'used_in_product_listing' => true,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_default_category_position');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_default_category_position',
            [
                'group' => 'General',
                'label' => 'Default group Position',
                'type' => 'int',
                'input' => 'text',
                'default' => '',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_default_uom');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_default_uom',
            [
                'group' => 'General',
                'label' => 'Default UOM',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => true,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_ean');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_ean',
            [
                'group' => 'General',
                'label' => 'EAN',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_price_display_type');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_price_display_type',
            [
                'group' => 'General',
                'label' => 'Price Display Type',
                'type' => 'int',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'option' =>
                    [
                        'values' =>
                            [
                                0 => 'Default',
                                1 => 'Range',
                            ]
                    ]
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_erp_images');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_images',
            [
                'group' => 'Image Management',
                'type' => 'text',
                'backend' => 'Epicor\Comm\Model\Eav\Attribute\Backend\Serialized',
                'label' => 'ERP Images',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                //'input_renderer' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpimages',
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_erp_images_last_processed');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_images_last_processed',
            [
                'group' => 'Image Management',
                'label' => 'Last ERP Image process time for this product',
                'type' => 'datetime',
                'input' => 'date',
                'default' => null,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_erp_images_processed');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_erp_images_processed',
            [
                'group' => 'Image Management',
                'label' => 'Images synced from ERP',
                'type' => 'int',
                'input' => 'boolean',
                'default' => '0',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_google_feed');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_google_feed',
            [
                'group' => 'General',
                'label' => 'Show in Google Product Feed',
                'type' => 'int',
                'input' => 'boolean',
                'default' => '1',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_isbn');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_isbn',
            [
                'group' => 'General',
                'label' => 'ISBN',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_is_new');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_is_new',
            [
                'group' => 'General',
                'label' => 'Is New',
                'type' => 'int',
                'input' => 'boolean',
                'required' => false,
                'user_defined' => false,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'default' => 1
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_last_msq_update');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_last_msq_update',
            [
                'group' => 'General',
                'label' => 'Last update from ERP',
                'type' => 'datetime',
                'input' => 'date',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_lead_time');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_lead_time',
            [
                'group' => 'General',
                'label' => 'Lead time',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => true,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_line_comments_enabled');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_line_comments_enabled',
            [
                'group' => 'General',
                'label' => 'Enable Line Comments',
                'type' => 'int',
                'input' => 'boolean',
                'default' => '1',
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

        $installer->removeAttribute($entityTypeId, 'ecc_manufacturers');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_manufacturers',
            [
                'group' => 'General',
                'label' => 'Manufacturers',
                'type' => 'text',
                'input' => 'multiselect',
                'backend' => 'Epicor\Comm\Model\Eav\Attribute\Data\Manufacturers',
                'input_renderer' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Manufacturers', //definition of renderer
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_more_info_file');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_more_info_file',
            [
                'group' => 'General',
                'label' => 'More Info',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_more_info_raw');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_more_info_raw',
            [
                'group' => 'General',
                'label' => 'More Info Raw Data',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_mpn');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_mpn',
            [
                'group' => 'General',
                'label' => 'MPN',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_oldskus');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_oldskus',
            [
                'group' => 'General',
                'label' => 'Old Skus',
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

        $installer->removeAttribute($entityTypeId, 'ecc_pack_size');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_pack_size',
            [
                'group' => 'General',
                'label' => 'Pack Size',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => true,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_previous_erp_images');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_previous_erp_images',
            [
                'group' => 'Image Management',
                'type' => 'text',
                'backend' => 'Epicor\Comm\Model\Eav\Attribute\Backend\Serialized',
                'label' => 'Previous ERP Images',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_related_documents');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_related_documents',
            [
                'group' => 'General',
                'type' => 'text',
                'backend' => 'Epicor\Comm\Model\Eav\Attribute\Data\Relateddocuments',
                'input_renderer' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Relateddocuments', //definition of renderer
                'label' => 'Related Documents',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_reorderable');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_reorderable',
            [
                'group' => 'General',
                'label' => 'Reorderable',
                'type' => 'int',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'default' => '1',
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_show_availability');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_show_availability',
            [
                'group' => 'General',
                'label' => 'Enable Availablility Check',
                'type' => 'int',
                'input' => 'boolean',
                'default' => '1',
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

        $installer->removeAttribute($entityTypeId, 'ecc_stk_type');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_stk_type',
            [
                'group' => 'General',
                'label' => 'STK Type flag',
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

        $installer->removeAttribute($entityTypeId, 'ecc_stockleveldisplay');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_stockleveldisplay',
            [
                'group' => 'General',
                'label' => 'Stock Level',
                'type' => 'varchar',
                'input' => 'select',
                'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Stocklevel',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'default' => '',
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_stocklimitlow');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_stocklimitlow',
            [
                'group' => 'General',
                'label' => 'Stock Level Limit Amber Indicator',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'default' => '',
                'used_in_product_listing' => true
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_stocklimitnone');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_stocklimitnone',
            [
                'group' => 'General',
                'label' => 'Stock Level Limit Red Indicator',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'default' => '',
                'used_in_product_listing' => true
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_uom');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_uom',
            [
                'group' => 'General',
                'label' => 'UOM',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => true,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_uom_filter');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_uom_filter',
            [
                'group' => 'General',
                'label' => 'UOM Filter',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true
            ]
        );

        $installer->removeAttribute($entityTypeId, 'ecc_upc');
        $installer->addAttribute(
            $entityTypeId,
            'ecc_upc',
            [
                'group' => 'General',
                'label' => 'UPC',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'user_defined' => false,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
            ]
        );

        //customer_address eav attribute
        $installer->removeAttribute('customer_address', 'ecc_erp_group_code');
        $installer->addAttribute('customer_address', 'ecc_erp_group_code', [
            'label' => 'ERP Group Code',
            'type' => 'varchar',
            'frontend_input' => 'text',
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'sort_order' => 140,
            'required' => 0,
            'multiline_count' => 0,
            'validate_rules' => $this->serializer->serialize([
                'max_text_length' => 255,
                'min_text_length' => 1
            ])
        ]);

        $installer->removeAttribute('customer_address', 'ecc_erp_address_code');
        $installer->addAttribute('customer_address', 'ecc_erp_address_code', [
            'label' => 'ERP Address Code',
            'type' => 'varchar',
            'input' => 'text',
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'sort_order' => 140,
            'required' => 0,
            'multiline_count' => 0,
            'validate_rules' => $this->serializer->serialize([
                'max_text_length' => 255,
                'min_text_length' => 1
            ])
        ]);

        $installer->removeAttribute('customer_address', 'ecc_instructions');
        $installer->addAttribute('customer_address', 'ecc_instructions', [
            'label' => 'Instructions',
            'type' => 'varchar',
            'input' => 'textarea',
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'sort_order' => 140,
            'required' => 0,
            'multiline_count' => 0,'validate_rules' => $this->serializer->serialize([
                'max_text_length' => 5000,
                'min_text_length' => 0
            ])
        ]);

        $installer->removeAttribute('customer_address', 'ecc_is_registered');
        $installer->addAttribute('customer_address', 'ecc_is_registered', [
            'type' => 'int',
            'input' => 'boolean',
            'label' => 'Is Registered Address',           
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'required' => 0,
            'default' => 0,
            'visible_on_front' => false,
        ]);
        $installer->removeAttribute('customer_address', 'ecc_is_delivery');
        $installer->addAttribute('customer_address', 'ecc_is_delivery', [
            'type' => 'int',
            'input' => 'boolean',
            'label' => 'Is Delivery Address',        
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'required' => 0,
            'default' => 0,
            'visible_on_front' => false,
        ]);

        $installer->removeAttribute('customer_address', 'ecc_is_invoice');
        $installer->addAttribute('customer_address', 'ecc_is_invoice', [
            'type' => 'int',
            'input' => 'boolean',
            'label' => 'Is Billing Address',        
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'required' => 0,
            'default' => 0,
            'visible_on_front' => false,
        ]);
        
        $installer->removeAttribute('customer_address', 'ecc_email');
        $installer->addAttribute('customer_address', 'ecc_email', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Email',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1,
            'system' => false
        ]);
        $installer->removeAttribute('customer_address', 'ecc_mobile_number');
        $installer->addAttribute('customer_address', 'ecc_mobile_number', [
            'type' => 'text',
            'input' => 'text',
            'label' => 'Mobile Number',
            'nullable' => true,
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'default' => ' ',
            'visible_on_front' => 1,
            'multiline_count' => 1,
            'system' => false
        ]);
        //customer eav attribute
        $installer->removeAttribute('customer', 'ecc_contract_header_always');
        $installer->addAttribute('customer', 'ecc_contract_header_always', [
            'group' => 'General',
            'label' => 'Always use Header Contract when Available',
            'input' => 'text',
            'type' => 'int',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_header_prompt');
        $installer->addAttribute('customer', 'ecc_contract_header_prompt', [
            'group' => 'General',
            'label' => 'Prompt for Header Selection if More Than 1',
            'type' => 'int',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_header_selection');
        $installer->addAttribute('customer', 'ecc_contract_header_selection', [
            'group' => 'General',
            'label' => 'Header Contract Selection',
            'type' => 'varchar',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_line_always');
        $installer->addAttribute('customer', 'ecc_contract_line_always', [
            'group' => 'General',
            'label' => 'Always use Line Level Contract when Available',
            'type' => 'int',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_line_prompt');
        $installer->addAttribute('customer', 'ecc_contract_line_prompt', [
            'group' => 'General',
            'label' => 'Show Dropdown for Optional Contracts',
            'type' => 'int',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_line_selection');
        $installer->addAttribute('customer', 'ecc_contract_line_selection', [
            'group' => 'General',
            'label' => 'Line Contract Selection',
            'type' => 'varchar',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_shipto_date');
        $installer->addAttribute('customer', 'ecc_contract_shipto_date', [
            'group' => 'General',
            'label' => 'Use Ship To Based on Contract Date',
            'type' => 'varchar',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_shipto_default');
        $installer->addAttribute('customer', 'ecc_contract_shipto_default', [
            'group' => 'General',
            'label' => 'Default Ship To Selection',
            'type' => 'varchar',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_contract_shipto_prompt');
        $installer->addAttribute('customer', 'ecc_contract_shipto_prompt', [
            'group' => 'General',
            'label' => 'Prompt for Ship To Selection if More Than 1',
            'type' => 'int',
            'input' => 'text',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_default_contract');
        $installer->addAttribute('customer', 'ecc_default_contract', [
            'group' => 'General',
            'label' => 'Default Contract',
            'type' => 'int',
            'input' => 'text',
            //epicor_common/eav_entity_attribute_frontend_erpdefaultcontract
            'frontend' => 'Epicor\Common\Model\Eav\Entity\Attribute\Frontend\Erpdefaultcontract',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_default_contract_address');
        $installer->addAttribute('customer', 'ecc_default_contract_address', [
            'group' => 'General',
            'label' => 'Default Contract Address',
            'type' => 'varchar',
            'input' => 'text',
            //epicor_common/eav_entity_attribute_frontend_erpdefaultcontractaddress
            'frontend' => 'Epicor\Common\Model\Eav\Entity\Attribute\Frontend\Erpdefaultcontractaddress',
            'default' => null,
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_default_location_code');
        $installer->addAttribute('customer', 'ecc_default_location_code', [
            'group' => 'General',
            'label' => 'Default Location Code',
            'type' => 'varchar',
            'input' => 'text',
            'default' => '',
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'sort_order' => 126,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_erp_account_type');
        $installer->addAttribute('customer', 'ecc_erp_account_type', [
            'group' => 'General',
            'label' => 'ERP Account Type',
            'type' => 'text',
            'input' => 'text',
            //epicor_common/eav_attribute_data_erpaccounttype
            'backend' => 'Epicor\Common\Model\Eav\Attribute\Data\Erpaccounttype',
            //epicor_common/eav_entity_attribute_frontend_erpaccounttype
            'frontend' => 'Epicor\Common\Model\Eav\Entity\Attribute\Frontend\Erpaccounttype',
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

        $installer->removeAttribute('customer', 'ecc_location_link_type');
        $installer->addAttribute('customer', 'ecc_location_link_type', [
            'group' => 'General',
            'label' => 'Location Link Type',
            'type' => 'varchar',
            'input' => 'text',
            'default' => '',
            'visible' => false,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'sort_order' => 5,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_master_shopper');
        $installer->addAttribute('customer', 'ecc_master_shopper', [
            'group' => 'General',
            'label' => 'Master Shopper',
            'type' => 'int',
            'input' => 'boolean',
            'default' => 0,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_salesrep_catalog_access');
        $installer->addAttribute('customer', 'ecc_salesrep_catalog_access', [
            'group' => 'General',
            'label' => 'Sales Rep Can Access Catalog',
            'type' => 'varchar',
            'input' => 'select',
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            //epicor_salesrep/eav_attribute_data_yesnonulloption
            'source' => 'Epicor\SalesRep\Model\Eav\Attribute\Data\Yesnonulloption',
            'default' => '',
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_erpaccount_id');
        $installer->addAttribute('customer', 'ecc_erpaccount_id', [
            'group' => 'General',
            'label' => 'ERP Account',
            'type' => 'int',
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => 1,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'visible' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_erp_login_id');
        $installer->addAttribute('customer', 'ecc_erp_login_id', [
            'group' => 'General',
            'label' => 'ERP Login ID',
            'type' => 'text',
            'length' => 255,
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => 1,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_fax_number');
        $installer->addAttribute('customer', 'ecc_fax_number', [
            'group' => 'General',
            'label' => 'Fax Number',
            'type' => 'text',
            'length' => 255,
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => 1,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_function');
        $installer->addAttribute('customer', 'ecc_function', [
            'group' => 'General',
            'label' => 'Function',
            'type' => 'text',
            'length' => 255,
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => 1,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_is_branch_pickup_allowed');
        $installer->addAttribute('customer', 'ecc_is_branch_pickup_allowed', [
            'group' => 'General',
            'label' => 'Branch Pickup Allowed',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 6,
            //epicor_branchpickup/eav_attribute_data_branchoptions
            'source' => 'Epicor\BranchPickup\Model\Eav\Attribute\Data\Branchoptions',
            'default' => '2',
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_mobile_number');
        $installer->addAttribute('customer', 'ecc_mobile_number', [
            'group' => 'General',
            'label' => 'Mobile Number',
            'type' => 'varchar',
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'is_user_defined' => 1,
            'system' => false,
            'is_visible' => 1,
            'sort_order' => 125,
        ]);

        $installer->removeAttribute('customer', 'ecc_previous_erpaccount');
        $installer->addAttribute('customer', 'ecc_previous_erpaccount', [
            'label' => 'Previous ERP Account',
            'type' => 'varchar',
            'input' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_prev_supplier_erpaccount');
        $installer->addAttribute('customer', 'ecc_prev_supplier_erpaccount', [
            'label' => 'Previous Supplier ERP Account',
            'type' => 'varchar',
            'input' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_sales_rep_account_id');
        $installer->addAttribute('customer', 'ecc_sales_rep_account_id', [
            'group' => 'General',
            'label' => 'Sales Rep Account Id',
            'type' => 'int',
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'visible' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_sales_rep_id');
        $installer->addAttribute('customer', 'ecc_sales_rep_id', [
            'group' => 'General',
            'label' => 'Sales Rep Id',
            'type' => 'varchar',
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

        $installer->removeAttribute('customer', 'ecc_supplier_erpaccount_id');
        $installer->addAttribute('customer', 'ecc_supplier_erpaccount_id', [
            'group' => 'General',
            'label' => 'Supplier ERP Account',
            'type' => 'int',
            'input' => 'text',
            'default' => '',
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'visible' => false,
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_telephone_number');
        $installer->addAttribute('customer', 'ecc_telephone_number', [
            'group' => 'General',
            'label' => 'Telephone Number',
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

        $installer->removeAttribute('customer', 'ecc_allow_masquerade');
        $installer->addAttribute('customer', 'ecc_allow_masquerade', [
            'label' => 'Allowed to Masquerade as Child Account',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 5,
            //epicor_comm/eav_attribute_data_yesnonulloption
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnonulloption',
            'default' => '2',
            'system' => false
        ]);


        $installer->removeAttribute('customer', 'ecc_allow_masq_cart_clear');
        $installer->addAttribute('customer', 'ecc_allow_masq_cart_clear', [
            'label' => 'Allowed to Clear Cart before on Masquerading as Child Account',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 5,
            //epicor_comm/eav_attribute_data_yesnonulloption
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnonulloption',
            'default' => '2',
            'system' => false
        ]);

        $installer->removeAttribute('customer', 'ecc_allow_masq_cart_reprice');
        $installer->addAttribute('customer', 'ecc_allow_masq_cart_reprice', [
            'label' => '0',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 5,
            //epicor_comm/eav_attribute_data_yesnonulloption
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnonulloption',
            'default' => '2',
            'system' => false
        ]);


        $installer->removeAttribute('customer', 'ecc_contact_code');
        $installer->addAttribute('customer', 'ecc_contact_code', [
            'group' => 'General',
            'label' => 'Contact Code',
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

        $installer->removeAttribute('customer', 'ecc_custom_address_allowed');
        $installer->addAttribute('customer', 'ecc_custom_address_allowed', [
            'label' => 'Custom Address Allowed',
            'type' => 'int',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 5,
            //epicor_comm/eav_attribute_data_yesnonulloption
            'source' => 'Epicor\Comm\Model\Eav\Attribute\Data\Yesnonulloption',
            'system' => false,
            'default' => '2'
        ]);

        $installer->removeAttribute('customer', 'ecc_contracts_filter');
        $installer->addAttribute('customer', 'ecc_contracts_filter', [
            'group' => 'General',
            'label' => 'Contract Filter',
            'type' => 'varchar',
            'input' => 'text',
            //epicor_common/eav_entity_attribute_frontend_erpcontractfilter
            'frontend' => 'Epicor\Common\Model\Eav\Entity\Attribute\Frontend\Erpcontractfilter',
            'default' => null,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'system' => false
        ]);
    }

}