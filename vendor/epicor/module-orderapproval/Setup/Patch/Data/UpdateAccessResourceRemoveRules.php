<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Epicor\AccessRight\Setup\UpgradeRoles;

class UpdateAccessResourceRemoveRules implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var UpgradeRoles
     */
    private $upgradeRoles;

    /**
     * UpdateAccessResourceRolesWithBudgets constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param UpgradeRoles $upgradeRoles
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        UpgradeRoles $upgradeRoles
    ) {

        $this->moduleDataSetup = $moduleDataSetup;
        $this->upgradeRoles = $upgradeRoles;
    }

    /**
     * @return UpdateAccessResourceRolesWithBudgets|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $dataRows = [
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_budgets'],
            ['resource_id' => 'Epicor_Customer::my_account_information_budgets']
        ];

        $this->upgradeRoles->removeRoleRules($dataRows, $this->moduleDataSetup);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [UpdateAccessResourceRolesWithBudgets::class];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '2.0.2';
    }


}