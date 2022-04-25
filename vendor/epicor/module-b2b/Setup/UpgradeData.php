<?php


namespace Epicor\B2b\Setup;

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

    /**
     * Configuration paths for B2B email templates/address
     *
     */
    const XML_PATH_REQUEST_EMAIL_TEMPLATE_NEW = 'epicor_b2b/registration/no_acct_admin_email_template';

    const XML_PATH_REQUEST_EMAIL_ADDRESS_NEW = 'epicor_b2b/registration/no_acct_admin_email_address';

    const XML_PATH_GUEST_EMAIL_TEMPLATE_NEW = 'epicor_b2b/registration/guest_acct_admin_email_template';

    const XML_PATH_GUEST_EMAIL_ADDRESS_NEW = 'epicor_b2b/registration/guest_acct_admin_email_address';

    const XML_PATH_REQUEST_EMAIL_ADDRESS_OLD = 'epicor_b2b/registration/reg_email_account';

    const XML_PATH_REQUEST_EMAIL_TEMPLATE_OLD = 'epicor_b2b/registration/reg_email_template';

    /**
     * Constants for old/new Account Actions
     */
    const OLD_PREREG = 'prereg';

    const NEW_PREREG = 'disable_new_erp_acct';

    const OLD_ACCT_REQ = 'email_request';

    const NEW_ACCT_REQ = 'no_acct';

    const OLD_GUEST_ACCT = 'email_cash';

    const NEW_GUEST_ACCT = 'guest_acct';

    const OLD_CNC_ACCT = 'cnc';

    const NEW_CNC_ACCT = 'erp_acct';


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
    )
    {
        $setup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->version1_1_0($setup);
        }
        $setup->endSetup();
    }


    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version1_1_0($setup)
    {
        $this->updateB2bConfigValues($setup);
    }

    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     *
     * To retain old B2b defaults after layout upgrade
     * https://github.com/magento/magento2/issues/9038
     */
    private function updateB2bConfigValues($setup)
    {
        $configTypes = $this->getConfigTypes();
        $writeConnection = $setup->getConnection('core_write');
        $continue = true;
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
        foreach ($configTypes as $type => $value) {

            $newVal = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'' . $type . '\'');
            $oldVal = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'' . $value . '\'');

            $newInfo = $newVal->fetch();
            $oldInfo = $oldVal->fetch();

            if ($newInfo !== false) {
                continue;
            }

            if ($oldInfo) {
                $data = [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => $type,
                    'value' => $oldInfo['value'],
                ];

                $writeConnection->insertOnDuplicate(
                    $setup->getTable('core_config_data'),
                    $data,
                    ['value']
                );
            }
        }

        //New B2b Action defaults
        $newAction = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_b2b/registration/b2b_acct_type\'');
        $oldAction = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_b2b/registration/reg_options\'');

        $newInfo = $newAction->fetch();
        $oldInfo = $oldAction->fetch();

        if ($newInfo !== false) {
            $continue = false;
        }
        if ($continue) {
            $actionMappings = $this->getActionMappings();
            if (($oldInfo) && (isset($actionMappings[$oldInfo['value']]))) {
                $actionval = $actionMappings[$oldInfo['value']];

                $data = [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'epicor_b2b/registration/b2b_acct_type',
                    'value' => $actionval,
                ];

                $writeConnection->insertOnDuplicate(
                    $setup->getTable('core_config_data'),
                    $data,
                    ['value']
                );
            }
        }

        //Default for Allowed Account Types
        $continue = true;
        $newAcctTypes = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_b2b/registration/allowed_cus_types\'');

        $newInfo = $newAcctTypes->fetch();
        if ($newInfo !== false) {
            $continue = false;
        }
        if ($continue) {

            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'epicor_b2b/registration/allowed_cus_types',
                'value' => "B2B Standard Account|B",
            ];

            $writeConnection->insertOnDuplicate(
                $setup->getTable('core_config_data'),
                $data,
                ['value']
            );
        }

        //New Pre-Reg action default
        $newAction = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_b2b/registration/pre_reg_pswd\'');
        $oldPrimary = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_b2b/registration/reg_options\'');
        $oldSecondary = $writeConnection->query('SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0 AND path = \'epicor_b2b/registration/prereg_active\'');

        $newInfo = $newAction->fetch();

        if ($newInfo !== false) {
            return;
        }

        $oldPriVal = $oldPrimary->fetch();
        $oldSecVal = $oldSecondary->fetch();

        if ($oldPriVal && $oldSecVal) {
            $data = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'epicor_b2b/registration/pre_reg_pswd',
                'value' => ($oldPriVal['value'] == 'prereg' || $oldSecVal['value'] == '1') ? '1' : '0',
            ];

            $writeConnection->insertOnDuplicate(
                $setup->getTable('core_config_data'),
                $data,
                ['value']
            );
        }

    }

    /**
     * @return array
     */
    private function getConfigTypes()
    {
        $types = [
            self::XML_PATH_REQUEST_EMAIL_TEMPLATE_NEW => self::XML_PATH_REQUEST_EMAIL_TEMPLATE_OLD,
            self::XML_PATH_REQUEST_EMAIL_ADDRESS_NEW => self::XML_PATH_REQUEST_EMAIL_ADDRESS_OLD,
            self::XML_PATH_GUEST_EMAIL_TEMPLATE_NEW => self::XML_PATH_REQUEST_EMAIL_TEMPLATE_OLD,
            self::XML_PATH_GUEST_EMAIL_ADDRESS_NEW => self::XML_PATH_REQUEST_EMAIL_ADDRESS_OLD,
        ];
        return $types;
    }

    /**
     * @return array
     */
    private function getActionMappings()
    {
        $actionMappings = [
            self::OLD_PREREG => self::NEW_PREREG,
            self::OLD_ACCT_REQ => self::NEW_ACCT_REQ,
            self::OLD_GUEST_ACCT => self::NEW_GUEST_ACCT,
            self::OLD_CNC_ACCT => self::NEW_CNC_ACCT,
        ];
        return $actionMappings;

    }
}
