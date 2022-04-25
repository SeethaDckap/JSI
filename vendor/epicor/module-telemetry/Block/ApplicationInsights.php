<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Telemetry\Block;

use Epicor\Telemetry\Service\Configuration;
use Epicor\ReleaseNotification\Service\Configuration as ReleaseConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Template\Context;
use Epicor\Telemetry\Model\ApplicationInsights as ApplicationInsightsModel;

/**
 * Application Insights Javascript Snippet in every page.
 * 
 * @category   Epicor
 * @package    Epicor_Telemetry
 * @author     Epicor Websales Team
 */

class ApplicationInsights  extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ApplicationInsightsModel
     */
    private $applicationInsights;

    /**
     * @var ReleaseConfig
     */
    private $eccProductMetadata;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * ApplicationInsights constructor.
     *
     * @param Context $context
     * @param ApplicationInsightsModel $applicationInsights
     * @param ReleaseConfig $eccProductMetadata
     * @param ProductMetadataInterface $productMetadata
     * @param CacheInterface $cache
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Configuration $config
     * @param array $data
     */
    public function __construct(
         Context $context,
         ApplicationInsightsModel $applicationInsights,
         ReleaseConfig $eccProductMetadata,
         ProductMetadataInterface $productMetadata,
         CacheInterface $cache,
         Session $customerSession,
         ScopeConfigInterface $scopeConfig,
         Configuration $config,
         array $data = []
     )
    {
        $this->applicationInsights = $applicationInsights;
        parent::__construct(
            $context,
            $data
        );
        $this->eccProductMetadata = $eccProductMetadata;
        $this->productMetadata = $productMetadata;
        $this->cache = $cache;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Is Application Insights enabled.
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * Application Insights Instrumentation Key.
     *
     * @return string|null
     */
    public function getInstrumentationKey()
    {
        return $this->applicationInsights->getInstrumentationKey();
    }

    /**
     * Get customer name.
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->applicationInsights->getCustomerName();
    }

    /**
     * Get customer code.
     *
     * @return string|null
     */
    public function getCustomerCode()
    {
        return $this->applicationInsights->getCustomerCode();
    }

    /**
     * Get customer country.
     *
     * @return string|null
     */
    public function getCustomerCountry()
    {
        return $this->applicationInsights->getCustomerCountry();
    }

    /**
     * Get deployment type.
     *
     * @return string|null
     */
    public function getDeploymentType()
    {
        $type = $this->applicationInsights->getDeploymentType();

        if ($type == '1') {
            return 'Epicor SaaS';
        }

        return 'On Prem';
    }

    /**
     * Get ECC version.
     *
     * @return string
     */
    public function getEccVersion()
    {
        $cached = $this->cache->load('telemetry_ecc_version');
        if (empty($cached)) {
            $cached = $this->eccProductMetadata->getEccVersion();
            $this->cache->save($cached, 'telemetry_ecc_version');
        }

        return $cached;
    }

    /**
     * Get Magento version.
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        $cached = $this->cache->load('telemetry_magento_version');
        if (empty($cached)) {
            $cached = $this->productMetadata->getVersion();
            $this->cache->save($cached, 'telemetry_magento_version');
        }

        return $cached;
    }

    /**
     * Is customer logged in?
     *
     * @return string
     */
    public function isLoggedIn()
    {
        if ($this->customerSession->isLoggedIn()) {
            return 'yes';
        }

        return 'no';
    }

    /**
     * Get ERP type.
     *
     * @return mixed|string
     */
    public function getErp()
    {
        $cached = $this->cache->load('telemetry_erp_type');
        if (empty($cached)) {
            $cached = $this->scopeConfig->getValue(
                'Epicor_Comm/licensing/erp',
                'store'
            );
            $this->cache->save($cached, 'telemetry_erp_type');
        }

        return $cached;
    }

}
