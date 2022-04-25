<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Helper;


class Context extends \Magento\Framework\App\Helper\Context
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $adminNotificationInboxFactory;

    /**
     * //@var \Magento\Email\Model\TemplateFactory
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $emailTemplateFactory;

    /**
     * //@var \Magento\Framework\TranslateInterface
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $translateInterface;

    /**
     * @var \Magento\Framework\Session\GenericFactory
     */
    protected $genericFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Model\Message\Upload\CusFactory
     */
    protected $commMessageUploadCusFactory;



    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $checkoutCartFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteResourceModelQuoteCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\Filesystem\Io\FileFactory
     */
    protected $ioFileFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Data\ErpSourceReader
     */
    protected $erpSourceReader;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /*
     *  \Magento\Catalog\Model\ResourceModel\Product $catalogResourceProduct    
     */
    protected  $catalogProductResourceModel;
    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $customerAddress;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory
     */
    protected $commErpMappingShippingstatusFactory;

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
        // FOR THIS CLASS
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
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ResourceModel\Product $catalogResourceProduct,
        \Magento\Customer\Model\Address $customerAddress,
        \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory $commErpMappingShippingstatusFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        $this->ioFileFactory = $ioFileFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->adminNotificationInboxFactory = $adminNotificationInboxFactory;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->translateInterface = $translateInterface;
        $this->genericFactory = $genericFactory;
        $this->request = $request;
        $this->commMessageUploadCusFactory = $commMessageUploadCusFactory;
        $this->commHelper = $commHelper;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->cache = $cache;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->checkoutCartFactory = $checkoutCartFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->quoteQuoteFactory = $quoteQuoteFactory;
        $this->quoteResourceModelQuoteCollectionFactory = $quoteResourceModelQuoteCollectionFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->resourceConfig=$resourceConfig;
        $this->globalConfig = $globalConfig;
        $this->design = $design;
        $this->directoryList = $directoryList;
        $this->timezone = $timezone;
        $this->_localeResolver = $localeResolver;
        $this->erpSourceReader = $erpSourceReader;
        $this->localeCurrency = $localeCurrency;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->checkoutSession = $checkoutCartFactory->create()->getCheckoutSession();
        $this->storeSystemStore = $storeSystemStore;
        $this->messageManager = $messageManager;
        $this->catalogProductResourceModel = $catalogResourceProduct;
        $this->customerAddress = $customerAddress;
        $this->commErpMappingShippingstatusFactory = $commErpMappingShippingstatusFactory;
        $this->productMetadata = $productMetadata;

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
            $scopeConfig
        );
    }

    /**
     * @return \Epicor\Lists\Helper\Session
     */
    public function getListsSessionHelper()
    {
        return $this->listsSessionHelper;
    }

    /**
     * @return \Epicor\Common\Helper\Access
     */
    public function getCommonAccessHelper()
    {
        return $this->commonAccessHelper;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return \Magento\Store\Model\System\Store
     */
    public function getStoreSystemStore()
    {
        return $this->storeSystemStore;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Epicor\Comm\Helper\Messaging
     */
    public function getCommMessagingHelper()
    {
        return $this->commMessagingHelper;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getDirectoryHelper()
    {
        return $this->directoryHelper;
    }

    /**
     * @return \Magento\AdminNotification\Model\InboxFactory
     */
    public function getAdminNotificationInboxFactory()
    {
        return $this->adminNotificationInboxFactory;
    }

    /**
     * @return \Magento\Email\Model\TemplateFactory
     */
    public function getEmailTemplateFactory()
    {
        return $this->emailTemplateFactory;
    }

    /**
     * @return \Magento\Framework\Translate\Inline\StateInterface
     */
    public function getTranslateInterface()
    {
        return $this->translateInterface;
    }

    /**
     * @return \Magento\Framework\Session\GenericFactory
     */
    public function getGenericFactory()
    {
        return $this->genericFactory;
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Epicor\Comm\Model\Message\Upload\CusFactory
     */
    public function getCommMessageUploadCusFactory()
    {
        return $this->commMessageUploadCusFactory;
    }



    /**
     * @return \Epicor\Comm\Helper\Data
     */
    public function getCommHelper()
    {
        return $this->commHelper;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getCatalogResourceModelProductCollectionFactory()
    {
        return $this->catalogResourceModelProductCollectionFactory;
    }

    /**
     * @return \Magento\Framework\App\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return \Magento\Directory\Model\CountryFactory
     */
    public function getDirectoryCountryFactory()
    {
        return $this->directoryCountryFactory;
    }

    /**
     * @return \Magento\Directory\Model\RegionFactory
     */
    public function getDirectoryRegionFactory()
    {
        return $this->directoryRegionFactory;
    }

    /**
     * @return \Magento\Checkout\Model\CartFactory
     */
    public function getCheckoutCartFactory()
    {
        return $this->checkoutCartFactory;
    }

    /**
     * @return \Magento\Customer\Model\SessionFactory
     */
    public function getCustomerSessionFactory()
    {
        return $this->customerSessionFactory;
    }

    /**
     * @return \Magento\Quote\Model\QuoteFactory
     */
    public function getQuoteQuoteFactory()
    {
        return $this->quoteQuoteFactory;
    }

    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    public function getQuoteResourceModelQuoteCollectionFactory()
    {
        return $this->quoteResourceModelQuoteCollectionFactory;
    }

    /**
     * @return \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    public function getListsResourceListModelCollectionFactory()
    {
        return $this->listsResourceListModelCollectionFactory;
    }

    /**
     * @return \Magento\Customer\Model\CustomerFactory
     */
    public function getCustomerCustomerFactory()
    {
        return $this->customerCustomerFactory;
    }

    /**
     * @return \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    public function getListsResourceListModelAddressCollectionFactory()
    {
        return $this->listsResourceListModelAddressCollectionFactory;
    }

    /**
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getCommLocationsHelper()
    {
        return $this->commLocationsHelper;
    }

    /**
     * @return \Epicor\Lists\Helper\Frontend\Product
     */
    public function getListsFrontendProductHelper()
    {
        return $this->listsFrontendProductHelper;
    }

    /**
     * @return \Epicor\Lists\Helper\Frontend\Contract
     */
    public function getListsFrontendContractHelper()
    {
        return $this->listsFrontendContractHelper;
    }

    /**
     * @return \Magento\Framework\Filesystem\Io\FileFactory
     */
    public function getIoFileFactory()
    {
        return $this->ioFileFactory;
    }

    /**
     * @return \Magento\Framework\DataObjectFactory
     */
    public function getDataObjectFactory()
    {
        return $this->dataObjectFactory;
    }

    /**
     * @return \Epicor\Comm\Model\GlobalConfig\Config
     */
    public function getGlobalConfig()
    {
        return $this->globalConfig;
    }

    /**
     * @return \Magento\Config\Model\ResourceModel\Config
     */
    public function getResourceConfig()
    {
        return $this->resourceConfig;
    }

    /**
     * @return \Magento\Framework\View\DesignInterface
     */
    public function getDesign()
    {
        return $this->design;
    }

    /**
     * @return \Magento\Framework\App\Filesystem\DirectoryList
     */
    public function getDirectoryList()
    {
        return $this->directoryList;
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function getLocaleResolver()
    {
        return $this->_localeResolver;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return Data\ErpSourceReader
     */
    public function getErpSourceReader()
    {
        return $this->erpSourceReader;
    }

    /**
     * @return \Magento\Framework\Locale\CurrencyInterface
     */
    public function getLocaleCurrency()
    {
        return $this->localeCurrency;
    }

    public function getMessageManager(){
        return $this->messageManager;
    }

    public function getCatalogProductResource(){
        return  $this->catalogProductResourceModel;
    }

    /*
     * @var \Magento\Customer\Model\Address
     */
    public function getCustomerAddress(){
        return  $this->customerAddress;
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory
     */
    public function getCommErpMappingShippingstatusFactory(){
        return $this->commErpMappingShippingstatusFactory;
    }

    public function getProductMetaData(){
        return $this->productMetadata;
    }
}
