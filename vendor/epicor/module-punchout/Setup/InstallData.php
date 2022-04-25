<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Themes
 * @subpackage Setup
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Setup;

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\Punchout\Model\Config as PunchoutConfig;

/**
 * Class InstallData
 *
 * @package Epicor\Punchout\Setup
 */
class InstallData implements InstallDataInterface
{

    /**
     *  App state.
     *
     * @var State
     */
    private $state;

    /**
     * ResourceConfig.
     *
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;

    /**
     * Punchout Configuration
     *
     * @var \Epicor\Punchout\Model\Config
     */
    private $punchoutConfig;


    /**
     * UpgradeData constructor
     *
     * @param State          $state          App state.
     * @param ResourceConfig $resourceConfig ResourceConfig.
     * @param PunchoutConfig $config         Punchout configuration.
     */
    public function __construct(
        State $state,
        ResourceConfig $resourceConfig,
        PunchoutConfig $config
    ) {
        $this->state          = $state;
        $this->resourceConfig = $resourceConfig;
        $this->punchoutConfig = $config;

    }//end __construct()


    /**
     * Data installation script.
     *
     * @param ModuleDataSetupInterface $setup   DB data resource interface for a module.
     * @param ModuleContextInterface   $context Context of a module being installed/updated.
     *
     * @return void
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
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

    }//end install()


}//end class
