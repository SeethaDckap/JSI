<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class AddUniqueConstraintAccSku
 * @package Epicor\Database\Setup\Patch\Schema
 */
class AddUniqueConstraintAccSku implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * AddUniqueConstraintAccSku constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->addIndex(
            $this->moduleDataSetup->getTable('ecc_erp_account_sku'),
            'PRODUCT_ID_CUSTOMER_GROUP_ID_SKU',
            array('product_id', 'customer_group_id', 'sku'),
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
