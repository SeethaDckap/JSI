<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Checkout;


class OnePageSuccessRegistration
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    public function afterToHtml(
        \Magento\Checkout\Block\Registration $subject,
        $result
    )
    {
        $IsHomeCustomerRegistrationEnabled = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$IsHomeCustomerRegistrationEnabled) {
            $result = "";
        }
        return $result;
    }

}
