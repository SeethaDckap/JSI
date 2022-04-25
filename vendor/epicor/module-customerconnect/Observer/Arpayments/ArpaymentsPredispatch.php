<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class ArpaymentsPredispatch extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    protected $arpaymentsHelper;
    
    public function __construct(\Magento\Framework\Registry $registry, 
                                PhpCookieManager $cookieManager,
                                CookieMetadataFactory $cookieMetadataFactory,             
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry        = $registry;
        $this->_request         = $request;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;          
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        $arpayments = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();
        if ($handle || $arpayments) {
             $this->arpaymentsHelper->clearArpaymentStorage();
        }
    }
}