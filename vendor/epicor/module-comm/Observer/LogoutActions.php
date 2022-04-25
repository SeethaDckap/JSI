<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

class LogoutActions extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Customer Session key.
     *
     * @var array
     */
    private $knownKeys = ['b_2_b_hierarchy_masquerade'];

    /**
     * Customer Logout Before Event.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customerSession->setHasStoreSelected(false);
        $customerSession->setMasqueradeAccountId(false);
        $customerSession->setDisplayLocations(false);

        $helper = $this->commProductHelper->create();
        $helper->clearConfigureList();

        /**
         * Update parent ERP Billing and Shipping address
         * When B2B is masquerade with child account
         * and customer moving for logout
         */
        if ($customerSession->getB2BHierarchyMasquerade()) {
            foreach ($this->knownKeys as $key) {
                $customerSession->unsetData($key);
            }

            $this->updateCustomerParentAddress();
        }
    }

    /**
     * Update Customer Parent Address
     * when customer is moving for logout
     * and B2B
     * when B2B masquerade to child
     *
     * @return void
     */
    private function updateCustomerParentAddress()
    {
        $cart = $this->checkoutCart->create();
        /* @var $cart \Magento\Checkout\Model\Cart */
        $session = $this->checkoutSession->create();
        /* @var $cart \Magento\Checkout\Model\Session */
        $this->updateCartAddresses();
        $this->registry->register('dont_send_bsv', 1);
        $cart->save();
        $session->setCartWasUpdated(true);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateCartAddresses()
    {
        /* @var $commHelper \Epicor\Comm\Helper\Data */
        $commHelper = $this->commHelper->create();

        /* @var $erpAccountInfo \Epicor\Comm\Model\Customer\Erpaccount */
        $erpAccountInfo = $commHelper->getErpAccountInfo();

        /* @var $customerSession \Magento\Customer\Model\Session */
        $customerSession = $this->customerSessionFactory->create();
        $customer = $customerSession->getCustomer();

        $defaultBillingAddressCode
            = $erpAccountInfo->getDefaultInvoiceAddressCode();
        $defaultShippingAddressCode
            = $erpAccountInfo->getDefaultDeliveryAddressCode();

        $quote = $this->checkoutSession->create()->getQuote();

        $billingAddress
            = $erpAccountInfo->getAddress($defaultBillingAddressCode);

        if ($billingAddress) {
            $erpAddress = $billingAddress->toCustomerAddress($customer,
                $erpAccountInfo->getId());
            $quoteBillingAddress = $this->quoteQuoteAddressFactory->create([]);
            $quoteBillingAddress->setData($erpAddress->getData());
            $quote->setBillingAddress($quoteBillingAddress);
        }

        $shippingAddress
            = $erpAccountInfo->getAddress($defaultShippingAddressCode);

        if ($shippingAddress) {
            $erpAddress = $shippingAddress->toCustomerAddress($customer,
                $erpAccountInfo->getId());
            $quoteShippingAddress = $this->quoteQuoteAddressFactory->create([]);
            $quoteShippingAddress->setData($erpAddress->getData());
            $quote->setShippingAddress($quoteShippingAddress);
        }
    }

}