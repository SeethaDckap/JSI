<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\SalesRep\Controller;

class Context extends \Magento\Framework\App\Action\Context
{
    protected $customerSession;

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    
    /*
     *  /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqd
     */
    protected $customerconnectMessageRequestCrqd;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
     
    protected $salesRepPricingRuleFactory;

     /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /*
     * @var \Magento\Framework\Unserialize\Unserialize $unserialize
     */
    protected $unserialize;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $_urlBuilder,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Psr\Log\LoggerInterface $logger,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\SalesRep\Model\Pricing\RuleFactory $salesRepPricingRuleFactory,
        \Magento\Customer\Model\Session $customerSession,
         \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Unserialize\Unserialize $unserialize,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor   
    )
    {
        $this->customerSession = $customerSession;
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        $this->registry = $registry;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->logger = $logger;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->salesRepPricingRuleFactory = $salesRepPricingRuleFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->request = $request;
        $this->customerconnectMessageRequestCrqd = $customerconnectMessageRequestCrqd;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commHelper = $commHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlDecoder = $urlDecoder;
        $this->_urlBuilder = $_urlBuilder;
        $this->unserialize = $unserialize;
        $this->encryptor = $encryptor;

        parent::__construct($request, $response, $objectManager, $eventManager, $_urlBuilder, $redirect, $actionFlag, $view, $messageManager, $resultRedirectFactory, $resultFactory);
    }
    
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }
    
    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Epicor\SalesRep\Helper\Account\Manage
     */
    public function getSalesRepAccountManageHelper()
    {
        return $this->salesRepAccountManageHelper;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Customer\Model\CustomerFactory
     */
    public function getCustomerCustomerFactory()
    {
        return $this->customerCustomerFactory;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return \Epicor\SalesRep\Model\AccountFactory
     */
    public function getSalesRepAccountFactory()
    {
        return $this->salesRepAccountFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\LayoutFactory
     */
    public function getResultLayoutFactory()
    {
        return $this->resultLayoutFactory;
    }

    /**
     * @return \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    public function getSalesRepPricingRuleFactory()
    {
        return $this->salesRepPricingRuleFactory;
    }
    
    public  function getCustomerconnectHelper(){
        return $this->customerconnectHelper ;
    }
    
    public  function getRequest(){
          return $this->request ;
    }
    
    public  function getCustomerconnectMessageRequestCrqd(){
          return $this->customerconnectMessageRequestCrqd ;
    }
    
    public  function getCommonAccessHelper(){
          return $this->commonAccessHelper ;
    }
    
    public  function getCustomerconnectMessagingHelper(){
          return $this->customerconnectMessagingHelper;
    }
    
    public  function getCommMessagingHelper(){
          return  $this->commMessagingHelper;
    }
    
    public  function getCommHelper(){
          return $this->commHelper ;
    }
    
    public  function getFrameworkHelperDataHelper(){
          return $this->frameworkHelperDataHelper;
    }
    
    public  function getLocalResolver(){
          return  $this->_localeResolver ;
    }
    
    public  function getUrlDecoder(){
          return  $this->urlDecoder ;
    }
  
    public  function getUnserialize(){
          return  $this->unserialize ;
    }
    
    public  function getEncryptor(){
          return  $this->encryptor ;
    }
 
}