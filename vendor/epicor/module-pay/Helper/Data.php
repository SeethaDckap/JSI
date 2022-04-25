<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Pay\Helper;


//class Epicor_Pay_Helper_Data extends Epicor_Common_Helper_Data
class Data extends \Epicor\Comm\Helper\Data
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $customerResourceModelAddressCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;


    public function __construct(
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerResourceModelAddressCollectionFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        \Epicor\Comm\Helper\Context $context)
    {
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->customerResourceModelAddressCollectionFactory = $customerResourceModelAddressCollectionFactory;

        parent::__construct($context);
    }


    public function setQuoteDefaultBillingAddress($paymentMethod)
    {
        if ($paymentMethod == 'pay' && $this->scopeConfig->isSetFlag('payment/pay/update_billing_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $commHelper = $this->commHelper;
            /* @var $commHelper \Epicor\Comm\Helper\Data */
            $erpAccountInfo = $commHelper->getErpAccountInfo();
            /* @var $erpAccountInfo \Epicor\Comm\Model\Customer\Erpaccount */

            $defaultInvoiceAddressCode = $erpAccountInfo->getDefaultInvoiceAddressCode();
            $quote = $this->checkoutSession->getQuote();


            $newBillingAddress = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->addFieldToFilter('erp_code', array('eq' => $defaultInvoiceAddressCode))
                ->getFirstItem();
            if (!$newBillingAddress->isObjectNew()) {

                $billingAddress = $this->customerResourceModelAddressCollectionFactory->create()->addAttributeToFilter('ecc_erp_address_code', array('eq' => $defaultInvoiceAddressCode))
                    ->addAttributeToSelect('*')
                    ->getFirstItem()
                    ->getData();
                $quote->setBillingAddress($this->quoteQuoteAddressFactory->create($billingAddress));
                $this->checkoutSession->setData('ForcedBillingAddressChange', true);
                $quote->save();
            }
        }
    }

    /**
     * Get credit check message
     *
     * @return boolean|string
     */
    public function getCreditCheckMessage()
    {
        $canShowCreditCheckMsg = $this->scopeConfig->isSetFlag('payment/pay/disp_credit_check_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$canShowCreditCheckMsg) {
            return false;
        }
        return 'Epicor Payment Method:  ' . $this->scopeConfig->getValue('payment/pay/credit_check_msg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
