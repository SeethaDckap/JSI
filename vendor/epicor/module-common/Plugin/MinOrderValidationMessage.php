<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin;

use Epicor\Comm\Helper\Data as CommHelper;
use Epicor\Comm\Model\MinOrderAmountFlag;
use Epicor\Dealerconnect\Model\Customer;
use Epicor\Dealerconnect\Model\Customer\Erpaccount;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MinOrderValidationMessage
{
    private $comDataHelper;
    private $pricingHelper;
    private $minOrderAmountFlag;
    private $scopeConfig;

    public function __construct(
        MinOrderAmountFlag $minOrderAmountFlag,
        ScopeConfigInterface $scopeConfig,
        CommHelper $data,
        PricingHelper $pricingHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->comDataHelper = $data;
        $this->pricingHelper = $pricingHelper;
        $this->minOrderAmountFlag = $minOrderAmountFlag;
    }

    public function afterGetMessage($subject, $result)
    {
        $eccMinOrderValue = $this->getFormattedEccMinOrderValue();
        $customMinOrderDescription = $this->getCustomMinOrderDescription();

        if (!$this->getCustomerErpAccount() || !$eccMinOrderValue) {
            return $result;
        }

        if ($eccMinOrderValue && !$customMinOrderDescription) {
            $result = $message = __('Minimum order amount is %1', $eccMinOrderValue);
        } else {
            $result = __($customMinOrderDescription);
        }

        return $result;
    }

    private function getCustomMinOrderDescription()
    {
        return  $this->scopeConfig->getValue(
            'sales/minimum_order/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    private function getFormattedEccMinOrderValue()
    {
        $erpAccountMinOrderValue = (float) $this->getErpAccountMinOrderValue();
        if ($erpAccountMinOrderValue > 0) {
            return $this->pricingHelper->currency($erpAccountMinOrderValue, true, false);
        }
    }

    public function getErpAccountMinOrderValue()
    {
        $customerErpAccount = $this->getCustomerErpAccount();
        if ($customerErpAccount instanceof Erpaccount) {
            $accountId = $customerErpAccount->getId();

            $erpAccountMinOrderValue = $this->comDataHelper->getMinimumOrderAmount($accountId);
            return $erpAccountMinOrderValue;
        }
    }

    private function getCustomer()
    {
        return $this->comDataHelper->getCustomer();
    }

    private function getCustomerErpAccount()
    {
        $customer = $this->getCustomer();
        if ($customer instanceof Customer) {
            return $customer->getCustomerErpAccount();
        }
    }
}
