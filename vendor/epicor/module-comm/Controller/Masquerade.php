<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;


/**
 * ERP Account controller controller
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class Masquerade extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $decoder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\DecoderInterface $decoder,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory
    )
    {
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->eventManager = $context->getEventManager();
        $this->customerSession = $customerSession;
        $this->decoder = $decoder;
        parent::__construct(
            $context
        );
    }

    protected function _processCart($action)
    {
        $cart = $this->checkoutCart;
        /* @var $cart \Magento\Checkout\Model\Cart */
        $session = $this->checkoutSession;
        /* @var $cart \Magento\Checkout\Model\Session */

        $helper = $this->commHelper;
        /* @var $helper \Epicor\Comm\Helper\Data */
        $cart->getQuote()->setEccErpAccountId($helper->getErpAccountInfo()->getId());
        $this->customerSessionFactory->create()->setCartMsqRegistry(array());
        $this->customerSessionFactory->create()->setBsvTriggerTotals(array());
        $cart->getQuote()->setTotalsCollectedFlag(false);
        switch ($action) {
            case'clear':
                $this->eventManager->dispatch('checkout_cart_empty', array());
                $this->_updateCartAddresses();
                $cart->truncate()->save();
                $session->setCartWasUpdated(true);
                break;
            case 'reprice':
                $this->_updateCartAddresses();
                $cart->save();
                $session->setCartWasUpdated(true);
                break;
            case 'save':
                // No Actions here... yet
                break;
        }
    }

    private function _updateCartAddresses()
    {
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $erpAccountInfo = $commHelper->getErpAccountInfo();
        /* @var $erpAccountInfo Epicor_Comm_Model_Customer_Erpaccount */

        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $defaultBillingAddressCode = $erpAccountInfo->getDefaultInvoiceAddressCode();
        $defaultShippingAddressCode = $erpAccountInfo->getDefaultDeliveryAddressCode();

        $quote = $this->checkoutSession->getQuote();

        $billingAddress = $erpAccountInfo->getAddress($defaultBillingAddressCode);

        if ($billingAddress) {
            $erpAddress = $billingAddress->toCustomerAddress($customer, $erpAccountInfo->getId());
            $quoteBillingAddress = $this->quoteQuoteAddressFactory->create([]);
            $quoteBillingAddress->setData($erpAddress->getData());
            $quote->setBillingAddress($quoteBillingAddress);
        }

        $shippingAddress = $erpAccountInfo->getAddress($defaultShippingAddressCode);

        if ($shippingAddress) {
            $erpAddress = $shippingAddress->toCustomerAddress($customer, $erpAccountInfo->getId());
            $quoteShippingAddress = $this->quoteQuoteAddressFactory->create([]);
            $quoteShippingAddress->setData($erpAddress->getData());
            $quote->setShippingAddress($quoteShippingAddress);
        }
    }

}
