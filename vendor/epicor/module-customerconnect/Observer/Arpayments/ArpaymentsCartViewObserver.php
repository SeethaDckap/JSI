<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;


use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;


class ArpaymentsCartViewObserver  implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    protected $arpaymentsHelper;
    protected $mCheckoutSession;
    protected $responseFactory;
    protected $url;    
    private $cookieManager;
    
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;       
    
    public function __construct(\Magento\Framework\Registry $registry, 
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper, 
                                \Magento\Checkout\Model\Session $mCheckoutSession,
                                \Magento\Framework\App\ResponseFactory $responseFactory,
                                PhpCookieManager $cookieManager,
                                CookieMetadataFactory $cookieMetadataFactory,             
                                \Magento\Framework\UrlInterface $url,            
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry        = $registry;
        $this->_request         = $request;
        $this->mCheckoutSession = $mCheckoutSession;
        $this->responseFactory = $responseFactory;
        $this->url = $url;              
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;            
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        $checkoutSession = $this->mCheckoutSession;
        $quote  = $checkoutSession->getQuote();
        if ($quote->getArpaymentsQuote()) {
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }               
            $redirectionUrl = $this->url->getUrl('customerconnect/arpayments/Proceedtoindex');
            $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
            return $this;               
        }
    }
}