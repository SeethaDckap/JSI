<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Template;


/**
 * 
 * Template link override block
 * 
 *  - adds access check to link display
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team 
 */
class Links extends \Magento\Framework\View\Element\Html\Links
{

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->scopeConfig = $scopeConfig;
    }
    public function setLinks($links)
    {
        $this->_links = $links;
    }

    protected function _beforeToHtml()
    {
        $helper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */
        $siteOfflineDontDisplayCheckout = (!$this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/failed_msg_online', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) &&
            $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/site_offline_checkout_disabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ? true : false;
        foreach ($this->_links as $x => $link) {
            if ($link['title'] == 'Checkout' && $siteOfflineDontDisplayCheckout) {
                unset($this->_links[$x]);
                continue;
            }
            $url = ($link['url']) ?: '';
            if (!empty($url) && !$helper->canAccessUrl($url)) {
                unset($this->_links[$x]);
                continue;
            }
        }
        parent::_beforeToHtml();
    }

}
