<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class ArpaymentsAssignDataVault extends AbstractDataAssignObserver
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
    
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($handle) {
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }
}