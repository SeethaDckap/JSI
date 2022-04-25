<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Helper;


class Context extends \Epicor\Common\Helper\Context
{
    /**
     * @var \Epicor\Comm\Model\Message\Request\LicsFactory
     */
    protected $commMessageRequestLicsFactory;

    /**
     * @var \Epicor\Comm\Model\BrandingFactory
     */
    protected $commBrandingFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\AstFactory
     */
    protected $commMessageRequestAstFactory;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Magento\Shipping\Model\ConfigFactory
     */
    protected $shippingConfigFactory;

    /**
     * @var \Magento\Backup\Model\BackupFactory
     */
    protected $backupBackupFactory;

    /**
     * @var \Magento\Backup\Model\DbFactory
     */
    protected $backupDbFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\Address\StoreFactory
     */
    protected $commCustomerErpaccountAddressStoreFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\CuauFactory
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Epicor\Comm\Model\Config\Source\CheckoutaddressFactory
     */
    protected $commConfigSourceCheckoutaddressFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $configConfigSourceYesnoFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $directoryResourceModelRegionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    protected $commResourceCustomerSkuCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerCustomer;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $storeGroup;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Theme\Model\Data\Design\Config
     */
    protected $designConfig;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProvider;

    protected $httpContext;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;


    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;


    /**
     * @var \Epicor\Comm\Model\ArrayMessages
     */
    protected $arrayMessages;

