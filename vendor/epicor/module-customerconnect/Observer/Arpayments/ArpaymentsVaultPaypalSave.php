<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class ArpaymentsVaultPaypalSave extends AbstractDataAssignObserver
{
    
        const PAYMENT_METHOD_NONCE = 'payment_method_nonce';
        const DEVICE_DATA = 'device_data';
        const PAYMENT_DATA = 'public_hash';    
        const PAYMENT_CUSTOMER_ID = 'customer_id';  
        
        
        protected $additionalInformationList = [
            self::PAYMENT_METHOD_NONCE,
            self::DEVICE_DATA,
            self::PAYMENT_DATA,
            self::PAYMENT_CUSTOMER_ID
        ];
        /**
         * @var \Magento\Framework\Registry
         */
        protected $_registry;
        protected $_request;
        protected $arpaymentsHelper;
        protected $_session;

        public function __construct(
           \Magento\Framework\Registry $registry,
           \Magento\Customer\Model\Session $session,     
            \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
            \Magento\Framework\App\Request\Http $request)
        {
            $this->arpaymentsHelper = $arpaymentsHelper;
            $this->_registry = $registry;
            $this->_request = $request;
            $this->_session = $session;
        }


        public function execute(\Magento\Framework\Event\Observer $observer)
        {
               $handle = $this->arpaymentsHelper->checkArpaymentsPage();
               if($handle) {
                    $data = $this->readDataArgument($observer);
                    $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
                    if (!is_array($additionalData)) {
                        return;
                    }
                    $paymentInfo = $this->readPaymentModelArgument($observer);
                    $customerId = $this->_session->getCustomer()->getId();
                    foreach ($this->additionalInformationList as $additionalInformationKey) {
                        if ((isset($additionalData[$additionalInformationKey])) || ($additionalInformationKey=="customer_id")) {
                            $paymentInfo->setAdditionalInformation(
                                $additionalInformationKey,
                                ($additionalInformationKey =="customer_id") ? $customerId : $additionalData[$additionalInformationKey]
                            );
                        }
                    }
               }
        }
}