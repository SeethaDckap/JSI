<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Helper;

use Epicor\Lists\CustomerData\ListData;

class Context extends \Epicor\Comm\Helper\Context
{

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;
     /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
     /**
     * @var \Epicor\Common\Helper\Locale\Format\Currency
     */
    protected $commonLocaleFormatCurrencyHelper;
    protected $listsResourceListModelProductCollection;
    protected $catalogProductFactory;

    /**
     * @var ListData
     */
    private $listData;

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
    // FOR THIS CLASS
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ResourceModel\Product $catalogResourceProduct,
        \Magento\Customer\Model\Address $customerAddress,
        \Epicor\Comm\Model\ArrayMessages  $arrayMessages,
        \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory $commErpMappingShippingstatusFactory,
        \Epicor\Common\Helper\Locale\Format\Currency $commonLocaleFormatCurrencyHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\Collection $listsResourceListModelProductCollection,
        ListData $listData
        )
    {
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->commProductHelper = $commProductHelper;
        $this->resourceConnection = $resourceConnection;
        $this->commonLocaleFormatCurrencyHelper = $commonLocaleFormatCurrencyHelper;
        $this->commHelper = $commHelper;
        $this->listsResourceListModelProductCollection= $listsResourceListModelProductCollection;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->listData = $listData;

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
            $commMessageRequestLicsFactory,
            $commBrandingFactory,
            $commCustomerErpaccountFactory,
            $customerAddressFactory,
            $commMessageRequestAstFactory,
            $commResourceCustomerErpaccountCollectionFactory,
            $shippingConfig,
            $shippingConfigFactory,
            $backupBackupFactory,
            $backupDbFactory,
            $commResourceCustomerErpaccountAddressCollectionFactory,
            $commCustomerErpaccountAddressFactory,
            $commCustomerErpaccountAddressStoreFactory,
            $customerconnectMessageRequestCuau,
            $commConfigSourceCheckoutaddressFactory,
            $configConfigSourceYesnoFactory,
            $directoryResourceModelRegionCollectionFactory,
            $catalogProductFactory,
            $commResourceCustomerSkuCollectionFactory,
            $commMessageRequestMsqFactory,
            $customerCustomer,
            $listsListModelFactory,
            $response,
            $orderFactory,
            $storeGroup,
            $productMetadata,
            $designConfig,
            $themeProvider,
            $httpContext,
            $customerSession,
            $checkoutCart,
            $commErpMappingAttributesFactory,
            $commResourceErpMappingAttributesFactory,
            $messageManager,
            $serializer,
            $customerRepository,
            $catalogResourceProduct,
            $customerAddress,
            $arrayMessages,
            $commErpMappingShippingstatusFactory
        );
    }

    /**
     * @return \Epicor\Lists\Model\ListModel\TypeFactory 
     */
    public function getListsListModelTypeFactory()
    {
        return $this->listsListModelTypeFactory;
    }

    /**
     * @return \Epicor\Comm\Helper\Product
     */
    public function getCommProductHelper()
    {
        return $this->commProductHelper;
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResourceConnection()
    {
        return $this->resourceConnection;
    }
    /**
     * @return \Epicor\Common\Helper\Locale\Format\Currency
     */
    public function getEccLocaleFormatCurrencyHelper()
    {
        return $this->commonLocaleFormatCurrencyHelper;  
    }
        /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getProductFactory()
    {
        return $this->catalogProductFactory ;
    }
    public function getCommHelperForList() {
        $this->commHelper;
    }
    public function getListsListProductCollCsv(){
       return  $this->listsResourceListModelProductCollection;
    }

    /**
     * @return ListData
     */
    public function getListData()
    {
        return $this->listData;
    }
   
}