    public function __construct(
        // FOR PARENT
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Cache\ConfigInterface $cacheConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\AdminNotification\Model\InboxFactory $adminNotificationInboxFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $emailTemplateFactory,
        \Magento\Framework\Translate\Inline\StateInterface $translateInterface,
        \Magento\Framework\Session\GenericFactory $genericFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Model\Message\Upload\CusFactory $commMessageUploadCusFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Checkout\Model\CartFactory $checkoutCartFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteResourceModelQuoteCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Epicor\Common\Helper\Data\ErpSourceReader $erpSourceReader,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Store\Model\System\Store $storeSystemStore,
        // FOR THIS CLASS
        \Epicor\Comm\Model\Message\Request\LicsFactory $commMessageRequestLicsFactory,
        \Epicor\Comm\Model\BrandingFactory $commBrandingFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Shipping\Model\ConfigFactory $shippingConfigFactory,
        \Magento\Backup\Model\BackupFactory $backupBackupFactory,
        \Magento\Backup\Model\DbFactory $backupDbFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\Address\StoreFactory $commCustomerErpaccountAddressStoreFactory,
        \Epicor\Customerconnect\Model\Message\Request\CuauFactory $customerconnectMessageRequestCuau,
        \Epicor\Comm\Model\Config\Source\CheckoutaddressFactory $commConfigSourceCheckoutaddressFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $configConfigSourceYesnoFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $directoryResourceModelRegionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory $commResourceCustomerSkuCollectionFactory,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Magento\Customer\Model\Customer $customerCustomer,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\GroupFactory $storeGroup,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Magento\Theme\Model\Data\Design\Config $designConfig,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Model\Erp\Mapping\AttributesFactory $commErpMappingAttributesFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\AttributesFactory $commResourceErpMappingAttributesFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ResourceModel\Product $catalogResourceProduct,
        \Magento\Customer\Model\Address $customerAddress,
        \Epicor\Comm\Model\ArrayMessages  $arrayMessages,
        \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory $commErpMappingShippingstatusFactory
    )
    {
        $this->httpContext = $httpContext;
        $this->commMessageRequestLicsFactory = $commMessageRequestLicsFactory;
        $this->commBrandingFactory = $commBrandingFactory;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->shippingConfig = $shippingConfig;
        $this->shippingConfigFactory = $shippingConfigFactory;
        $this->backupBackupFactory = $backupBackupFactory;
        $this->backupDbFactory = $backupDbFactory;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->commCustomerErpaccountAddressStoreFactory = $commCustomerErpaccountAddressStoreFactory;
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->commConfigSourceCheckoutaddressFactory = $commConfigSourceCheckoutaddressFactory;
        $this->configConfigSourceYesnoFactory = $configConfigSourceYesnoFactory;
        $this->directoryResourceModelRegionCollectionFactory = $directoryResourceModelRegionCollectionFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->commResourceCustomerSkuCollectionFactory = $commResourceCustomerSkuCollectionFactory;
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->customerCustomer = $customerCustomer;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->response = $response;
        $this->orderFactory = $orderFactory;
        $this->storeGroup = $storeGroup;
        $this->productMetadata = $productMetadata;
        $this->designConfig = $designConfig;
        $this->themeProvider = $themeProvider;
        $this->customerSession = $customerSession;
        $this->checkoutCart = $checkoutCart;
        $this->commErpMappingAttributesFactory = $commErpMappingAttributesFactory;
        $this->commResourceErpMappingAttributesFactory = $commResourceErpMappingAttributesFactory;
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
        $this->eventManager = $eventManager;
        $this->arrayMessages = $arrayMessages;


        parent::__construct(
            $urlEncoder,
            $urlDecoder,
            $logger,
            $moduleManager,
            $httpRequest,
            $cacheConfig,
            $eventManager,
            $urlBuilder,
            $httpHeader,
            $remoteAddress,
            $scopeConfig,
            $registry,
            $commMessagingHelper,
            $storeManager,
            $directoryHelper,
            $adminNotificationInboxFactory,
            $emailTemplateFactory,
            $translateInterface,
            $genericFactory,
            $request,
            $commMessageUploadCusFactory,
            $commHelper,
            $catalogResourceModelProductCollectionFactory,
            $cache,
            $directoryCountryFactory,
            $directoryRegionFactory,
            $checkoutCartFactory,
            $customerSessionFactory,
            $quoteQuoteFactory,
            $quoteResourceModelQuoteCollectionFactory,
            $listsResourceListModelCollectionFactory,
            $customerCustomerFactory,
            $listsResourceListModelAddressCollectionFactory,
            $commLocationsHelper,
            $listsFrontendProductHelper,
            $listsFrontendContractHelper,
            $ioFileFactory,
            $dataObjectFactory,
            $resourceConfig,
            $globalConfig,
            $directoryList,
            $design,
            $localeResolver,
            $timezone,
            $erpSourceReader,
            $localeCurrency,
            $listsSessionHelper,
            $commonAccessHelper,
            $storeSystemStore,
            $messageManager,
            $catalogResourceProduct,
            $customerAddress,
            $commErpMappingShippingstatusFactory,
            $productMetadata
        );
    }

    /**
     * @return \Epicor\Comm\Model\ArrayMessages
     */
    public function getArrayMessages()
    {
        return $this->arrayMessages;
    }


