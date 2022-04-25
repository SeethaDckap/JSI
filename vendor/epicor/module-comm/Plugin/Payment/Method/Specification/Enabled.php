<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Payment\Method\Specification;

class Enabled {
     /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commHelper;
    
    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    protected $commCustomerErpaccountFactory;
        
     public function __construct(
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
    ) {
        $this->commHelper = $commHelper;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
    }
    
    /**
     * Include/Exclude payment method based on ERP setting
     *
     * @return bool
     */
    public function aroundIsSatisfiedBy(
        \Magento\Multishipping\Model\Payment\Method\Specification\Enabled $subject,
        \Closure $proceed,
        $paymentMethod
    )
    {
        $result = $proceed($paymentMethod);
        $erpAccountId = $this->commHelper->create()->getErpAccountId();
        if ($erpAccountId) {
            $erpGroup = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
            $getAccountType = $erpGroup->getAccountType();
            $allowedTypes = array("B2B", "B2C", "Dealer");
            if (in_array($getAccountType, $allowedTypes)) {
                if (!(is_null($erpGroup->getAllowedPaymentMethods()) &&
                    is_null($erpGroup->getAllowedPaymentMethodsExclude()))) {
                    $exclude = !is_null($erpGroup->getAllowedPaymentMethods()) ? 'N' : 'Y';
                    $validPaymentMethods = unserialize($erpGroup->getAllowedPaymentMethods());
                    $invalidPaymentMethods = unserialize($erpGroup->getAllowedPaymentMethodsExclude());
                    if ($exclude == 'N') {
                        if (!in_array($paymentMethod, $validPaymentMethods)) {
                            $result = false;
                        }
                    } else {
                        if (in_array($paymentMethod, $invalidPaymentMethods)) {
                            $result = false;
                        }
                    }
                }
            }
        }
        return $result;
    }
    
}
