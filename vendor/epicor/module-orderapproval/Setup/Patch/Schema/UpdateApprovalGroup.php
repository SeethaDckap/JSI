<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\OrderApproval\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Class UpdateApprovalGroup
 *
 * @package Epicor\OrderApproval\Setup\Patch
 */
class UpdateApprovalGroup implements SchemaPatchInterface, PatchVersionInterface
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
            "ECC_ERP_ACCOUNT_ENTITY_ID_ECC_ERP_ACCOUNT_ENTITY_ID",
            "ecc_approval_group_erp_account",
            "erp_account_id",
            "ecc_erp_account",
            "entity_id"

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
        return [];
    }
}
