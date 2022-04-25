<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;

/**
 * Application Insights Data
 *
 * @category   Epicor
 * @package    Epicor_Telemetry
 * @author     Epicor Websales Team
 *
 */
class ApplicationInsights
{

    /**
     * Config Path Telemetry
     */
    const CONFIG_PATH_TELEMETRY = 'telemetry';

    /**
     * Telemetry path ID's.
     */
    const INSTRUMENTATION_KEY_ARGUMENT = 'instrumentation_key';
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMER_CODE = 'customer_code';
    const CUSTOMER_COUNTRY = 'customer_country';
    const DEPLOYMENT_TYPE = 'deployment_type';

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * ApplicationInsights constructor.
     * @param Writer $writer
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Writer $writer,
        DeploymentConfig $deploymentConfig
    )
    {
        $this->writer = $writer;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param array $configs
     * @throws FileSystemException
     */
    public function setConfigs(array $configs)
    {
        $data = [
            ConfigFilePool::APP_ENV => [
                self::CONFIG_PATH_TELEMETRY => [
                    self::INSTRUMENTATION_KEY_ARGUMENT => $configs[self::INSTRUMENTATION_KEY_ARGUMENT],
                    self::CUSTOMER_NAME => $configs[self::CUSTOMER_NAME],
                    self::CUSTOMER_CODE => $configs[self::CUSTOMER_CODE],
                    self::CUSTOMER_COUNTRY => $configs[self::CUSTOMER_COUNTRY],
                    self::DEPLOYMENT_TYPE => $configs[self::DEPLOYMENT_TYPE]
                ]
            ]
        ];
        $this->writer->saveConfig($data);
    }

    /**
     * Get the Application Insights Instrumentation Key.
     *
     * @return array|mixed|string|null
     */
    public function getInstrumentationKey()
    {
        $configPath = self::CONFIG_PATH_TELEMETRY . "/" . self::INSTRUMENTATION_KEY_ARGUMENT;
        return $this->deploymentConfig->get($configPath);
    }

    /**
     * Get the customer name.
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        $configPath = self::CONFIG_PATH_TELEMETRY . "/" . self::CUSTOMER_NAME;
        return $this->deploymentConfig->get($configPath);
    }

    /**
     * Get the customer code.
     *
     * @return string|null
     */
    public function getCustomerCode()
    {
        $configPath = self::CONFIG_PATH_TELEMETRY . "/" . self::CUSTOMER_CODE;
        return $this->deploymentConfig->get($configPath);
    }

    /**
     * Get the customer country.
     *
     * @return string|null
     */
    public function getCustomerCountry()
    {
        $configPath = self::CONFIG_PATH_TELEMETRY . "/" . self::CUSTOMER_COUNTRY;
        return $this->deploymentConfig->get($configPath);
    }

    /**
     * Get the deployment type.
     *
     * @return string|null
     */
    public function getDeploymentType()
    {
        $configPath = self::CONFIG_PATH_TELEMETRY . "/" . self::DEPLOYMENT_TYPE;
        return $this->deploymentConfig->get($configPath);
    }
}
