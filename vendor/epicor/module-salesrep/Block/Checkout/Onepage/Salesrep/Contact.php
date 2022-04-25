<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Checkout\Onepage\Salesrep;


class Contact extends \Magento\Checkout\Block\Onepage
{

    /**
     * @var \Epicor\SalesRep\Helper\Checkout
     */
    protected $salesRepCheckoutHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Epicor\SalesRep\Helper\Checkout $salesRepCheckoutHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->salesRepCheckoutHelper = $salesRepCheckoutHelper;
        $this->customerSession = $customerSession;
    }
    protected function _construct()
    {
        $this->getCheckout()->setStepData('salesrep_contact', array(
            'label' => __('Choose Recipient ERP Account Contact'),
            'is_show' => $this->isShow(),
            'allow' => 'allow'
        ));

        if ($this->isShow()) {
            $billing = $this->getCheckout()->getStepData('billing');

            if (isset($billing['allow'])) {
                unset($billing['allow']);
            }

            $this->getCheckout()->setStepData('billing', $billing);
        }

        parent::_construct();
    }

    public function isShow()
    {
        $helper = $this->salesRepCheckoutHelper;
        /* @var $helper Epicor_Salesrep_Helper_Checkout */

        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        return $helper->isChooseContactEnabled() && $customer->isSalesRep() && $this->getContacts();
    }

    public function isRequired()
    {
        $helper = $this->salesRepCheckoutHelper;
        /* @var $helper Epicor_Salesrep_Helper_Checkout */

        return $helper->isChooseContactRequired();
    }

    public function getContacts()
    {
        $helper = $this->salesRepCheckoutHelper;
        /* @var $helper Epicor_Salesrep_Helper_Checkout */

        return $helper->getSalesErpContacts();
    }

    public function getCurrentContact()
    {
        return $this->getQuote()->getEccSalesrepChosenCustomerId();
    }

}
