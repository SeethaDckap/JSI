<?php

namespace Cloras\Base\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Cloras\Base\Api\IntegrationInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\User\Model\UserFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    const ERP_CUSTOMER_ID = 'cloras_erp_customer_id';

    const ERP_CONTACT_ID = 'cloras_erp_contact_id';

    const ERP_SHIPTO_ID = 'cloras_erp_shipto_id';


    /**
     * Customer setup factory.
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    private $eavSetupFactory;

    private $eavConfig;

    /**
     * Init.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory  $attributeSetFactory
     * @param IntegrationInterface $integrationInterface
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        IntegrationInterface $integrationInterface,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        UserFactory $userFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->integrationInterface = $integrationInterface;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->userFactory = $userFactory;
    }//end __construct()

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /*
         * @var \Magento\Customer\Setup\CustomerSetup
         */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $setup->startSetup();

        $attributesInfo = [
            self::ERP_CUSTOMER_ID => [
                'label'        => 'ERP Customer ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true,
            ],
            self::ERP_CONTACT_ID  => [
                'label'        => 'ERP Contact ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true,
            ],
            self::ERP_SHIPTO_ID   => [
                'label'        => 'ERP ShipTo ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true,
            ],
            
        ];

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /*
         * @var AttributeSet
         */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, $attributeParams);
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
            $attribute->addData(
                [
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms'      => ['adminhtml_customer'],
                ]
            );
            $this->attributeSave($attribute);
        }

        //create integration
        $this->integrationInterface->createNewIntegration();


        //add existing column to sales order grid
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            'ext_order_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => true,
                'comment' => 'Ext Order Id'
            ]
        );
        // $setup->endSetup();


        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $productCustomAttributes = [];
            
            $productCustomAttributes['uom'] = [
                'label' => 'UOM',
                'type' => 'text'
            ];
            
            $productCustomAttributes['inv_mast_uid'] = [
                'label' => 'Inventory Master Id',
                'type' => 'text'
            ];

            $productCustomAttributes['erp_product_id'] = [
                'label' => 'ERP Product Id',
                'type' => 'text'
            ];

            foreach ($productCustomAttributes as $attributeCode => $attributeInfo) {
                $this->addEAVAttribute(
                    $eavSetup,
                    $attributeCode,
                    $attributeInfo['label'],
                    $attributeInfo['type']
                );
            }
            
            $this->createAttributeGroup($eavSetup, $productCustomAttributes);
         
            $setup->endSetup();

        $this->createAdminUser();
    }
    
    private function attributeSave($attribute)
    {
        $attribute->save();
    }

    private function createAdminUser()
    {
        
        $adminInfo = [
            'username'  => 'clorasadmin',
            'firstname' => 'Cloras',
            'lastname'  => 'Admin',
            'email'     => 'info@cloras.com',
            'password'  => 'cloras@123',
            'interface_locale' => 'en_US',
            'is_active' => 1
        ];

        $userModel = $this->userFactory->create();
        $userModel->setData($adminInfo);
        $userModel->setRoleId(1);
        try {
            $userModel->save();
        } catch (\Exception $ex) {
            $ex->getMessage();
        }
    }

    private function addEAVAttribute($eavSetup, $attributeCode, $label, $inputType)
    {
        $attribute =  $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeCode,
                [
                    'type' => $inputType,
                    'backend' => '',
                    'frontend' => '',
                    'label' => $label,
                    'input' => $inputType,
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => true,
                    'apply_to' => '',
                ]
            );
        }
    }

    private function createAttributeGroup($eavSetup, $productCustomAttributes)
    {
        $groupName = 'ERP Attributes'; /* Label of your group*/
        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product'); /* get entity type id so that attribute are only assigned to catalog_product */
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId); /* Here we have fetched all attribute set as we want attribute group to show under all attribute set.*/
         
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 19);
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            foreach ($productCustomAttributes as $attributeCode => $attributeInfo) {
                // Add existing attribute to group
                $attributeId = $eavSetup->getAttributeId($entityTypeId, $attributeCode);
                $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
            }
        }
    }
}//end class
