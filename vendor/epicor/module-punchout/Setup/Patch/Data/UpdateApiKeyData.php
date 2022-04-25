<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Punchout\Setup\Patch\Data;

use Epicor\Punchout\Model\Config as PunchoutConfig;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;

/**
 * Class UpdateApiKeyData
 *
 * @package Epicor\Punchout\Setup\Patch\Schema
 */
class UpdateApiKeyData implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;


    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;


    /**
     * @var \Epicor\Punchout\Model\Config
     */
    private $punchoutConfig;


    /**
     * InitializeReportEntityTypesAndPages constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup ModuleDataSetup.
     * @param ResourceConfig           $resourceConfig  ResourceConfig.
     * @param PunchoutConfig           $config          Config.
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConfig $resourceConfig,
        PunchoutConfig $config
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConfig  = $resourceConfig;
        $this->punchoutConfig  = $config;

    }//end __construct()


    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $apiKey = $this->punchoutConfig->getApiKey();
        if ($apiKey === '') {
            $secretKey = $this->punchoutConfig->generateSecretKey();
            $this->resourceConfig->saveConfig(
                'epicor_punchout/setup_request/api_key',
                $secretKey,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();

    }

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
        return '2.0.3';

    }//end getVersion()


    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];

    }//end getAliases()


}//end class
