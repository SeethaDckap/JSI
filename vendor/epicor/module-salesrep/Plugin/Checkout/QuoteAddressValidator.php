<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Checkout;


class QuoteAddressValidator
{

    
    /**
     * Address factory.
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Customer repository.
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;



    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function aroundValidate(
        \Magento\Quote\Model\QuoteAddressValidator $subject,
            \Closure $proceed,
            \Magento\Quote\Api\Data\AddressInterface $addressData
    ) {
        $customerModel = $this->customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
      //validate customer id
        if ($addressData->getCustomerId()) {
            $customer = $this->customerRepository->getById($addressData->getCustomerId());
            if (!$customer->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer id %1', $addressData->getCustomerId())
                );
            }
        }


        $multicuco=true;
        $erpaccountData = $customerModel->getErpAcctCounts();
        if ($erpaccountData && is_array($erpaccountData) && count($erpaccountData) > 1) {
            $multicuco=true;
        }
        if ($customerModel->isSalesRep() || $multicuco) {
            return true;
        }
        $result = $proceed($addressData);
        return $result;
    }


    public function aroundValidateForCart(
        \Magento\Quote\Model\QuoteAddressValidator $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $cart,
        \Magento\Quote\Api\Data\AddressInterface $addressData
    ) {
        $customerModel = $this->customerSession->getCustomer();
        $multicuco=true;
        $erpaccountData = $customerModel->getErpAcctCounts();
        if ($erpaccountData && is_array($erpaccountData) && count($erpaccountData) > 1) {
            $multicuco=true;
        }
        if ($customerModel->isSalesRep() || $multicuco) {
            return true;
        }
        $result = $proceed($cart,$addressData);
        return $result;
    }


}