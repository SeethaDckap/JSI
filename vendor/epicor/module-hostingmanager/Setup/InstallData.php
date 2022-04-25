<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\HostingManager\Setup;


use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

    /**
     * @var \Epicor\HostingManager\Model\SiteFactory
     */
    protected $hostingManagerSiteFactory;        
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;      

    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\HostingManager\Model\SiteFactory $hostingManagerSiteFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->hostingManagerSiteFactory = $hostingManagerSiteFactory;
        try{ 
            $state->setAreaCode('frontend'); 
        }catch (\Magento\Framework\Exception\LocalizedException $e)
        {   /* DO NOTHING, THE SARE CODE IS ALREADY SET */
        }  
    }
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->version_1_0_0();
        }
    }

    protected function version_1_0_0()
    {
        $site = $this->hostingManagerSiteFactory->create();
        /* @var $sites Epicor_HostingManager_Model_Resource_Site_Collection */
        $site->load(true,'is_default');
        if (!$site->getId()) {
            $siteUrl = $this->scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,0);
            $url = parse_url($siteUrl, PHP_URL_HOST);
            $site->setName('Default Website');
            $site->setUrl($url);
            $site->setIsWebsite(false);
            $site->setCode('');
            $site->setChildId(0);
            $site->setIsDefault(true);
            $site->save();            
        }
    }

}