<?php


namespace Epicor\Customerconnect\Setup;

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

        if (version_compare($context->getVersion(), '1.0.7.7', '<')) {
            $this->version_1_0_7_7($customerSetup);
        }
        if (version_compare($context->getVersion(), '1.0.7.9', '<')) {
            $this->version_1_0_7_9($setup);
        }
        if (version_compare($context->getVersion(), '1.0.8.0', '<')) {
            $this->version_1_0_8_0($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param \Magento\Customer\Setup\CustomerSetup $customerSetup
     */
    private function version_1_0_7_7($customerSetup)
    {
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'ecc_misc_view_type', [
            'group' => 'General',
            'label' => 'View Miscellaneous Charges',
            'type' => 'varchar',
            'input' => 'select',
            'visible' => false,
            'required' => false,
            'user_defined' => 1,
            'sort_order' => 7,
            'source' => 'Epicor\Customerconnect\Model\Eav\Attribute\Data\MiscViewType',
            'default' => '2',
            'system' => false
        ]);

        $attributes = [
            'ecc_misc_view_type'
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }

    }

    private function version_1_0_7_9($setup)
    {
        $columnConfig = unserialize($this->scopeConfig->getValue('customerconnect_enabled_messages/CUOD_request/grid_informationconfig'));
        $taxExemptRef = array(
            "header" => "Tax Exempt Reference",
            "index" => "tax_exempt_reference",
        );
        array_push($columnConfig, $taxExemptRef);
        $value = serialize($columnConfig);
        if (empty($value)) {
            $value = 'a:11:{s:17:"_1565155358027_27";a:2:{s:6:"header";s:10:"Order Date";s:5:"index";s:10:"order_date";}s:18:"_1565155375195_195";a:2:{s:6:"header";s:7:"Need By";s:5:"index";s:13:"required_date";}s:18:"_1565155390410_410";a:2:{s:6:"header";s:5:"Terms";s:5:"index";s:13:"payment_terms";}s:18:"_1565155398500_500";a:2:{s:6:"header";s:9:"PO Number";s:5:"index";s:18:"customer_reference";}s:18:"_1565155417596_596";a:2:{s:6:"header";s:12:"Sales Person";s:5:"index";s:15:"salesRep > name";}s:18:"_1565155428720_720";a:2:{s:6:"header";s:8:"Ship Via";s:5:"index";s:15:"delivery_method";}s:18:"_1565155432730_730";a:2:{s:6:"header";s:3:"FOB";s:5:"index";s:3:"fob";}s:18:"_1565155446540_540";a:2:{s:6:"header";s:6:"Tax Id";s:5:"index";s:5:"taxid";}s:18:"_1565166422895_895";a:2:{s:6:"header";s:8:"Contract";s:5:"index";s:13:"contract_code";}s:18:"_1565166443933_933";a:2:{s:6:"header";s:20:"Additional Reference";s:5:"index";s:20:"additional_reference";}s:18:"_1565166445537_537";a:2:{s:6:"header";s:11:"Ship Status";s:5:"index";s:11:"ship_status";}}';
        }
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'customerconnect_enabled_messages/CUOD_request/grid_informationconfig\'');

        $erpInfo = $var->fetch();
        if ($erpInfo == false) {
            return;
        }
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'customerconnect_enabled_messages/CUOD_request/grid_informationconfig',
            'value' => $value,
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );
    }

    private function version_1_0_8_0($setup)
    {
        $columnConfig = unserialize($this->scopeConfig->getValue('customerconnect_enabled_messages/CUID_request/grid_informationconfig'));
        $taxExemptRef = array(
            "header" => "Tax Exempt Reference",
            "index" => "tax_exempt_reference",
        );
        array_push($columnConfig, $taxExemptRef);
        $value = serialize($columnConfig);
        if (empty($value)) {
            $value = '';
        }
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $var = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'customerconnect_enabled_messages/CUID_request/grid_informationconfig\'');

        $erpInfo = $var->fetch();
        if ($erpInfo == false) {
            return;
        }
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'customerconnect_enabled_messages/CUID_request/grid_informationconfig',
            'value' => $value,
        ];

        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );
    }


}
