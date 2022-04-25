<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block;

/**
 * Site Monitoring block used for adding Montoring site Script in every page.
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class SiteMonitoring  extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        
        parent::__construct(
            $context,
            $data
        );
    }
    
    public function getMonitoringCodeSnippet()
    { 
        $html = $this->scopeConfig->getValue('Epicor_Comm/site_monitoring/code_snippet');
        return ($html) ? $html : '';
    }
    

}
