<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Cre\Plugin;
use Magento\Framework\View\Asset\Minification;

class ExcludeFilesFromMinification
{
    protected $scopeConfig;


    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig      = $scopeConfig;
    }

    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $creActive = $this->scopeConfig->getValue('payment/cre/active',$storeScope);
        if($creActive && $this->includeCreScripts()) {
            $result[] = $this->includeCreScripts();
        }
        return $result;
    }

    public function includeCreScripts()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $liveMode = $this->scopeConfig->getValue('payment/cre/live_mode', $storeScope);
        $testUrl = $this->scopeConfig->getValue('payment/cre/test_url', $storeScope);
        $liveUrl = $this->scopeConfig->getValue('payment/cre/live_url', $storeScope);
        $includeJs = ($liveMode) ? $liveUrl : $testUrl;
        $result ='';
        if ($includeJs) {
            $result = $includeJs;
        }
        return $result;
    }

}