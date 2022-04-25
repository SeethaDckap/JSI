<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Checkout;

/**
 * One page checkout processing model
 */
class PaymentInformationManagementPlugin
{

    /*
     * @var \Epicor\SalesRep\Helper\Checkout
     */
    protected $salesrepHelper;
    /**
    * @var \Magento\Framework\Stdlib\CookieManagerInterface
    */
    protected $_cookieManager;

    protected $quoteRepository;

    protected $customerCustomerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;



    public function __construct(
        \Epicor\SalesRep\Helper\Checkout  $salesrepHelper,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->salesrepHelper = $salesrepHelper;
        $this->_cookieManager = $cookieManager;
        $this->quoteRepository = $quoteRepository;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerSession = $customerSession;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    public function beforeSavePaymentInformation(
       \Magento\Checkout\Model\PaymentInformationManagement $subject,
         $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {

         $customer = $this->customerSession->getCustomer();

        if($customer->isSalesRep() && $billingAddress){
            $quote = $this->quoteRepository->getActive($cartId);
            if($quote->getArpaymentsQuote()) {
              return array($cartId,$paymentMethod,$billingAddress);
            }
            $cusAddressId = $this->_cookieManager->getCookie('erp_billing_customer_addressId');
            if (!$billingAddress->getCustomerAddressId() &&  $cusAddressId != 'new-address') {
                 if($cusAddressId==null){
                       $cusAddressId =   $this->_cookieManager->getCookie('erp_shipping_customer_addressId');
                 }
               if($cusAddressId!=null){
                   $erpAddress = $this->salesrepHelper->_getMasqAddressData($cusAddressId,$quote);
                   if(isset($erpAddress['erp_code'])){
                              $billingAddress->setEccErpAddressCode($erpAddress['erp_code']);
                       }
                       $billingAddress->addData($erpAddress);
               }
            }

            if($quote->getEccSalesrepChosenCustomerInfo()){
                $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());
                if (isset($customerInfo['name'])) {

                    $nameParts = explode(' ', $customerInfo['name'], 3);
                    $firstname = $nameParts[0];
                    if (count($nameParts) == 3) {
                        $lastname = $nameParts[2];
                    } else {
                        $lastname = $nameParts[1];
                    }
                    $billingAddress->setFirstname($firstname);
                    $billingAddress->setLastname($lastname);
                }
           }else{
                $billingAddress->setFirstname($customer->getFirstname());
                $billingAddress->setLastname($customer->getLastname());
           }

        }

        //set ecc erp address code for BSV when B2B is Masquerade to children
        if ($this->customerSession->getB2BHierarchyMasquerade()) {
            $quote = $this->quoteRepository->getActive($cartId);
            $cusAddressId = $this->_cookieManager->getCookie('erp_billing_customer_addressId');
            if (!$billingAddress->getCustomerAddressId() &&  $cusAddressId != 'new-address') {
                if($cusAddressId==null){
                    $cusAddressId =   $this->_cookieManager->getCookie('erp_shipping_customer_addressId');
                }
                if($cusAddressId!=null){
                    $erpAddress = $this->salesrepHelper->_getMasqAddressData($cusAddressId,$quote);
                    if(isset($erpAddress['erp_code'])){
                        $billingAddress->setEccErpAddressCode($erpAddress['erp_code']);
                    }
                    $billingAddress->addData($erpAddress);
                }
            }
        }
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDomain($this->sessionManager->getCookieDomain());

        return array($cartId,$paymentMethod,$billingAddress);
    }
}
