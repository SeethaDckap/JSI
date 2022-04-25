<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Punchout\Setup\Patch\Data;

use Epicor\Common\Helper\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class UpdateConnections
 */
class UpdateAccessElement implements DataPatchInterface, PatchVersionInterface
{

    /**
     * Schema setup interface.
     *
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * Common setup helper.
     *
     * @var Setup
     */
    private $commonHelperSetup;


    /**
     * UpdateConnections constructor.
     *
     * @param SchemaSetupInterface $schemaSetup       Schema setup interface.
     * @param Setup                $commonHelperSetup Common helper setup.
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        Setup $commonHelperSetup
    ) {
        $this->schemaSetup       = $schemaSetup;
        $this->commonHelperSetup = $commonHelperSetup;

    }//end __construct()


    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $helper = $this->commonHelperSetup;
        $helper->addAccessElement('Epicor_Punchout', 'SetupRequest', 'index', '', 'Access', 1, 1);
        $helper->addAccessElement('Epicor_Punchout', 'SetupRequest', 'sessionstart', '', 'Access', 1, 1);
        $helper->addAccessElement('Epicor_Punchout', 'PurchaseOrder', 'index', '', 'Access', 1, 1);

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
        return '2.0.2';

    }//end getVersion()


    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];

    }//end getAliases()


}//end class
