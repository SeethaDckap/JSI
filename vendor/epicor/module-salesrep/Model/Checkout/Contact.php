<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Checkout;


class Contact extends \Magento\Framework\Model\AbstractModel
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
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->salesRepCheckoutHelper = $salesRepCheckoutHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
    protected function _construct()
    {
        
        parent::_construct();
    }

    public function isSalesRep()
    {
        $helper = $this->salesRepCheckoutHelper;
        /* @var $helper Epicor_Salesrep_Helper_Checkout */

        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        return $customer->isSalesRep();
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
