<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class WeakPasswords
 * @package Epicor\B2b\Setup\Patch\Data
 */
class AddCustomerPasswords implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * AddCustomerPasswords constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Update table with existing customer password hash
     * @return AddCustomerPasswords|void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $customerPassTable = $this->moduleDataSetup->getTable('ecc_customer_passwords');
        $data = $this->getCustomerPasswords();
        if (empty($data) === false) {
            $connection->insertArray($customerPassTable, ['customer_id', 'password_hash'], $data);
        }
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
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * Returns the customers and their password hash
     * @return string[]
     */
    private function getCustomerPasswords()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable("customer_entity");
        return $connection->fetchAll(
            $connection->select()
            ->from($table, ['entity_id', 'password_hash'])
        );
    }

}