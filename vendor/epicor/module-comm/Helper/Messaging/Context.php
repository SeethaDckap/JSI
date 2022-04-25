<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Helper\Messaging;


class Context extends \Epicor\Comm\Helper\Context
{
    /**
     * @var \Epicor\Comm\Model\Message\UploadFactory
     */
    protected $commMessageUploadFactory;

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CountryFactory
     */
    protected $commErpMappingCountryFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory
     */
    protected $commErpMappingOrderstatusFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentFactory
     */
    protected $commErpMappingPaymentFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory
     */
    protected $commErpMappingCurrencyFactory;

    /**
     * @var \Epicor\Common\Model\Erp\Mapping\LanguageFactory
     */
    protected $commonErpMappingLanguageFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingShippingmethodFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesResourceModelOrderCollectionFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory
     */
    protected $customerconnectErpMappingErporderstatusFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $salesResourceModelOrderInvoiceCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Payment
     */
    protected $commErpMappingPayment;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Cardtype\CollectionFactory
     */
    protected $commResourceErpMappingCardtypeCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCurrencyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Convert\OrderFactory
     */
    protected $salesConvertOrderFactory;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $salesOrderServiceFactory;

    /**
     * @var \Epicor\Common\Model\MessageUploadModelReader
     */
    protected $messageUploadModelReader;

