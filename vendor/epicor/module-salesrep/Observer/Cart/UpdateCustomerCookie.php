<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\SalesRep\Observer\Cart;

/**
 * Description of UpdateCustomerCookie
 *
 * @author ashwani.arya
 */
class UpdateCustomerCookie extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
  
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
   
    }

}