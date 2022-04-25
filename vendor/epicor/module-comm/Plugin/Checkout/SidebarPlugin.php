<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Checkout;

/**
 * Description of SidebarPlugin: Disable go to Checkout button if Customer OnStop Restriction value is "Cannot access Checkout"
 */
class SidebarPlugin {
      /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;
        
     public function __construct(
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->commHelper = $commHelper;
    }
    
    /**
     * Returns minicart config
     *
     * @return array
     */
    public function afterGetConfig(
        \Magento\Checkout\Block\Cart\Sidebar $subject,
        array  $output
    )
    {
        $output['showCheckout'] = $this->commHelper->isFunctionalityDisabledForCustomer('checkout') ? false : true;
        
        return $output;
    }
    
}
