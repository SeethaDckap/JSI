<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Cart;

class EndMasquerade extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
   protected $salesRepHelper;

    protected $request;
    
    protected $customerSession;
      /**
    * @var \Magento\Framework\Stdlib\CookieManagerInterface
    */
    protected $_cookieManager;
 
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    
    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager     
    ) {
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->_cookieManager = $cookieManager;
        $this->sessionManager = $sessionManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
         parent::__construct($salesRepHelper, $customerSession, $request);
    }
    
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
           $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDomain($this->sessionManager->getCookieDomain());
           
        $this->_cookieManager->deleteCookie('erp_shipping_customer_addressId',$metadata);
        $this->_cookieManager->deleteCookie('erp_billing_customer_addressId',$metadata);
   
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        if ($customer->isSalesRep()) {
            $helper = $this->salesRepHelper;
            /* @var $helper Epicor_SalesRep_Helper_Data */
            $helper->wipeCart();
        }
    }

}