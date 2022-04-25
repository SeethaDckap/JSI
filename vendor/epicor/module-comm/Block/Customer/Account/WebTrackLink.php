<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Customer\Account;

/**
 * Customer Web Track link
 */
class WebTrackLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    const WBBTRACK_DEFAULT_TITLE = '_WebTrack_';
    
    const WBBTRACK_TITLE_CONFIG_PATH = 'Epicor_Comm/integrations/webtrack_title';
                  
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;             
    
   /**
    * 
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,       
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();               
        parent::__construct($context, $defaultPath, $data);
    }
           
    /**
     * @return string
     */
    public function getLabel()
    {                
        $title = $this->_scopeConfig->getValue(self::WBBTRACK_TITLE_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return !empty($title) ? $title : __(self::WBBTRACK_DEFAULT_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {        
        // for webtrack link, erp should require bistrack
        if($this->_scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != 'bistrack'){
            return '';
        }
               
        return parent::_toHtml();
    }
}
