<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Punchout\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Class UpdateConnections
 */
class UpdateConnections implements SchemaPatchInterface, PatchVersionInterface
{

    /**
     * Schema setup interface.
     *
     * @var SchemaSetupInterface
     */
    private $schemaSetup;


    /**
     * UpdateConnections constructor.
     *
     * @param SchemaSetupInterface $schemaSetup Schema setup interface.
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

        $this->schemaSetup->getConnection()->addForeignKey(
            'ECC_PUNCHOUT_CONNECTIONS_IDENTITY_ECC_ERP_ACCOUNT_ERP_CODE',
            'ecc_punchout_connections',
            'identity',
            'ecc_erp_account',
            'erp_code'
        );

        $this->schemaSetup->endSetup();

    }//end apply()


    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];

    }//end getDependencies()


    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';

    }//end getVersion()


    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];

    }//end getAliases()


}//end class
