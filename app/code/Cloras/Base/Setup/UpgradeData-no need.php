<?php

namespace Cloras\Base\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\User\Model\UserFactory;
use Magento\Eav\Model\Config;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
    private $customerSetupFactory;
    private $attributeSetFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        UserFactory $userFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->userFactory = $userFactory;
    }

    /**
     * Upgrades DB for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2', '<')) {

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
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->createAdminUser();
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {

            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $setup->startSetup();
            $attributesInfo = [];

            $attributesInfo['cloras_sf_customer_id']=[
                'label'        => 'SF Customer ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true
            ];
            $attributesInfo['cloras_sf_contact_id']=[
                'label'        => 'SF Contact ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true
            ];

            $customerEntity = $customerSetup->getEavConfig()->getEntityType(\Magento\Customer\Model\Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
    
            /*
             * @var AttributeSet
             */
            $attributeSet     = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            foreach ($attributesInfo as $attributeCode => $attributeParams) {
                $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode, $attributeParams);
                $attribute = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);
                $attribute->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => ['adminhtml_customer'],
                    ]
                );
                $this->attributeSave($attribute);

                $setup->endSetup();
            }
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

    private function attributeSave($attribute)
    {
        $attribute->save();
    }
}
