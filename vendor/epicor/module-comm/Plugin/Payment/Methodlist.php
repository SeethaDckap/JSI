<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Payment;

class Methodlist {
    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    protected $commCustomerErpaccountFactory;

    protected $scopeConfig;

    protected $arpaymentsHelper;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentFactory
     */
    protected $commErpMappingPaymentFactory;

    protected $internalPayments = array('esdm','elements','cre');


    public function __construct(
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPaymentFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
    ) {
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->commErpMappingPaymentFactory = $commErpMappingPaymentFactory;
    }

    /**
     * Include/Exclude payment method based on ERP setting
     *
     * @return array
     */
    public function afterGetAvailableMethods(
        \Magento\Payment\Model\MethodList $subject,
        array  $availableMethods
    )
    {
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
                    foreach ($availableMethods as $key => $method) {
                        if ($exclude == 'N') {
                            if (!in_array($method->getCode(), $validPaymentMethods)) {
                                unset($availableMethods[$key]);
                            }
                        } else {
                            if (in_array($method->getCode(), $invalidPaymentMethods)) {
                                unset($availableMethods[$key]);
                            }
                        }
                    }
                }
            }
            $handle = $this->arpaymentsHelper->checkArpaymentsPage();
            if($handle) {
                $availableMethods = $this->ArpaymentMethods($availableMethods);
            }
        }
        return $availableMethods;
    }

    public function ArpaymentMethods($availableMethods) {
        $methodArpayments = $this->scopeConfig->getValue(
            "customerconnect_enabled_messages/CAAP_request/valid_payment_methods"
            ,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $myMethodArpayments = explode(',', $methodArpayments);
        $internalPayments = $this->internalPayments;
        foreach ($availableMethods as $key => $method) {
            if (!in_array($method->getCode(), $myMethodArpayments)) {
                unset($availableMethods[$key]);
            } else {
                if(in_array($method->getCode(),$internalPayments)) {
                    $allowedMapping = array('C','N','A','D');
                } else {
                    $allowedMapping = array('C','N');
                }
                $model = $this->commErpMappingPaymentFactory->create()->loadMappingByStore($method->getCode(), 'magento_code');
                if(!empty($model->getPaymentCollected())) {
                    if(!in_array($model->getPaymentCollected(), $allowedMapping)) {
                        unset($availableMethods[$key]);
                    }
                } else {
                    unset($availableMethods[$key]);
                }
            }
        }
        return $availableMethods;
    }

}