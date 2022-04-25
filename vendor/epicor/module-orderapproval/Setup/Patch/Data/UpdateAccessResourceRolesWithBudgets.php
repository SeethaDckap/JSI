<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Epicor\AccessRight\Setup\UpgradeRoles;
use Magento\Framework\App\State;

class UpdateAccessResourceRolesWithBudgets implements DataPatchInterface, PatchVersionInterface
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
     * @var State
     */
    private $state;

    /**
     * UpdateAccessResourceRolesWithBudgets constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param UpgradeRoles $upgradeRoles
     * @param State $state
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        UpgradeRoles $upgradeRoles,
        State $state
    ) {

        $this->moduleDataSetup = $moduleDataSetup;
        $this->upgradeRoles = $upgradeRoles;
        try{
            $state->setAreaCode('frontend');
        }catch (LocalizedException $e)
        {   /* DO NOTHING, THE AREA CODE IS ALREADY SET */
        }
    }

    /**
     * @return UpdateAccessResourceRolesWithBudgets|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $dataRows = [
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_budgets'],
            ['resource_id' => 'Epicor_Customerconnect::customerconnect_budgets_read'],
            ['resource_id' => 'Epicor_Customer::my_account_information_budgets'],
            ['resource_id' => 'Epicor_Customer::my_account_information_budgets_read'],
        ];

        $this->upgradeRoles->updateNewRoleResources($dataRows, $this->moduleDataSetup);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
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
        return '2.0.1';
    }


}