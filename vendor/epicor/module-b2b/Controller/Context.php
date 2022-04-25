<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\B2b\Controller;

class Context extends \Magento\Framework\App\Action\Context
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;


    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\MessagingFactory
     */
    protected $commMessagingHelperFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;
        /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;
        /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface 
     */
    protected $customerRepositoryInterface;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    /**
     * @var \Epicor\B2b\Model\ResourceModel\User
     */
    private $user;
    
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\MessagingFactory $commMessagingHelperFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Epicor\Comm\Helper\DataFactory $commHelperFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Common\Helper\Xml $commonXmlHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Epicor\B2b\Model\ResourceModel\User $user
    )
    {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->commHelperFactory = $commHelperFactory;
        $this->commMessagingHelperFactory = $commMessagingHelperFactory;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig=$scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->customerconnectMessageRequestCuau =$customerconnectMessageRequestCuau;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->_localeResolver = $localeResolver;
        $this->customerRepositoryInterface = $_customerRepositoryInterface;
        $this->productMetadata = $productMetadata;
        $this->user = $user;
        parent::__construct($request, $response, $objectManager, $eventManager, $url, $redirect, $actionFlag, $view, $messageManager, $resultRedirectFactory, $resultFactory);
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Epicor\Comm\Helper\DataFactory
     */
    public function getCommHelperFactory()
    {
        return $this->commHelperFactory;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @return \Epicor\Comm\Helper\MessagingFactory
     */
    public function getCommMessagingHelperFactory()
    {
        return $this->commMessagingHelperFactory;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function getTransportBuilder()
    {
        return $this->transportBuilder;
    }

    /**
     * @return \Epicor\Customerconnect\Model\Message\Request\CuauFactory
     */
    public function getCustomerconnectMessageRequestCuau()
    {
        return $this->customerconnectMessageRequestCuau;
    }
    public function getCustomerConnectHelper(){
        return $this->customerconnectHelper;
    }
        /**
     * @return \Epicor\Common\Helper\Xml
     */
    public function getCommonXmlHelper()
    {
        return $this->commonXmlHelper;
    }
    
    public function getLocaleResolver(){
        return  $this->_localeResolver;
    }
    
    public function getCustomerRepositoryInterface(){
        return  $this->customerRepositoryInterface;
    }
    
    public function getProductMetadata(){
        return $this->productMetadata;
    }

    /**
     * Returns The User Resource Model
     * @return \Epicor\B2b\Model\ResourceModel\User
     */
    public function getUserResourceModel()
    {
        return $this->user;
    }
}