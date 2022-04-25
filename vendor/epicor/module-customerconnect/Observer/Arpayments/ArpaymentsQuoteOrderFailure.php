<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class ArpaymentsQuoteOrderFailure extends AbstractObserver  implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    protected $arpaymentsHelper;
    protected $checkoutsession;
    
    /**
     * @var PhpCookieManager
     */
    private $cookieManager;
    
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    
    public function __construct(
       \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,                
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Framework\App\Request\Http $request)
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry = $registry;
        $this->_request = $request;
        $this->checkoutsession = $checkoutsession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;            
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($handle) {
            $quote = $observer->getEvent()->getQuote();
            if ($quote->getArpaymentsQuote()) {
                $quote->setCustomerId(null)->save();
                if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                    $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                }
            }
        }
    }
}