    /**
     * @var \Epicor\Common\Model\MessageRequestModelReader
     */
    protected $messageRequestModelReader;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceServiceFactory
     */
    protected $invoiceServiceFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

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
        \Epicor\Comm\Model\Message\UploadFactory $commMessageUploadFactory,
        \Epicor\Common\Helper\Xml $commonXmlHelper,
        \Epicor\Comm\Model\Erp\Mapping\CountryFactory $commErpMappingCountryFactory,
        \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory $commErpMappingOrderstatusFactory,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPaymentFactory,
        \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory $commErpMappingCurrencyFactory,
        \Epicor\Common\Model\Erp\Mapping\LanguageFactory $commonErpMappingLanguageFactory,
        \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory $commErpMappingShippingmethodFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesResourceModelOrderCollectionFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory $customerconnectErpMappingErporderstatusFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $salesResourceModelOrderInvoiceCollectionFactory,
        \Epicor\Comm\Model\Erp\Mapping\Payment $commErpMappingPayment,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Cardtype\CollectionFactory $commResourceErpMappingCardtypeCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\CollectionFactory $commResourceCustomerErpaccountCurrencyCollectionFactory,
        \Epicor\Common\Model\MessageUploadModelReader $messageUploadModelReader,
        \Epicor\Common\Model\MessageRequestModelReader $messageRequestModelReader,
        \Magento\Sales\Model\Convert\OrderFactory $salesConvertOrderFactory,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Service\InvoiceServiceFactory $invoiceServiceFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ResourceModel\Product $catalogResourceProduct,
        \Magento\Customer\Model\Address $customerAddress,
        \Epicor\Comm\Model\ArrayMessages  $arrayMessages,
        \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory $commErpMappingShippingstatusFactory
    )
    {
        $this->shipmentFactory = $shipmentFactory;
        $this->invoiceServiceFactory = $invoiceServiceFactory;
        $this->salesOrderServiceFactory = $orderService;
        $this->salesConvertOrderFactory = $salesConvertOrderFactory;
        $this->commMessageUploadFactory = $commMessageUploadFactory;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->commErpMappingCountryFactory = $commErpMappingCountryFactory;
        $this->commErpMappingOrderstatusFactory = $commErpMappingOrderstatusFactory;
        $this->commErpMappingPaymentFactory = $commErpMappingPaymentFactory;
        $this->commErpMappingCurrencyFactory = $commErpMappingCurrencyFactory;
        $this->commonErpMappingLanguageFactory = $commonErpMappingLanguageFactory;
        $this->commErpMappingShippingmethodFactory = $commErpMappingShippingmethodFactory;
        $this->transactionFactory = $transactionFactory;
        $this->salesResourceModelOrderCollectionFactory = $salesResourceModelOrderCollectionFactory;
        $this->customerconnectErpMappingErporderstatusFactory = $customerconnectErpMappingErporderstatusFactory;
        $this->salesResourceModelOrderInvoiceCollectionFactory = $salesResourceModelOrderInvoiceCollectionFactory;
        $this->commErpMappingPayment = $commErpMappingPayment;
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->commResourceErpMappingCardtypeCollectionFactory = $commResourceErpMappingCardtypeCollectionFactory;
        $this->commResourceCustomerErpaccountCurrencyCollectionFactory = $commResourceCustomerErpaccountCurrencyCollectionFactory;
        $this->messageUploadModelReader = $messageUploadModelReader;
        $this->messageRequestModelReader = $messageRequestModelReader;
        $this->encryptor = $encryptor;
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
     * @return \Epicor\Comm\Model\Erp\Mapping\CountryFactory
     */
    public function getCommErpMappingCountryFactory()
    {
        return $this->commErpMappingCountryFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory
     */
    public function getCommErpMappingOrderstatusFactory()
    {
        return $this->commErpMappingOrderstatusFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\PaymentFactory
     */
    public function getCommErpMappingPaymentFactory()
    {
        return $this->commErpMappingPaymentFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    public function getCommErpMappingShippingmethodFactory()
    {
        return $this->commErpMappingShippingmethodFactory;
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\Payment
     */
    public function getCommErpMappingPayment()
    {
        return $this->commErpMappingPayment;
    }

    /**
     * @return \Epicor\Comm\Model\Message\UploadFactory
     */
    public function getCommMessageUploadFactory()
    {
        return $this->commMessageUploadFactory;
    }

    /**
     * @return \Epicor\Common\Helper\Xml
     */
    public function getCommonXmlHelper()
    {
        return $this->commonXmlHelper;
    }

    /**
     * @return \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory
     */
    public function getCommErpMappingCurrencyFactory()
    {
        return $this->commErpMappingCurrencyFactory;
    }

    /**
     * @return \Epicor\Common\Model\Erp\Mapping\LanguageFactory
     */
    public function getCommonErpMappingLanguageFactory()
    {
        return $this->commonErpMappingLanguageFactory;
    }

    /**
     * @return \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Cardtype\CollectionFactory
     */
    public function getCommResourceErpMappingCardtypeCollectionFactory()
    {
        return $this->commResourceErpMappingCardtypeCollectionFactory;
    }

    /**
     * @return \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\CollectionFactory
     */
    public function getCommResourceCustomerErpaccountCurrencyCollectionFactory()
    {
        return $this->commResourceCustomerErpaccountCurrencyCollectionFactory;
    }

    /**
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory
     */
    public function getCustomerconnectErpMappingErporderstatusFactory()
    {
        return $this->customerconnectErpMappingErporderstatusFactory;
    }

    /**
     * @return \Epicor\Common\Model\MessageUploadModelReader
     */
    public function getMessageUploadModelReader()
    {
        return $this->messageUploadModelReader;
    }

    /**
     * @return \Epicor\Common\Model\MessageRequestModelReader
     */
    public function getMessageRequestModelReader()
    {
        return $this->messageRequestModelReader;
    }

    /**
     * @return \Magento\Quote\Model\Quote\AddressFactory
     */
    public function getQuoteQuoteAddressFactory()
    {
        return $this->quoteQuoteAddressFactory;
    }

    /**
     * @return \Magento\Framework\DB\TransactionFactory
     */
    public function getTransactionFactory()
    {
        return $this->transactionFactory;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public function getSalesResourceModelOrderCollectionFactory()
    {
        return $this->salesResourceModelOrderCollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    public function getSalesResourceModelOrderInvoiceCollectionFactory()
    {
        return $this->salesResourceModelOrderInvoiceCollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\Convert\OrderFactory
     */
    public function getSalesConvertOrderFactory()
    {
        return $this->salesConvertOrderFactory;
    }

    /**
     * @return \Magento\Sales\Model\Service\OrderService
     */
    public function getSalesOrderServiceFactory()
    {
        return $this->salesOrderServiceFactory;
    }

    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }

    /**
     * @return \Magento\Sales\Model\Service\InvoiceServiceFactory
     */
    public function getInvoiceServiceFactory()
    {
        return $this->invoiceServiceFactory;
    }

    /**
     * @return \Magento\Sales\Model\Order\ShipmentFactory
     */
    public function getShipmentFactory()
    {
        return $this->shipmentFactory;
    }


}