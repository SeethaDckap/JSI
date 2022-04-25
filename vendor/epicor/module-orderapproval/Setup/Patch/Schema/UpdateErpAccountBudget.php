<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as DbTable;

class UpdateErpAccountBudget implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * UpdateApprovalGroup constructor.
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        // Updating data of the 'catalog_product_bundle_option_value' table.

        $this->schemaSetup->getConnection()->addForeignKey(
            "ECC_ERP_ACC_BUD_ENT_ID_ECC_ERP_ACC_BUD_ENT_ID",
            "ecc_erp_account_budget",
            "erp_id",
            "ecc_erp_account",
            "entity_id"
        );

        $this->schemaSetup->getConnection()->addIndex(
            "ecc_erp_account_budget",
            "ECC_ERP_ACCOUNT_BUDGET_UNIQUE_INDEX",
            ['erp_id', 'type'],
            DbTable::INDEX_TYPE_UNIQUE
        );

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return self::getDependencies();
    }
}