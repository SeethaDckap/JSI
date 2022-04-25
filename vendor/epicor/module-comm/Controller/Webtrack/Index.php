<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Webtrack;

use Magento\Framework\Controller\ResultFactory;

/**
 * Redirect to WEB Track
 *
 */
class Index extends \Epicor\Comm\Controller\webtrack {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
        
    /**
     *
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;
    
    /**
     *
     * @var \Epicor\Comm\Model\Message\Request\AstFactory 
     */
    protected $commMessageRequestAstFactory;
    
    /**
     * @var string
     */
    protected $ssoToken = '';
    
    /**
     * @var string
     */
    protected $loginId = '';
        
    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory
    ) {
        parent::__construct($context, $logger);
        $this->_scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
    }

    public function execute() {    
        $this->loginId = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : false;
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->loginId) {
            return $resultRedirect->setUrl($this->_url->getBaseUrl());
        }

        //if(!$this->customerSession->getSsoToken()){
            $ast = $this->commMessageRequestAstFactory->create();
            $customer = $this->customerSession->getCustomer();
            $customer->setIsWebtrack(true);
            $ast->setCustomer($customer);
            $ast->sendMessage();
        //}
                    
        $this->ssoToken = $this->customerSession->getSsoToken() ?: '';
        if(!$this->ssoToken){   
            $this->_logger->critical("Webtrack ssoToken is missing from ast message.");
            $this->messageManager->addWarningMessage("Hi ".$this->loginId . ", Sorry you don't have a WebTrack User Account.");
            return $resultRedirect->setUrl($this->_url->getBaseUrl());                      
        }
        
        $webtrackUrl = $this->_scopeConfig->getValue(self::WBBTRACK_URL_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $param = "ssoToken=" . urlencode ($this->ssoToken . "+" . $this->loginId);

        $UrlData = parse_url($webtrackUrl);
        if ($UrlData && !isset($UrlData["scheme"])) {
            $webtrackUrl = 'http://' . $webtrackUrl;
        }
        
        $url = $webtrackUrl . "?" . $param;
        return $resultRedirect->setUrl($url);
    }

}