    /**
     * @return \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }


    /**
     * @return \Magento\Checkout\Model\Cart
     */
    public function getCheckoutCart()
    {
        return $this->getCheckoutCartFactory()->create();
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\AttributesFactory
     */
    public function getCommErpMappingAttributesFactory()
    {
        return $this->commErpMappingAttributesFactory;
    }

    /**
     * @return \Epicor\Comm\Model\ResourceModel\Erp\Mapping\AttributesFactory
     */
    public function getCommResourceErpMappingAttributesFactory()
    {
        return $this->commResourceErpMappingAttributesFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Message\Request\LicsFactory
     */
    public function getCommMessageRequestLicsFactory()
    {
        return $this->commMessageRequestLicsFactory;
    }

    /**
     * @return \Epicor\Comm\Model\BrandingFactory
     */
    public function getCommBrandingFactory()
    {
        return $this->commBrandingFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    public function getCommCustomerErpaccountFactory()
    {
        return $this->commCustomerErpaccountFactory;
    }

    /**
     * @return \Magento\Backup\Model\BackupFactory
     */
    public function getBackupBackupFactory()
    {
        return $this->backupBackupFactory;
    }

    /**
     * @return \Magento\Backup\Model\DbFactory
     */
    public function getBackupDbFactory()
    {
        return $this->backupDbFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    public function getCommCustomerErpaccountAddressFactory()
    {
        return $this->commCustomerErpaccountAddressFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Customer\Erpaccount\Address\StoreFactory
     */
    public function getCommCustomerErpaccountAddressStoreFactory()
    {
        return $this->commCustomerErpaccountAddressStoreFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Config\Source\CheckoutaddressFactory
     */
    public function getCommConfigSourceCheckoutaddressFactory()
    {
        return $this->commConfigSourceCheckoutaddressFactory;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getCatalogProductFactory()
    {
        return $this->catalogProductFactory;
    }

    /**
     * @return \Magento\Customer\Model\AddressFactory
     */
    public function getCustomerAddressFactory()
    {
        return $this->customerAddressFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Message\Request\AstFactory
     */
    public function getCommMessageRequestAstFactory()
    {
        return $this->commMessageRequestAstFactory;
    }

    /**
     * @return \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    public function getCommResourceCustomerErpaccountCollectionFactory()
    {
        return $this->commResourceCustomerErpaccountCollectionFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    public function getCommMessageRequestMsqFactory()
    {
        return $this->commMessageRequestMsqFactory;
    }

    /**
     * @return \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    public function getCommResourceCustomerErpaccountAddressCollectionFactory()
    {
        return $this->commResourceCustomerErpaccountAddressCollectionFactory;
    }

    /**
     * @return \Epicor\Customerconnect\Model\Message\Request\CuauFactory
     */
    public function getCustomerconnectMessageRequestCuau()
    {
        return $this->customerconnectMessageRequestCuau;
    }

    /**
     * @return \Magento\Config\Model\Config\Source\YesnoFactory
     */
    public function getConfigConfigSourceYesnoFactory()
    {
        return $this->configConfigSourceYesnoFactory;
    }

    /**
     * @return \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    public function getCommResourceCustomerSkuCollectionFactory()
    {
        return $this->commResourceCustomerSkuCollectionFactory;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerCustomer()
    {
        return $this->customerCustomer;
    }

    /**
     * @return \Magento\Framework\App\ProductMetadata
     */
    public function getProductMetadata()
    {
        return $this->productMetadata;
    }

    /**
     * @return \Magento\Theme\Model\Data\Design\Config
     */
    public function getDesignConfig()
    {
        return $this->designConfig;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    public function getDirectoryResourceModelRegionCollectionFactory()
    {
        return $this->directoryResourceModelRegionCollectionFactory;
    }

    /**
     * @return \Magento\Shipping\Model\Config
     */
    public function getShippingConfig()
    {
        return $this->shippingConfig;
    }

    /**
     * @return \Magento\Shipping\Model\ConfigFactory
     */
    public function getShippingConfigFactory()
    {
        return $this->shippingConfigFactory;
    }

    /**
     * @return \Epicor\Lists\Model\ListModelFactory
     */
    public function getListsListModelFactory()
    {
        return $this->listsListModelFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \Magento\Sales\Model\OrderFactory
     */
    public function getOrderFactory()
    {
        return $this->orderFactory;
    }

    /**
     * @return \Magento\Store\Model\GroupFactory
     */
    public function getStoreGroup()
    {
        return $this->storeGroup;
    }

    /**
     * @return \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    public function getThemeProvider()
    {
        return $this->themeProvider;
    }

    /**
     * @return \Magento\Framework\App\Http\Context
     */
    public function getHttpContext()
    {
        return $this->httpContext;
    }
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public function getCustomerRepository()
    {
        return $this->customerRepository;
    }
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public function getCustomerFactory()
    {
        return $this->customerCustomerFactory;
    }
    
}