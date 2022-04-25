<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Framework;

class CurrentPlugin 
{
    /**
     * @var array
     */
    protected $exclude_links;

    /**
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Epicor\Comm\Helper\Data $commHelper
    )
    {
        $this->exclude_links = explode(',', $scopeConfig->getValue('customer/account_menu_options/menu_custom_disallowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $this->commHelper = $commHelper;
    }
    

    public function aftertoHtml(
        \Magento\Framework\View\Element\Html\Link\Current $subject,
        $result
    ) 
    {
        $eccHidePrices = $this->commHelper->getEccHidePrice();
        if (($eccHidePrices == 1  || $eccHidePrices == 2) && !in_array('Add Product To Cart By CSV', $this->exclude_links)){
                $this->exclude_links = array_merge(explode(',', 'Add Product To Cart By CSV'),$this->exclude_links
            );
        }
        $name = $subject->getLabel();
        if (in_array($name, $this->exclude_links)) {
            $result = "";
        }
        return $result;
    }
}
