<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Plugin\Checkout;

/**
 * Description of SidebarPlugin: Disable go to Checkout button if Contract is not selected
 */
class SidebarPlugin 
{
    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;
        
     public function __construct(
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
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
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper \Epicor\Lists\Helper\Frontend\Contract */
        
        if ($helper->contractsEnabled() && $helper->stopCheckout()) {
            $output['showCheckout'] =  false;
        }
        return $output;
    }
    
}
