<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;

use Epicor\Comm\Model\Customer\Erpaccount;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Tests\NamingConvention\true\mixed;
use phpDocumentor\Reflection\Types\Mixed_;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const GOR_SENT_INDICATOR = 'epicor_comm_enabled_messages/gor_request/gor_sent_indicator';

    const READABLE_TO_WEEKS = 'week';
    const READABLE_TO_DAYS = 'day';
    const READABLE_TO_HOURS = 'hour';
    const READABLE_TO_MINS = 'minute';
    const READABLE_TO_SECS = 'second';
    const DAY_FORMAT_MEDIUM = 'medium';
    const DAY_FORMAT_FULL = 'long';
    const XML_PATH_ROBOTS_GENERAL_LOCALE_CODE = 'general/locale/code';
    const XML_PATH_ROBOTS_GENERAL_LOCALE_TIMEZONE = 'general/locale/timezone';

    private $_key = array(88, 241, 5, 53, 220, 134, 129, 28, 59, 121, 138, 209, 220, 222, 197, 106, 33, 143, 139, 84, 209, 60, 99, 44, 171, 228, 182, 251, 173, 24, 109, 119);
    private $_iv = array(120, 28, 75, 133, 229, 128, 38, 221, 164, 43, 246, 230, 75, 41, 210, 84);

    private $_nonErpProducts;
    private $_erpProducts;
    private $_accountCreated;

    protected $_errorSendingEmail = false;

    protected $_uomSeparator = null;

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
    protected $transportBuilder;

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

    /*
     * @var \Magento\Framework\Filesystem\Io\FileFactory
     */
    protected $ioFileFactory;

    /**
     * @var DataObjectFactory
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

     /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $catalogProductResourceModel;

     /**
     * @var \Magento\Customer\Model\Address
     */
    protected $customerAddress;

    public function __construct(
        Context $context
            )
    {
        $this->listsSessionHelper = $context->getListsSessionHelper();
        $this->commonAccessHelper = $context->getCommonAccessHelper();
        $this->checkoutSession = $context->getCheckoutSession();
        $this->storeSystemStore = $context->getStoreSystemStore();

        $this->ioFileFactory = $context->getIoFileFactory();
        $this->dataObjectFactory = $context->getDataObjectFactory();
        $this->registry = $context->getRegistry();
        $this->commMessagingHelper = $context->getCommMessagingHelper();
        $this->storeManager = $context->getStoreManager();
        $this->directoryHelper = $context->getDirectoryHelper();
        $this->adminNotificationInboxFactory = $context->getAdminNotificationInboxFactory();
        $this->transportBuilder = $context->getEmailTemplateFactory();
        $this->translateInterface = $context->getTranslateInterface();
        $this->genericFactory = $context->getGenericFactory();
        $this->request = $context->getRequest();
        $this->commMessageUploadCusFactory = $context->getCommMessageUploadCusFactory();
        $this->commHelper = $context->getCommHelper();
        $this->catalogResourceModelProductCollectionFactory = $context->getCatalogResourceModelProductCollectionFactory();
        $this->cache = $context->getCache();
        $this->directoryCountryFactory = $context->getDirectoryCountryFactory();
        $this->directoryRegionFactory = $context->getDirectoryRegionFactory();
        $this->checkoutCartFactory = $context->getCheckoutCartFactory();
        $this->customerSessionFactory = $context->getCustomerSessionFactory();
        $this->quoteQuoteFactory = $context->getQuoteQuoteFactory();
        $this->quoteResourceModelQuoteCollectionFactory = $context->getQuoteResourceModelQuoteCollectionFactory();
        $this->listsResourceListModelCollectionFactory = $context->getListsResourceListModelCollectionFactory();
        $this->customerCustomerFactory = $context->getCustomerCustomerFactory();
        $this->listsResourceListModelAddressCollectionFactory = $context->getListsResourceListModelAddressCollectionFactory();
        $this->commLocationsHelper = $context->getCommLocationsHelper();
        $this->listsFrontendProductHelper = $context->getListsFrontendProductHelper();
        $this->listsFrontendContractHelper = $context->getListsFrontendContractHelper();
        $this->resourceConfig = $context->getResourceConfig();
        $this->globalConfig = $context->getGlobalConfig();
        $this->design = $context->getDesign();
        $this->directoryList = $context->getDirectoryList();
        $this->_localeResolver = $context->getLocaleResolver();
        $this->timezone = $context->getTimezone();
        $this->erpSourceReader = $context->getErpSourceReader();
        $this->localeCurrency = $context->getLocaleCurrency();
        $this->messageManager = $context->getMessageManager();
        $this->catalogProductResourceModel = $context->getCatalogProductResource();
        $this->customerAddress = $context->getCustomerAddress();

        parent::__construct($context);
    }

    public function backtrace($display = true, $use_br = true)
    {
        $data = '';
        foreach (debug_backtrace() as $trace) {
            $data .= @$trace['file'] . ':' . @$trace['line'] . ' ' . @$trace['class'] . '::' . @$trace['function'] . ($use_br ? '<br>' : "\n");
        }

        if ($display) {
            echo '<pre>' . $data . '</pre>';
        }
        return $data;
    }

    /**
     * Send an ssync curl request
     * @param string $url
     * @param array $params
     */
    public function sendAsyncRequest($url, $params = array())
    {

        try {
            $connection = new \Zend_Http_Client();
            $adapter = new \Zend_Http_Client_Adapter_Curl();

            $connection->setUri($url);

            $adapter->setCurlOption(CURLOPT_USERAGENT, 'api');
            $adapter->setCurlOption(CURLOPT_TIMEOUT_MS, 1000);
            $adapter->setCurlOption(CURLOPT_HEADER, 0);
            $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, false);
            $adapter->setCurlOption(CURLOPT_FORBID_REUSE, true);
            $adapter->setCurlOption(CURLOPT_CONNECTTIMEOUT, 1);
            $adapter->setCurlOption(CURLOPT_DNS_CACHE_TIMEOUT, 10);
            $adapter->setCurlOption(CURLOPT_FRESH_CONNECT, true);

            foreach ($params as $key => $value) {
                $connection->setParameterPost($key, $value);
            }
            $connection->setAdapter($adapter);

            $connection->request(\Zend_Http_Client::POST);
        } catch (\Zend_Http_Client_Exception $exc) {
            $this->_logger->error($exc->getMessage());
            //its fine its surposed to do this;
        }
    }

    /**
     * Value to Boolean.
     * True = 1, yes, y, true
     *
     * False = 0, no, n, false
     *
     * if no match is found then it will return the defaultTo value
     *
     * @param mixed $value
     * @param mixed $defaultTo
     * @return bool
     */
    function toBoolean($value, $defaultTo = false)
    {

        $type = gettype($value);
        $result = $defaultTo;
        switch ($type) {
            case "bool" :
                $result = $value;
                break;

            case "string":
                if (in_array(strtolower($value), array('1', 'true', 'y', 'yes'))) {
                    $result = true;
                } elseif (in_array(strtolower($value), array('0', 'false', 'n', 'no'))) {
                    $result = false;
                }
                break;

            case "integer":
                if ($value === 1) {
                    $result = true;
                } elseif ($value === 0) {
                    $result = false;
                }
                break;
        }

        return $result;
    }

    /**
     * Returns an array of valid License Types
     *
     * array ( 'Consumer', 'Customer', 'Supplier', 'Ios', 'Android' )
     *
     * @return array
     */
    public function getValidLicenseTypes()
    {
        $valid_types = $this->registry->registry('valid_license_types');

        if (is_null($valid_types)) {
            $valid_types = array();
            $type = $this->scopeConfig->getValue('Epicor_Comm/licensing/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            switch ($type) {
                case "lics": // Send Lic message to the ERP
                    //M1 > M2 Translation Begin (Rule p2-5.5)
                    //$filename = Mage::getBaseDir() . DS . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                    $filename = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $newfileName = $this->directoryList->getPath('pub').DIRECTORY_SEPARATOR.$this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if (file_exists($newfileName) && !is_dir($newfileName)) {
                        $filename = $newfileName;
                    }
                    //M1 > M2 Translation End
                    $ini_data = array();

                    if (file_exists($filename) && !is_dir($filename)) {
                        $file_data = file_get_contents($filename);
                        $file_data = $this->decryptWithPassword($file_data, 'Epicor_Encrypt' . $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . 'violin1234', false);

                        if (strpos($file_data, '[licenseTypes]') !== false) {
                            try {
                                $ini_data = parse_ini_string($file_data, true);
                            } catch (\Exception $e) {

                            }
                        }
                    }
                    $send_lics = true;
                    $expired = false;

                    if (isset($ini_data['licenseInfo'])) {
                        $data = $this->arrayToVarian($ini_data['licenseInfo']);
                        if (
                            (!is_null($data->getExpires()) && $data->getExpires() < time()) ||
                            (!is_null($data->getErpUrl()) && $data->getErpUrl() != $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ||
                            (!is_null($data->getSiteUrl()) && $data->getSiteUrl() != $this->scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0))
                        ) {
                            $expired = true;
                        }

                        if (is_null($data->getRecheck()) || $data->getRecheck() > time())
                            $send_lics = false;

                        if ($expired) {
                            //M1 > M2 Translation Begin (Rule P2-2)
                            //Mage::getConfig()->saveConfig('Epicor_Comm/licensing/cert_file', '');
                            $this->resourceConfig->saveConfig('Epicor_Comm/licensing/cert_file', '', \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                            //M1 > M2 Translation End
                            $file = $this->ioFileFactory->create();
                            $file->open();
                            //M1 > M2 Translation Begin (Rule p2-5.5)
                            //$file->rm(Mage::getBaseDir() . DS . $filename);

                            $oldFileName = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $filename;
                            $newFileName = $this->directoryList->getPath('pub').DIRECTORY_SEPARATOR . $filename;

                            if (file_exists($newFileName)) {
                                $file->rm($newfileName);
                            }
                            if (file_exists($oldFileName)) {
                                $file->rm($oldFileName);
                            }
                            //M1 > M2 Translation End
                        }
                    }

                    if ($expired || $send_lics) {
                        $this->sendLics();
                    }

                case "ioncube": // Read IonCube Encrypted File
                    //M1 > M2 Translation Begin (Rule p2-5.5)
                    //$filename = Mage::getBaseDir() . DS . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $filename = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $newfileName = $this->directoryList->getPath('pub').DIRECTORY_SEPARATOR.$this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if (file_exists($newfileName) && !is_dir($newfileName)) {
                        $filename = $newfileName;
                    }
                    //M1 > M2 Translation End
                    if (file_exists($filename) && !is_dir($filename)) {
                        $file_data = file_get_contents($filename);
                        $file_data = $this->decryptWithPassword($file_data, 'Epicor_Encrypt' . $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . 'violin1234', false);
                        try {
                            $ini_data = parse_ini_string($file_data, true);
                        } catch (\Exception $e) {

                        }
                        if (isset($ini_data['licenseTypes'])) {
                            foreach ($ini_data['licenseTypes'] as $key => $value) {
                                if ($value == 'Y')
                                    $valid_types[] = $key;
                            }
                        }
                    }
                    break;

                case "epicor": // Read Epicor License File
                    $file_data = file_get_contents($this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                    $file_data = $this->decryptWithPassword($file_data, 'Epicor_Encrypt' . $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . 'violin1234', false);

                    try {
                        $ini_data = parse_ini_string($file_data, true);
                    } catch (\Exception $e) {

                    }
                    foreach ($ini_data['licenseTypes'] as $key => $value) {
                        if ($value == 'Y')
                            $valid_types[] = $key;
                    }
                    break;

                default:
                    //$valid_types = array('Consumer', 'Customer', 'Supplier', 'Ios', 'Android', 'Consumer_Configurator', 'Customer_Configurator');
                    break;
            }
            $this->registry->register('valid_license_types', $valid_types);

//        $license_key = Mage::getStoreConfig('Epicor_Comm/licensing/key');
//        $valid_types = array( 'Consumer', 'Customer', 'Supplier', 'Ios', 'Android', 'Consumer_Configurator', 'Customer_Configurator' );
        }

        return $valid_types;
    }

    /**
     * Processes a license change, updating any config needed due to license changing
     *
     */
    public function processLicenseChange()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //Mage::getConfig()->reinit();

        //M1 > M2 Translation End
        $types = $this->getValidLicenseTypes();

        $isConsumer = in_array('Consumer', $types);
        $isCustomer = in_array('Customer', $types);
        $isSupplier = in_array('Supplier', $types);

        if (!$isConsumer) {
            // No longer consumer licensed
            // set b2b portal to 1 and hide flag to 0

            //M1 > M2 Translation Begin (Rule P2-2)
            //Mage::getConfig()->saveConfig('epicor_b2b/registration/reg_portaltype', 1);
            //Mage::getConfig()->saveConfig('epicor_b2b/registration/reg_customer', 0);
            //Mage::getConfig()->saveConfig('epicor_b2b/registration/show_reg_portaltype', 0);
            $this->resourceConfig->saveConfig('epicor_b2b/registration/reg_portaltype', 1, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            $this->resourceConfig->saveConfig('epicor_b2b/registration/reg_customer', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            $this->resourceConfig->saveConfig('epicor_b2b/registration/show_reg_portaltype', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            //M1 > M2 Translation End

        } else {
            // Now consumer licensed
            if ($isCustomer || $isSupplier) {
                // set hide flag to 1

                //M1 > M2 Translation Begin (Rule P2-2)
                //Mage::getConfig()->saveConfig('epicor_b2b/registration/show_reg_portaltype', 1);
                $this->resourceConfig->saveConfig('epicor_b2b/registration/show_reg_portaltype', 1, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                //M1 > M2 Translation End

            } else {
                // set hide flag to 0 and b2b flag to 0

                //M1 > M2 Translation Begin (Rule P2-2)
                //Mage::getConfig()->saveConfig('epicor_b2b/registration/reg_portaltype', 0);
                //Mage::getConfig()->saveConfig('epicor_b2b/registration/show_reg_portaltype', 0);
                $this->resourceConfig->saveConfig('epicor_b2b/registration/reg_portaltype', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                $this->resourceConfig->saveConfig('epicor_b2b/registration/show_reg_portaltype', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                //M1 > M2 Translation End
            }
        }

        if ($isCustomer) {

            //M1 > M2 Translation Begin (Rule P2-2)
            //Mage::getConfig()->saveConfig('epicor_b2b/registration/show_reg_portal', 1);
            $this->resourceConfig->saveConfig('epicor_b2b/registration/show_reg_portal', 1, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            //M1 > M2 Translation End

        } else {

            //M1 > M2 Translation Begin (Rule P2-2)
            //Mage::getConfig()->saveConfig('epicor_b2b/registration/show_reg_portal', 0);
            $this->resourceConfig->saveConfig('epicor_b2b/registration/show_reg_portal', 0, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            //M1 > M2 Translation End
        }


        //M1 > M2 Translation Begin (Rule P2-5.6)
        //Mage::getConfig()->reinit();

        //M1 > M2 Translation End

    }

    public function sendLics()
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $result = $helper->requestLicense();
    }

    /**
     * Encode the given string with a password
     *
     * @param string $data
     * @param string $password
     * @return string
     */
    public function encryptWithPassword($data, $password = 'password123!', $url = true)
    {
        $safePassword = md5($password);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $safePassword, $data, MCRYPT_MODE_ECB, $safePassword);
        if ($url)
            $encoded = $this->urlEncode($encrypted);
        else
            $encoded = base64_encode($encrypted);
        return $encoded;
    }

    /**
     * Decode the given string with a password
     *
     * @param string $encoded
     * @param string $password
     * @return string
     */
    public function decryptWithPassword($encoded, $password = 'password123!', $url = true)
    {
        $safePassword = md5($password);
        if ($url)
            $decoded = $this->urlDecode($encoded);
        else
            $decoded = base64_decode($encoded);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $safePassword, $decoded, MCRYPT_MODE_ECB, $safePassword);
        $value = rtrim($decrypted, "\0");
        return $value;
    }

    /**
     * Check if one of the license types are valid
     *
     * @param mixed $license_types
     * @return bool
     */
    public function isLicensedFor($license_types = array())
    {

        $license_types = (array)$license_types;

        $licensed = false;
        $valid_license_types = $this->getValidLicenseTypes();

        foreach ($license_types as $license_type) {
            $licensed = in_array($license_type, $valid_license_types);
            if ($licensed)
                break;
        }

        return $licensed;
    }

    /** Checks if erp is a legacy erp
     */
    public function isLegacyErp()
    {
        return in_array($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), array('qds', 'xeres', 'opal', 'kopen', 'concerto', 'control'));
    }

    /**
     * Gets a Modules License
     *
     * @param string $module name
     * @return boolean
     */
    public function isModuleLicensed($module)
    {
        $licensed = true;
        $licenses = $this->getModuleLicenses($module);
        if (!is_null($licenses)) {
            $licensed = $this->isLicensedFor($licenses);
        }
        return $licensed;
    }

    /**
     * Gets a Modules License
     *
     * @param string $module name
     * @return string - License
     */
    public function getModuleLicenses($module)
    {
        $licenses = null;
        //M1 > M2 Translation Begin (Rule 4)
        //$module = Mage::getConfig()->getNode('global/license_types/' . $module);
        $module = $this->globalConfig->get('license_types/' . $module);
        //M1 > M2 Translation End
        if ($module) {
            $licenses = (array)$module->children();
        }
        return $licenses;
    }

    /**
     *
     * @param float|string $price
     * @param bool $show_currency
     * @param int $currency_code
     * @return string
     */
    public function formatPrice($price, $show_currency = true, $currency_code = null)
    {
        $precision = $this->commHelper->getProductPricePrecision();
        if ($show_currency) {
            if ($currency_code == null)
                $currency_code = $this->storeManager->getStore()->getBaseCurrencyCode();

            $installedCurrencies = explode(',', $this->scopeConfig->getValue('system/currency/installed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if (!in_array($currency_code, $installedCurrencies)) {
                $formated_price = $currency_code . $price;
            } else {
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //$formated_price = Mage::app()->getLocale()->currency($currency_code)->toCurrency($price);
                $formated_price = $this->localeCurrency->getCurrency($currency_code)->toCurrency($price, ['precision' => $precision]);
                //M1 > M2 Translation End
            }
        } else
            $formated_price = \Zend_Locale_Format::toNumber($price, ['precision' => $precision]);
        return $formated_price;
    }

    /**
     *
     * @return string
     */
    public function getCurrencySymbol($currency_code)
    {
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //return Mage::app()->getLocale()->currency($currency_code)->getSymbol();
        return $this->localeCurrency->getCurrency($currency_code)->getSymbol();
        //M1 > M2 Translation End
    }

    /**
     * Converts an amount from one currency to another
     *
     * @param float $amount - amount to convert
     * @param string $from - currency code from
     * @param string $to - currency code to
     *
     * @return string - amount localised to store currency
     */
    public function getCurrencyConvertedAmount($amount, $from, $to = null, $options = array())
    {

        if (is_null($to)) {
            $to = $from;
        }

        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$currency = Mage::app()->getLocale()->currency($to);
        $currency = $this->localeCurrency->getCurrency($to);
        //M1 > M2 Translation End
        return $currency->toCurrency($this->directoryHelper->currencyConvert($amount, $from, $to), $options);
    }

    /**
     * Converts a date / timestamp to the format specified, using magento locale dates
     *
     * @param string $timestamp
     * @param string $format
     *
     * @return string
     */
    public function getLocalDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {
        if (is_numeric($timestamp)) {
           // $timestamp = new \Datetime(date(DATE_ATOM, $timestamp));
            return date('c',$timestamp);
        }
        //// puts utc at end of +hh:mm
//            $timestamp = date(DATE_ISO8601, $timestamp);          // puts utc at end of +hhmm

        return $this->timezone->formatDate($timestamp, $format, $showTime);
    }

	/**
     * @param string $datetime
     * @return string
     */
    public function getFormattedDateTime($datetime = '')
    {
        if ($datetime == '') {
            $datetime = date('c', time());
        }

        $formattedDateTime = new \DateTime(
            date('Y-m-d\TH:i:s', strtotime($datetime)),
            new \DateTimeZone($this->scopeConfig->getValue(self::XML_PATH_ROBOTS_GENERAL_LOCALE_TIMEZONE))
        );

        return $formattedDateTime->format('Y-m-d\TH:i:sP');
    }

    public function qtyRounding($quantity, $number_of_decimals = null)
    {
        if (is_numeric($number_of_decimals) && $number_of_decimals >= 0) {
            $quantity = round($quantity, $number_of_decimals, $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/qtyroundingmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $quantity = number_format($quantity, $number_of_decimals, ".", "");
        }
        if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/truncated_trailing_zeros', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $quantity = floatval($quantity);
        }
        return $quantity;
    }

    public function truncateZero($quantity, $number_of_decimals)
    {
        if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/truncated_trailing_zeros', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $quantity = floatval($quantity);
        } else {
            $quantity = number_format($quantity, $number_of_decimals);
        }
        return $quantity;
    }

    public function getDecimalPlaces($productInfo)
    {
        if ($productInfo instanceof \Epicor\Comm\Model\Product) {
            $decimalPlaces = $productInfo->getEccDecimalPlaces();
        } else {
            $decimalPlaces = $productInfo;
        }

        if ($decimalPlaces == '') {
            $globalDecimal = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/qtydecimals', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $decimalPlaces = (!is_null($globalDecimal)) ? $globalDecimal : '';
        }
        return $decimalPlaces ?: 0;
    }

    /**
     * Creates a Collapseable Fieldset Legend Header
     *
     * usage :
     *      createCollapseableHeader('Legend Header', 'form_id', $form->getHtmlIdPrefix());
     *
     * @param string $title
     * @param string $fieldset_id
     * @param string $form_prefix
     * @return string
     */
    public function createCollapseableHeader($title, $fieldset_id, $form_prefix)
    {
        return '<div class="collapseable"><a href="#" class="open" onclick="jQuery(\'#' . $form_prefix . $fieldset_id . '\').toggle();jQuery(\'.entry-edit .entry-edit-head\').addClass(\'collapseable\');jQuery(this).toggleClass(\'open\');return false;">' . $title . '</a></div>';
    }

    /**
     * This method creates the html for a notification responce for the admin site
     * @param type $txt
     * @param type $type
     * @return type
     */
    public function showMessageHtml($txt, $type)
    {
        return '<ul class="messages"><li class="' . $type . '-msg"><ul><li>' . $txt . '</li></ul></li></ul>';
    }

    /**
     *  This method sends a message to the magento mail box.
     *  Severities are based upon
     *  Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL
     * @param string $txt
     * @param string $subject
     * @param int $severity
     */
    public function sendMagentoMessage($txt, $subject, $severity, $link = null)
    {
        $model = $this->adminNotificationInboxFactory->create();
        /* @var $model \Magento\AdminNotification\Model\Inbox */
        $model->setTitle($subject);
        $model->setDescription($txt);
        $model->setSeverity($severity);
        if ($link == null) {
            $link = ('adminhtml/notification');
        }
        $date = date('Y-m-d H:i:s');
        $model->setDateAdded($date);
        $model->setUrl($link);
        $model->save();
    }

    /**
     * Sends an Email
     * @param array $data
     * @param array $email
     * @return type
     */
    public function sendEmail($data, $email)
    {

        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData($data);

// Just use the contact form template
        $emailTemplate = $this->scopeConfig->getValue('contacts/email/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $mailTemplate = $this->transportBuilder;
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
            ->setReplyTo($data['email'])
            ->sendTransactional(
                $emailTemplate, $this->scopeConfig->getValue('contacts/email/sender_email_identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), $email, null, array('data' => $dataObject)
            );

        return $mailTemplate->getSentSuccess();
    }
    /*
     * This is added in order to send a success/failure response to the calling function
     */
    public function sendTransactionalEmailWithResponse($template, $from, $to, $name, $vars, $storeId = null)
    {
        $this->sendTransactionalEmail($template, $from, $to, $name, $vars, $storeId = null);
        return $this->_errorSendingEmail ? false : true;
    }

    /**
     * Sends an Email
     * @param array $data
     * @param array $email
     * @return type
     */
    public function sendTransactionalEmail($template, $from, $to, $name, $vars, $storeId = null)
    {
        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData($vars);

        $translate = $this->translateInterface;
        $translate->suspend();

        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        /* older code of Magento 1 version commented */
        /*
        $mail = $this->transportBuilder->create();
        // @var $mailTemplate Mage_Core_Model_Email_Template
        $mail->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $template, $from, $to, $name, $data
            );
        */
        try {
            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
            $mail = $this->transportBuilder->setTemplateIdentifier($template)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($vars)
                            ->setFrom($from)
                            ->addTo($to)
                            ->getTransport();
            $mail->sendMessage();
            $translate->resume();
           // return $mail->getSentSuccess();
        } catch (\Exception $e) {
            $this->_errorSendingEmail = true;
            $translate->resume();

        }

    }

    function strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }
    /**
     * Shows a notification to the site visitor
     * @param string $txt
     * @param string $type
     */
    public function showNotification($txt, $type, $msgType = '')
    {
        $sendAjaxErrorMsg = false;
        $dontSendErrorMsg = array('crqu', 'crqc');
        switch ($type) {
            case 'Success':
                $this->genericFactory->create()->addSuccess($txt);
                break;
            case 'Error':
                if (strtolower($msgType) == 'bsv') {
                    #Mage::throwException($txt);
                    #die(json_encode(array(Mage::app()->getRequest()->getControllerName(), Mage::app()->getRequest()->getActionName())));
                    if ($this->strposa($this->request->getOriginalPathInfo(), ['shipping-information','payment-information','SaveBranchInformation']) !== false) {
                        if(!empty($txt)){
                             $this->registry->register('bsv_quote_error', $txt);
                        }
                        throw new \Exception($txt);
                    }
                    $this->messageManager->addNoticeMessage($txt);
                } else {
                    if (in_array(strtolower($msgType), $dontSendErrorMsg)) {
                        $sendAjaxErrorMsg = true;
                        break;
                    }
                    $this->messageManager->addNoticeMessage($txt);
                }
                break;
            case 'Notice':
                if (in_array(strtolower($msgType), $dontSendErrorMsg)) {
                    $sendAjaxErrorMsg = true;
                    break;
                }
            default:
                $this->messageManager->addNoticeMessage($txt);
        }
        if ($sendAjaxErrorMsg) {
            $this->registry->unregister('message_error');
            $error = array('message_type' => $type, 'text' => $txt);
            $this->registry->register('message_error', $error);
        }
    }

    /**
     * Strips the non printable ascii chars form the given string to avoid problems in old erp systems
     * @param String $string
     * @return String Processed String
     */
    public function stripNonPrintableChars($string)
    {
        return preg_replace('/[^\p{L}0-9\-@.\x00-\x09\x0B\x0C\x0E-\|\xB0-\xFF]/u', ' ', $string);
    }

    /**
     * check if scheduled time is due to run
     *
     * @param string $frequency
     * @param string $time
     * @param string | array $day
     * @param string | array $date
     */
    public function time2Run($frequency, $time, $days, $dates, $lastRun = null)
    {
        if ($lastRun == null)
            $lastRun = strtotime('-2 years');

        $runNow = false;
        $nextRunTimes = array();

        switch ($frequency) {
            case 'daily':
                $nextRunTimes[] = strtotime($time);
                break;

            case 'weekly':
                $days = explode(',', $days);
                if (!is_array($days))
                    $days = array($days);

                foreach ($days as $day) {
                    if ($day == date('l'))
                        $nextRunTimes[] = strtotime('this ' . $day . ' ' . $time);

                    $nextRunTimes[] = strtotime('next ' . $day . ' ' . $time);
                }
                break;

            case 'monthly':
                $dates = explode(',', $dates);
                if (!is_array($dates))
                    $dates[] = $dates;
                $months[] = date('F Y');
                $months[] = date('F Y', strtotime('+1 month'));
                foreach ($months as $month) {
                    foreach ($dates as $date) {
                        $nextRunTimes[] = strtotime($date . ' ' . $month . ' ' . $time);
                    }
                }
                break;
        }
        $lowerTime = strtotime('- 5 minutes');
        $upperTime = strtotime('+ 5 minutes');
        foreach ($nextRunTimes as $nextRunTime) {
            if ($nextRunTime > $lowerTime &&
                $nextRunTime < $upperTime &&
                $nextRunTime > strtotime('+12 hours', $lastRun)
            ) {
                $runNow = true;
                break;
            }
        }

        return $runNow;
    }

    /**
     *
     * @param int|string $time
     * @param string $suffix
     * @param bool $auto_adjust
     * @param string $lowest_unit
     * @return string
     */
    function readableTimeDiff($time, $auto_adjust = true, $lowest_unit = self::READABLE_TO_SECS)
    {
        if (is_string($time))
            $time = strtotime($time);

        $now = time();
        if ($now >= $time) {
            $time_diff = $now - $time;
            $suffix = 'ago';
        } else {
            $time_diff = $time - $now;
            $suffix = 'remaining';
        }

        if ($auto_adjust) {
            if ($time_diff >= 7 * 24 * 3600) {
                if ($suffix == 'ago')
                    //M1 > M2 Translation Begin (Rule 32)
                    //return $this->getLocalDate($time, self::DAY_FORMAT_MEDIUM);
                    return $this->getLocalDate($time, \IntlDateFormatter::MEDIUM);
                //M1 > M2 Translation End
                else
                    $lowest_unit = self::READABLE_TO_WEEKS;
            } elseif ($time_diff > 24 * 3600)
                $lowest_unit = self::READABLE_TO_DAYS;
            elseif ($time_diff > 3600)
                $lowest_unit = self::READABLE_TO_HOURS;
            elseif ($time_diff > 60)
                $lowest_unit = self::READABLE_TO_MINS;
            else
                $lowest_unit = self::READABLE_TO_SECS;
        }

        $time_ago = $this->readableTime($time_diff, $lowest_unit);

        //M1 > M2 Translation Begin (Rule 55)
        //return $this->__("%s $suffix", $time_ago);
        return __("%1 $suffix", $time_ago);
        //M1 > M2 Translation End
    }

    /*
     * Convert seconds to human readable text.
     *
     * @param int $secs
     */

    function readableTime($secs, $lowest_unit = self::READABLE_TO_SECS)
    {
        $units = array(
            "week" => 7 * 24 * 3600,
            "day" => 24 * 3600,
            "hour" => 3600,
            "minute" => 60,
            "second" => 1,
        );

// specifically handle zero
        if ($secs == 0)
            return "0 {$lowest_unit}s";

        $s = "";

        foreach ($units as $name => $divisor) {
            $quot = intval($secs / $divisor);
            if ($quot || $name == $lowest_unit) {
                $secs -= $quot * $divisor;

                if ($name == $lowest_unit && $secs > ($units[$name] / 2))
                    $quot++;

                //M1 > M2 Translation Begin (Rule 55)
                /*if (abs($quot) > 1)
                    $s .= $this->__("%s {$name}s", $quot);
                else
                    $s .= $this->__("%s $name", $quot);*/
                if (abs($quot) > 1)
                    $s .= __("%1 {$name}s", $quot);
                else
                    $s .= __("%1 $name", $quot);
                //M1 > M2 Translation End

                $s .= ", ";
            }
            if ($name == $lowest_unit)
                break;
        }

        return substr($s, 0, -2);
    }

    public function bytesToHuman($bytes)
    {
        $symbols = array('B', 'Kb', 'Mb', 'Gb', 'Tb');
        $exp = floor(log($bytes) / log(1024));

        return sprintf('%.2f ' . $symbols[$exp], $bytes / pow(1024, floor($exp)));
    }

    public function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
// The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Check for any GORs that have not been sent successfully
     */
    public function setPhpMemoryLimits()
    {
        // Allow for greater memory usage
        // Change collection to use a simlar method to Mage_ImportExport_Model_Export_Entity_Product::export
        ini_set('memory_limit', '512M');
    }

    /**
     * Check for any GORs that have not been sent successfully
     */
    public function setPhpTimeLimits()
    {
        //Execution time may be very long00
        set_time_limit(14400);
    }

    /**
     * Returnes whether the request is admin or not
     *
     * @return boolean
     */
    public function isAdmin()
    {
        //M1 > M2 Translation Begin (Rule 31)
        //if ($this->storeManager->getStore()->isAdmin()) {
        if ($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            return true;
        }
        //M1 > M2 Translation End

        //M1 > M2 Translation Begin (Rule p2-5.4)
        //if (Mage::getDesign()->getArea() == 'adminhtml') {
        if ($this->design->getArea() == 'adminhtml') {
            //M1 > M2 Translation End
            return true;
        }

        return false;
    }

    public function convertStringToCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    /**
     * Converts multidimensional array to multidimentioanl Varien Object
     *
     * @param array $array
     */
    public function arrayToVarian($array)
    {
        $var_obj = $this->dataObjectFactory->create();
        foreach ($array as $key => $value) {

            if (is_array($value))
                $value = $this->arrayToVarian($value);

            $var_obj->setData($key, $value);
        }
        return $var_obj;
    }

    /**
     * Converts multidimensional Varien Object to multidimentional array
     *
     * @param \Magento\Framework\DataObject / array $var_obj
     */
    public function varienToArray($var_obj)
    {
        $array = array();

        $data = ($var_obj instanceof \Magento\Framework\DataObject) ? $var_obj->getData() : $var_obj;

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $array[$key] = $this->varienToArray($value);
        }

        return $array;
    }

    /**
     * Checks if a customer can edit/create addresses.
     *
     * @param string $type        Permission type check.
     * @param string $level       Level.
     * @param string $addressType Address type.
     *
     * @return boolean
     */
    public function customerAddressPermissionCheck(
        string $type,
        string $level='customer',
        string $addressType='shipping'
    ) {
        $allowed    = false;
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $erpGroupId = $erpAccount->getId();
        $default    = $this->scopeConfig->getValue(
            'customer/create_account/default_erpaccount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$erpGroupId || $default == $erpGroupId) {
            $allowed = true;
        } else {
            if ($type == 'create') {
                $actionName = $this->request->getModuleName().'_'.$this->request->getControllerName()
                    .'_'.$this->request->getActionName();

                if ($actionName !== 'checkout_index_index') {
                    $shippingAllowed = $this->getAddressAllowed($erpAccount, $level, 'shipping');
                    $billingAllowed  = $this->getAddressAllowed($erpAccount, $level, 'billing');
                    $allowed         = $shippingAllowed || $billingAllowed;
                } else {
                    $allowed = $this->getAddressAllowed($erpAccount, $level, $addressType);
                }
            }
        }

        return $allowed;

    }//end customerAddressPermissionCheck()


    public function splitProductCode($code)
    {
        $uomSeparator = $this->getUOMSeparator();
        $arr = explode($uomSeparator, $code);
        if ($arr === false) {
            $arr = array($code, '');
        } elseif (count($arr) < 2) {
            $arr[1] = null;
        }

        return $arr;
    }

    public function stripProductCodeUOM($code)
    {
        $arr = $this->splitProductCode($code);
        return $arr[0];
    }

    public function getUOMSeparator()
    {
        if($this->_uomSeparator === null){
            $this->_uomSeparator = $this->scopeConfig->getValue('Epicor_Comm/units_of_measure/separator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (empty($this->_uomSeparator)) {
            $this->_uomSeparator = $this->scopeConfig->getValue('Epicor_Comm/units_of_measure/separator_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            }
        }
        return $this->_uomSeparator;
    }

    /**
     * check uom has a special characters
     * @param $sku
     *
     * @return bool
     */
    public function checkUomHasSpecialCharacter($sku)
    {
        $uomSeparator = $this->getUOMSeparator();
        $uomArray     = explode($uomSeparator, $sku);
        if ((is_array($uomArray) && !empty($uomArray[1]))
            && (preg_match('/[^a-zA-Z\d]/', $uomArray[1]))) {
                return true;
        }

        return false;
    }

    public function removeUOMSeparator($sku)   // this will remove the uom from the supplied $sku eg xxx123yyy will return xxx yyy
    {

        return $this->getSku($sku) . " " . $this->getUom($sku);
    }

    public function getSku($sku)
    {
        $skuArray = $this->splitProductCode($sku);
        return $skuArray[0];
    }

    public function getUom($sku)
    {
        $skuArray = $this->splitProductCode($sku);
        if ($skuArray[1]) {
            return $skuArray[1];
        } else {
            $product = $this->catalogResourceModelProductCollectionFactory->create()->addAttributeToSelect('ecc_default_uom')
                ->addAttributeToFilter('sku', array('eq' => $sku))
                ->load()
                ->getFirstItem();

            return $product->getEccDefaultUom();
        }
    }

    public function safeString($str, $spacer = '_')
    {
        return preg_replace('([\W])', '', str_replace('_', $spacer, str_replace(' ', $spacer, strtolower($str))));
    }

    public function setErpDefaults($newErp, $scopeId = 0, $mapping_data = false)
    {

        $scope = ($scopeId) ? 'stores' : 'default';

        //M1 > M2 Translation Begin (Rule 4)
        //$erps = (array) Mage::getConfig()->getNode("global/erps");
        $erps = (array)$this->globalConfig->get("erps");
        //M1 > M2 Translation End
// create nested loops to process all data in retrieved xml
        if (isset($erps[$newErp])) {
            $erp = $erps[$newErp];                                       // locate chosen erp

            //M1 > M2 Translation Begin (Rule P2-2)
            //$config = Mage::getConfig();
            $config = $this->resourceConfig;
            //M1 > M2 Translation End

            foreach ($erp as $path_part1 => $value2) {
                switch (true) {
                    case $path_part1 == 'mappingData' && $mapping_data:
                        foreach ($value2 as $mappingType) {
                            // LOGIC TO ERASE ALL RECORDS ON THE MAPPING TABLE
                            $collection = $this->erpSourceReader->getModel($mappingType['source'])->getCollection()->getItems();
                            foreach ($collection as $item) {
                                $item->delete();
                            }
                            $records = json_decode($mappingType['data'], true);
                            $fields = explode(',', $mappingType['fields']);

                            foreach ($records as $record) {
                                $model = $this->erpSourceReader->getModel($mappingType['source']);
                                foreach ($fields as $key => $field) {
                                    $model->setData($field, $record[$key]);
                                }
                                $model->save();
                                $model->unsetData();
                            }
                        }
                        break;
                    case $path_part1 != 'mappingData' && !$mapping_data:
                        foreach ($value2 as $path_part2 => $value3) {
                            if (is_array($value3)) {
                                foreach ($value3 as $path_part3 => $value4) {
                                    $path = $path_part1 . "/" . $path_part2 . "/" . $path_part3;
                                    $config->saveConfig($path, $value4, $scope, $scopeId);    // update each config path specified in array with default value
                                }
                            }
                        }
                        break;
                }
            }
        }
        $this->cache->clean(array('CONFIG', 'LAYOUT_GENERAL_CACHE_TAG'));
    }

    private function getKey()
    {
        return implode(array_map("chr", $this->_key));
    }

    private function getIv()
    {
        return implode(array_map("chr", $this->_iv));
    }

    public function eccEncode($data)
    {
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($data) % $block);
        $data .= str_repeat(chr($pad), $pad);

        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->getKey(), $data, MCRYPT_MODE_CBC, $this->getIv());
        return base64_encode($encrypted);
    }

    public function eccDecode($data)
    {
        $data = base64_decode($data);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->getKey(), $data, MCRYPT_MODE_CBC, $this->getIv());
        $value = rtrim($decrypted, "\x00..\x1F");
        return $value;
    }

    /**
     * Checks whether the provided currency code is a valid magento currency
     *
     * @param string $currencyCode
     *
     * @return boolean
     */
    public function isCurrencyCodeValid($currencyCode)
    {
        try {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$currency_from_system = Mage::app()->getLocale()->currency($currencyCode)->getShortName();
            $currency_from_system = $this->localeCurrency->getCurrency($currencyCode)->getShortName();
            //M1 > M2 Translation End
            $result = ($currency_from_system != $currencyCode) ? false : true;
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function removeDelimiter($account)
    {
        $delimiter = $this->getUomSeparator();
        $revisedAccount = implode(' ', explode($delimiter, $account));
        return $revisedAccount;
    }

    /**
     * Returns the Region Id (if applicable) for the given county & country
     *
     * @param string $countryCode
     * @param string $county
     *
     * @return integer
     */
    public function getRegionFromCountyName($countryCode, $county)
    {
        $region = false;

        try {
            $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
            /* @var $countryModel Mage_Directory_Model_Country */

            if (!empty($countryModel) || !$countryModel->isObjectNew()) {
                $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                    ->addCountryFilter($countryModel->getId())
                    ->load();
                /* @var $collection Mage_Directory_Model_Resource_Region_Collection */

                // Check to see if the country has regions, and check if it's valid
                if ($collection->count() > 0) {
                    // try loading a region with the county field as the code
                    $region = $this->directoryRegionFactory->create()->loadByCode($county, $countryModel->getId());
                    /* @var $region Mage_Directory_Model_Region */

                    if (empty($region) || $region->isObjectNew()) {
                        // try loading a region with the county field as the name
                        $region = $this->directoryRegionFactory->create()->loadByName($county, $countryModel->getId());

                        if (empty($region) || $region->isObjectNew()) {
                            $region = false;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $region = false;
        }

        return $region;
    }

    public function getCountryCodeForDisplay($countryCode)
    {
        try {
            $helper = $this->commMessagingHelper;
            $displayCountryCode = $helper->getCountryCodeMapping($countryCode, $helper::ERP_TO_MAGENTO);
        } catch (\Exception $e) {
            $displayCountryCode = $countryCode;
        }

        return $displayCountryCode;
    }

    public function getCountryName($countryCode)
    {
        try {
            $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
            /* @var $countryModel Mage_Directory_Model_Country */
            $name = $countryModel->getName();
        } catch (\Exception $e) {
            $name = $countryCode;
        }

        return $name;
    }

    public function getCurrentlyLicensedFor()
    {
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$filename = Mage::getBaseDir() . DS . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $filename = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $newFileName = $this->directoryList->getPath('pub').DIRECTORY_SEPARATOR.$this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(file_exists($newFileName)){
            $filename = $newFileName;
        }
        //M1 > M2 Translation End
        $row = '';
        if ((!is_dir($filename)) && (file_exists($filename))) {

            $hideSettings = array(
                'Android',
                'Ios',
            );

            $translations = array(
                'Consumer' => __('Consumer Connect'),
                'Customer' => __('Customer Connect'),
                'Supplier' => __('Supplier Connect'),
                'Ios' => __('Mobile Gateway for iOS'),
                'Android' => __('Mobile Gateway for Android'),
                'Consumer_Configurator' => __('Consumer Product Configurator'),
                'Customer_Configurator' => __('Customer Product Configurator'),
                'Dealer_Portal' => __('Dealer Portal'),
            );
            $valid_types = array();
            $file_data = file_get_contents($filename);
            $file_data = $this->decryptWithPassword($file_data, 'Epicor_Encrypt' . $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . 'violin1234', false);

            $license = explode('[licenseInfo]', $file_data);
            $licenseInfo = substr($license[0], 15);
            preg_match_all('/([A-Za-z].*?)=([Y]|[N])/', $licenseInfo, $info);

            $licenseInfoArray = array();

            if (count($info) == 3 && isset($info[0]) && !empty($info[0])) {
                foreach ($info[1] as $x => $typeName) {
                    $licenseInfoArray[$typeName] = $info[2][$x];
                }

                foreach ($licenseInfoArray as $name => $value) {
                    if (!in_array($name, $hideSettings)) {
                        $value = (strtoupper($value) == 'Y') ? 'Y' : 'N';
                        $row .= "<span class='license_name'>"
                             . (isset($translations[$name]) ? $translations[$name] : $name)
                             . "</span><span class='license_value_{$value}'></span><br />";
                    }
                }
            } else {
                $row = __('Error Reading License, please apply a valid License');
            }
        }
        return $row;
    }

    /**
     * Converts a inputted date / timestamp to the specified format
     *
     * @param string $dateToConvert
     * @param string $format
     * @param string $type (date, time, datetime)
     * @return string
     */
    public function getFormattedInputDate($dateToConvert, $format, $type = 'date')
    {
        switch ($type) {
            case 'date':
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //$inputFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $inputFormat = $this->timezone->getDateFormat(\IntlDateFormatter::SHORT);
                //M1 > M2 Translation End
                break;

            case 'time':
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //$inputFormat = Mage::app()->getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $inputFormat = $this->timezone->getTimeFormat(\IntlDateFormatter::SHORT);
                //M1 > M2 Translation End
                break;

            case 'datetime':
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //$inputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $inputFormat = $this->timezone->getDateTimeFormat(\IntlDateFormatter::SHORT);
                //M1 > M2 Translation End
                break;
        }
        $formattedDate = '';

        if (!empty($dateToConvert)) {
            try {
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //$date = new \Zend_Date($dateToConvert, $inputFormat, Mage::app()->getLocale()->getLocaleCode());
                $date = new \Zend_Date($dateToConvert, $inputFormat, $this->_localeResolver->getLocale());
                //M1 > M2 Translation End
                $formattedDate = $date->toString($format);
            } catch (\Exception $ex) {
                $formattedDate = $dateToConvert;
            }
        }

        return $formattedDate;
    }

    /**
     * Checks to see if a given error message is alread in the sessionf or the session type
     *
     * @param string $sessionType
     * @param string $error
     *
     * @return boolean
     */
    public function errorExists($error)
    {
         $exists = false;
        //M1 > M2 Translation Begin (Rule p2-5.1)
        //$messages = Mage::getSingleton($sessionType)->getMessages()->getItems();
         $messages = $this->messageManager->getMessages()->getErrors();
        //M1 > M2 Translation End
        foreach ($messages as $message) {

            if ($error == $message->getText()) {
                $exists = true;
            }
        }

        return $exists;
    }

    /**
     * Checks to see if a given warning message is already in the session or the session type
     *
     * @param string $sessionType
     * @param string $error
     *
     * @return boolean
     */
    public function warningExists($warning)
    {
         $exists = false;
         $messages = $this->messageManager->getMessages()->getItemsByType('warning');

        foreach ($messages as $message) {
            if (strcmp($warning, $message->getText()) ==0) {
                $exists = true;
            }
        }

        return $exists;
    }

    /**
     * Taken from magento's own adminhtml js helper
     * with the numeric restriction removed
     *
     * @param string $encoded
     * @return array
     */
    public function decodeGridSerializedInput($encoded)
    {
        $isSimplified = (false === strpos($encoded, '='));
        $result = array();
        $decoded = $this->parse_qs($encoded);
        $decoded = is_array($decoded) ? $decoded : array();
        foreach ($decoded as $key => $value) {
            if ($isSimplified) {
                $result[] = $key;
            } else {
                $result[$key] = null;
                parse_str(base64_decode($value), $result[$key]);
            }
        }
        return $result;
    }

    public function parse_qs($data)
    {
        $data = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $data);

        parse_str($data, $values);
        if (!empty($values)) {
            //hex2bin() is PHP5.4+
            if (!function_exists('hex2bin')) {
                return array_combine(array_map('Self::hextobin', array_keys($values)), $values);
            } else {
                return array_combine(array_map('hex2bin', array_keys($values)), $values);
            }
        }
    }

    //The function hex2bin does not exist in PHP5.3 (hex2bin() is PHP5.4+)
    public function hextobin($hexstr)
    {
        $n = strlen($hexstr);
        $sbin = "";
        $i = 0;
        while ($i < $n) {
            $a = substr($hexstr, $i, 2);
            $c = pack("H*", $a);
            if ($i == 0) {
                $sbin = $c;
            } else {
                $sbin .= $c;
            }
            $i += 2;
        }
        return $sbin;
    }

    public function convertIso8601DateNoLocale($date, $format)
    {
        return date($format, strtotime($date));
    }

    public function wipeCart()
    {
        $cart = $this->checkoutCartFactory->create();
        /* @var $cart \Epicor\Comm\Model\Cart */
        $this->registry->unregister('dont_send_bsv');
        $this->customerSessionFactory->create()->setCartMsqRegistry(array());
        $this->customerSessionFactory->create()->setBsvTriggerTotals(array());
        $this->registry->register('dont_send_bsv', true, true);

        if ($cart->getQuote()->getId()) {
            $this->clearOldActiveBaskets();
            $newQuote = $this->quoteQuoteFactory->create();
            /* @var $newQuote \Epicor\Comm\Model\Quote */
            $newQuote->setStore($this->storeManager->getStore());
            $cart->setQuote($newQuote);
            //$cart->init();
            $cart->save();
        }
    }

    public function clearOldActiveBaskets()
    {
        $customerSession = $this->customerSessionFactory->create();
        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $quotes = $this->quoteResourceModelQuoteCollectionFactory->create();
        /* @var $quotes Mage_Sales_Model_Resource_Quote_Collection */
        $quotes->addFieldToFilter('is_active', array('eq' => 1))
            ->addFieldToFilter('customer_id', $customer->getId());

        foreach ($quotes->getItems() as $quote) {
            $quote->delete();
        }
    }

    /**
     * Loads Current Customer Specific Lists & Filter  by Customer Id and type filter
     */
    public function customerListsById($customerId, $typeFilter)
    {
        if ($customerId) {
            $collection = $this->listsResourceListModelCollectionFactory->create();
            /* @var $collection Epicor_Lists_Model_Resource_List_Collection */
            $filter = $typeFilter;
            $collection->$filter();
            $collection->filterActive();
            $collection->filterByCustomer($customerId);
            return $collection->toArray();
        }
    }

    /**
     * Get Selected Customer Address for the particular Contract by customer id and address id (dropdown)
     *
     * @return array
     */
    public function customerSelectedAddressById($addressId, $customerId)
    {
        if ($customerId)
            $loadHelper = $this->customerListAddressesById($addressId, $customerId);
        $customerData = $this->customerCustomerFactory->create()->load($customerId);
        $defaultContractAddress = $customerData->getEccDefaultContractAddress();
        $select['type'] = 'success';
        $select['html'] = '<select name="account[ecc_default_contract_address]" id="_accountecc_default_contract_address" class="select absolute-advice">';
        $select['html'] .= '<option value="">No Default Address</option>';
        if ($loadHelper) {
            foreach ($loadHelper as $code => $address) {
                $defaultSelect = ($code == $defaultContractAddress ? "selected=selected" : "");
                $select['html'] .= '<option value="' . $code . '" ' . $defaultSelect . '>' . $address->getName() . '</option>';
            }
        }
        $select['html'] .= '</select>';
        return $select;
    }

    /**
     * Customer Address by Id and customer Id
     *
     * @return array $items
     */
    public function customerListAddressesById($listId, $customerId)
    {
        /* @var $collection Epicor_Lists_Model_Resource_List_Address_Collection */
        $collection = $this->listsResourceListModelAddressCollectionFactory->create();
        $collection->getSelect()->join(
            array('list' => $collection->getTable('ecc_list_customer')), 'list.customer_id = ' . $customerId . ' AND list.list_id = main_table.list_id', array()
        );
        $collection->addFieldtoFilter('main_table.list_id', $listId);
        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        return $items;
    }

    /**
     * Perform location filtering
     *
     * @return array $items
     */
    public function performLocationProductFiltering($collection)
    {
        $helper = $this->commLocationsHelper;
        /* @var $helper Epicor_Comm_Helper_Locations */
        if ($helper->isLocationsEnabled()) {
            if (
                $collection->getFlag('no_product_filtering') ||
                $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection
            ) {
                return $collection;
            }

            $locationTable = $collection->getTable('ecc_location_product');
            $locationString = $helper->getEscapedCustomerDisplayLocationCodes();
            $collection->getSelect()->where(
                '(SELECT COUNT(*) FROM ' . $locationTable . ' AS `locations` WHERE locations.product_id = e.entity_id AND locations.location_code IN (' . $locationString . ')) > 0'
            );
            $this->registry->unregister('location_sql_applied');
            $this->registry->register('location_sql_applied', true);
        }
        return $collection;
    }

    /**
     * Perform lists/contract filtering
     *
     * @return array $items
     */
    public function performContractProductFiltering($collection)
    {
        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        if (
            $helper->listsDisabled() ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('lists_sql_applied')
        ) {
            return $collection;
        }

        if ($helper->hasFilterableLists() || $contractHelper->mustFilterByContract()) {
            $productIds = $helper->getActiveListsProductIds();
            $collection->getSelect()->where(
                '(e.entity_id IN(' . $productIds . '))'
            );
        }

        $collection->setFlag('lists_sql_applied', true);
        return $collection;
    }

    /**
     * Search for the directory, if not found tries to create it, if not makable returns false
     *
     * @param string $directory
     * @return bool
     */
    public function validateOrCreateDirectory($directory, $mode = 0777)
    {
        if (!is_dir($directory)) {
            mkdir($directory, $mode, true);
            if (!is_dir($directory)) {
                return false;
            }
        }

        return true;
    }


    public function getAutohideCategories()
    {
        return $this->scopeConfig->isSetFlag('epicor_common/catalog_navigation/auto_hide', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the selected branch details from session
     * @return selected location code
     */
    public function checkBranchSession()
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        return $sessionHelper->getValue('ecc_selected_branchpickup');
    }

    /**
     * Generates a cacheKey for the current customer & config combination
     *
     * @return string
     */
    public function getCategoryCacheKeys()
    {
        $regCache = $this->registry->registry('ecc_category_cache_keys');
        if ($regCache) {
            return $regCache;
        }

        $cacheKey = $this->hasCategoryAccess() ? 'yes' : 'no';

        if ($cacheKey == 'no') {
            $this->registry->unregister('ecc_category_cache_keys');
            $this->registry->register('ecc_category_cache_keys', array($cacheKey));
            return array($cacheKey);
        }

        $autoHideEnabled = $this->getAutohideCategories();

        if ($autoHideEnabled == false) {
            return array();
        }

        $cacheKeys = array(
            $cacheKey
        );

        // Add cache key for Lists product filtering
        $listHelper = $this->listsFrontendProductHelper;
        /* @var $listHelper Epicor_Lists_Helper_Frontend_Product */
        if ($listHelper->listsEnabled()) {
            $listProductIds = $listHelper->getActiveListsProductIds();
            $listProductKey = ($listProductIds == 0) ? 'none' : $listProductIds;
            $cacheKeys[] .= 'LISTSPRODUCTS' . md5($listProductKey);
        }

        $contractsHelper = $this->listsFrontendContractHelper;
        /* @var $contractsHelper Epicor_Lists_Helper_Frontend_Contract */
        if ($contractsHelper->contractsEnabled()) {
            $contractKey = $contractsHelper->getSelectedContractCode();
            $cacheKeys[] .= 'CONTRACT' . md5($contractKey);
        }

        // Add cache key for Locations product filtering

        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $locEnabled = $locHelper->isLocationsEnabled();
        if ($locEnabled) {
            $locations = $locHelper->getCustomerDisplayLocationCodes();
            $cacheKeys[] .= 'LOCATIONS' . md5(implode('_', $locations));
        }

        $this->registry->unregister('ecc_category_cache_keys');
        $this->registry->register('ecc_category_cache_keys', $cacheKeys);

        return $cacheKeys;
    }

    protected function hasCategoryAccess()
    {
        $accessHelper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */

        return $accessHelper->canAccessUrl('catalog/category/view');
    }

    /**
     * Checks whether addresses should be restricted
     *
     * @return boolean
     */
    public function restrictAddressTypes()
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        $force = $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($force == false && $helper->contractsEnabled()) {
            $quote = $this->checkoutSession->getQuote();
            /* @var $quote Epicor_Comm_Model_Quote */

            $contracts = $helper->getQuoteContracts($quote);
            if (empty($contracts) == false) {
                $force = true;
            }
        }

        return $force;
    }

    public function getAllStoresFormatted()
    {
        $store = $this->storeSystemStore;
        $stores = $store->getStoreValuesForForm(false, true);

        // replace first entry returned with '', so that validation will occur
        if (isset($stores[0])) {
            $stores[0]['value'] = '';
        }

        return $stores;
    }
    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Check if an array is associative
     * @param array $arr
     * @return boolean
     */

    function isAssoc(array $arr)
    {
        foreach (array_keys($arr) as $key) {
            if (!is_int($key)) {
                return true;
            } else {
                return false;
            }
        }
    }
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }
    public function getEncryptor(){
        return $this->encryptor;
    }

    public  function getDealerMode()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $helper->getErpAccountInfo();
        $getAccountType = $erpAccount->getAccountType();
        $types = array('Dealer','Distributor');
        if(in_array($getAccountType, $types)) {
          return true;
        } else {
          return false;
        }
    }

    public function checkDealerLicense() {
        $dealerLicense = $this->isLicensedFor(array('Dealer_Portal'));
        return $dealerLicense;
    }

    /*
     * get customer order ref validation fields
     */
    public function cusOrderRefValidation()
    {
        $cusOrderRefMaxLength = $this->getMaxPoLength();
        $cusOrderRefMaxLength = $cusOrderRefMaxLength ? $cusOrderRefMaxLength : 50;
        $cusOrderRefValidation = ['min_text_length' => 1, 'max_text_length' => $cusOrderRefMaxLength];
        if ($this->isPoValidationRequired()) {
            $cusOrderRefValidation = array('required-entry' => true) + $cusOrderRefValidation;
        }
        return $cusOrderRefValidation;
    }

    private function getMaxPoLength()
    {
        return $this->scopeConfig->getValue('checkout/options/max_po_length', ScopeInterface::SCOPE_STORE);
    }

    private function isPoValidationRequired()
    {
        if (!$this->isErpAccountPoMandatoryGlobal()) {
            return $this->getErpAccountPoMandatorySetting();
        }
        return $this->isCheckoutGlobalPoMandatoryRequired();
    }

    private function isCheckoutGlobalPoMandatoryRequired(): bool
    {
        return (boolean)$this->scopeConfig->getValue('checkout/options/po_mandatory', ScopeInterface::SCOPE_STORE);
    }

    private function getErpAccountPoMandatorySetting()
    {
        $erpAccount = $this->commHelper->getErpAccountInfo();

        return $erpAccount->getPoMandatory();
    }

    private function isErpAccountPoMandatoryGlobal(): bool
    {
        return is_null($this->getErpAccountPoMandatorySetting());
    }

    public function retrieveNonErpProductsInCart($customerDetails = null, $message = false, $source = 'checkout')
    {
        $optionsArray = array(
            'proxy' => 'has placed an order that contains non ERP products. The proxy was used to create the order.',
            'checkoutproxy' => 'has placed an order that contains non ERP products. The proxy was used to create the order.',
            'quoteproxy' => 'has created a quote that contains non ERP products. The proxy was used to create the quote.',
            'request' => 'has a cart which contains non ERP products and cannot checkout.',
            'checkoutrequest' => 'has a cart which contains non ERP products and cannot checkout.',
            'quoterequest' => 'has a cart which contains non ERP products and cannot create a quote.',
            'erpcreate' => 'has a cart which contains non ERP products. The ERP needed to create the product.'
        );
        $options = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $toEmail = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/administration_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $messageArray = false;
        //if  this email not set, use general email
        if (!$toEmail) {
            $toEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $request = false;
        $this->_accountType = $this->customerSessionFactory->create()->isLoggedIn() ? 'Customer' : 'Guest';
        //if call has come from a message
        if ($message) {
            if (is_object($customerDetails)) {
                $messageArray = $customerDetails->getMessageArray();
                $requiredAddress = isset($messageArray['messages']['request']['body']['invoiceAddress']) ? $messageArray['messages']['request']['body']['invoiceAddress'] : $messageArray['messages']['request']['body']['quote']['deliveryAddress'];
                $this->getCustomerDetails($requiredAddress,
                                          true);
            }
        } else {
            //if call is not from a message, but from capture details
            $jsonData = json_decode($customerDetails, true);
            $street = '';
            $actionType = '';
            if ($jsonData) {

                foreach ($jsonData as $key => $data) {
                    //don't process form key or action_type
                    if (isset($data['name']) && in_array($data['name'], array('action_type', 'form_key'))) {
                        continue;
                    }
                    //don't bother if password is present or register checkbox
                    if (isset($data['name']) && in_array($data['name'], array('capturedetails[register]', 'capturedetails[customer_password]', 'capturedetails[confirm_password]'))) {
                        if ($data['name'] == 'capturedetails[register]') {
                            if ($data['value']) {
                                $this->_accountCreated = true;
                            }
                        }
                        continue;
                    }
                    preg_match_all("/\[(.*?)\]/", $data['name'], $match);
                    $matchValue = $match[1][0];
                    if(!$matchValue){
                        continue;
                    }
                    if(!$matchValue){
                        continue;
                    }
                    if ($matchValue == 'street') {
                        $street .= empty($data['value']) ? '' : $data['value'] . ',';
                        $data['value'] = $street;
                    }

                    if ($matchValue == 'region_id') {
                        if ($data['value']) {
                            $regionCode = $this->directoryRegionFactory->create()->load($data['value'])->getDefaultName();
                            $data['value'] = $regionCode;
                        } else {
                            continue;
                        }
                    }

                    if ($matchValue == 'country_id') {
                        if ($data['value'] != 'US') { // GJ - This is wrong!
                            unset($this->_address['region_id']);
                        }
                    }

                    if ($matchValue == 'email') {
                        $this->_customerEmail = $data['value'];
                        continue;
                    }

                    if ($matchValue == 'name') {
                        $this->_customerName = $data['value'];
                        continue;
                    }

                    if ($matchValue == 'telephone') {
                        $this->_customerTelephone = $data['value'];
                        continue;
                    }
                    if ($key == 'action_type' || $data['name'] == 'action_type') {
                        $actionType = isset($data['name']) ? $data['value'] : $data;
                    } else {
                        $this->_address[$matchValue] = rtrim($data['value'], ',');
                    }

                }
                if (count($jsonData) <= 1) {
                    $this->getCustomerDetails(false, false);
                }
            } else {
                $this->getCustomerDetails(false, false);
            }
        }
        $this->getNonErpProductItems($this->registry->registry('message_type'), $messageArray);
        //if no non erp products in cart/order , don't send email
        if (empty($this->_nonErpProducts)) {
            return;
        }

        if ($options == 'proxy' && in_array($this->registry->registry('message_type'), array('GQR', 'CRQU'))) {
            $options = 'quoteproxy';
        }
        if ($options == 'request') {
            $options = $actionType . 'request';
            $request = true;
        }

        $customerValue = $this->_accountType == 'Customer' ? null: 'customer';
        $body = "<div>{$this->_accountType} {$customerValue} {$this->_customerName} {$optionsArray[$options]}</div>";

        $customerValue = $this->_accountType == 'Customer' ? null: 'customer';
        $body = "<div>{$this->_accountType} {$customerValue} {$this->_customerName} {$optionsArray[$options]}</div>";

        if ($this->_accountCreated) {
            // if a new account was requested but email already exists say so
            if(!$this->registry->registry('customer_already_exists')){
                $body .= "</br><div>A new Customer account has been created</div></br>";
            }else{
                $body .= "</br><div>A new Customer account was requested, but the email already exists, so the request was ignored</div></br>";
            }
        }

        $orderNumber = $this->registry->registry('GOR_order_number');
        $erpOrderNumber = $this->registry->registry('GOR_ERP_order_number');

        if ($orderNumber) {
            $body .= "</br><div>Order Number: {$orderNumber}</div></br>";
        }

        if ($erpOrderNumber) {
            $body .= "</br><div>ERP Order Number: {$erpOrderNumber}</div></br>";
        }

        $body .= "</br><div>Email: {$this->_customerEmail}</div>";
        $body .= "</br><div>Telephone: {$this->_customerTelephone}</div></br>";
        foreach ($this->_address as $key => $add) {
            $content = $key . ':' . ' ' . $add;
            $body .= "<div>{$content}</div>";
        }

        $productPlural = count($this->_nonErpProducts) > 1 ? 's' : '';
        $body .= "</br><div>Non ERP Product{$productPlural}</div>";
        $body .= "<div>==============</div>";

        foreach ($this->_nonErpProducts as $key => $product) {
            $body .= "</br><div>Name: " . $product['name'] . "</div><div>SKU: " . $key . "</div><div>QTY: " . $product['qty'] . "</div><div>ID: " . $product['id'] . "</div><div>Price: " . $product['price'] . '</div>';
        }

        if ($this->_erpProducts) {
            $body .= "</br><div>Rest of Cart</div>";
            $body .= "<div>=========</div>";
            foreach ($this->_erpProducts as $key => $product) {
                $body .= "</br><div>Name: " . $product['name'] . "</div><div>SKU: " . $key . "</div><div>QTY: " . $product['qty'] . "</div><div>ID: " . $product['id'] . "</div><div>Price: " . $product['price'] . '</div>';
            }
        }

        if ($request) {
            $body .= "</br><div>Please contact them to discuss the issue.</div>";
        }

        $subject = $source == 'rfq' ? __('Non Erp Products in Quote') : __('Non Erp Products in Cart');

        if ($source == 'checkout') {
           $this->clearCheckoutOfNonErpItems();
        }
        $this->sendCustomEmail($subject, $toEmail, $body);
    }

    /**
     *
     * @param boolean $requiredAddress
     * @param boolean $message
     */
    private function getCustomerDetails($requiredAddress, $message)
    {

        $customerSession = $this->customerSessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            $customer = $customerSession->getCustomer();
            $this->_customerName = $customer->getName();
            $this->_customerEmail = $customer->getEmail();
            $this->_customerTelephone = $customer->getTelephoneNumber();
            if ($customer->getErpaccountId()) {
                $this->_accountType = $this->commHelper->getErpAccountInfo($customer->getErpaccountId())->getAccountType();
            }
            if (!$requiredAddress) {
                if($customer->getDefaultBillingAddress()){
                    $requiredAddress = $customer->getDefaultBillingAddress()->getData();
                }
            }
        } else {
            $this->_customerName = $requiredAddress['contactName'];
            $this->_customerEmail = $requiredAddress['emailAddress'];
            $this->_customerTelephone = $requiredAddress['telephoneNumber'];
        }

        $this->_address['company'] = @$requiredAddress['name'];
        $this->_address['company'] = !isset($this->_address['company']) ? @$requiredAddress['company'] : $this->_address['company'];
        if (isset($requiredAddress['street'])) {
            $this->_address['street'] = $requiredAddress['street'];
        } else {
            $streetArray = array();
            $streetArray[0] = $requiredAddress['address1'];
            if (isset($requiredAddress['address2'])) {
                $streetArray[1] = $requiredAddress['address2'];
            }
            if (isset($requiredAddress['address3'])) {
                $streetArray[2] = $requiredAddress['address3'];
            }
            $this->_address['street'] = implode(', ',
                                                $streetArray);
        }
        $this->_address['city'] = $requiredAddress['city'];
        $this->_address['region'] = array_key_exists('county', $requiredAddress) ? $requiredAddress['county']: '';
        $this->_address['region'] = array_key_exists('region', $requiredAddress) ? $requiredAddress['region'] : $this->_address['region'];
        $this->_address['countryId'] = isset($requiredAddress['country']) ? $requiredAddress['country'] : $requiredAddress['country_id'];
        $this->_address['postcode'] = $requiredAddress['postcode'];
    }

    /**
     * Clears the cart of non ERP items
     */
    public function clearCheckoutOfNonErpItems()
    {
        $this->productsInCart(null, true);
    }
     /**
     *
     * @param  $message
     * @param boolean $messageArray
     */
    private function getNonErpProductItems($message, $messageArray = false ){


        if($message){
            switch ($message) {
                case 'CRQU':
                     //could pick these from message, but they are saved in registry anyway
                    $this->productSkusNotOnCart($this->registry->registry('message_product_skus'));
                    break;
                case 'GOR':
                    $this->productSkusNotOnCart($this->registry->registry('message_product_skus'));
                    break;
                default:
                    $this->productsInCart();
                    break;
            }
        }else{
            //if no message and product sku registry populated use that, else use cart,
            if($this->registry->registry('rfq_product_skus')){
                $this->productSkusNotOnCart();
            }else{
                $this->productsInCart();
            }
        }
    }
     /**
     *
     * @param array  $productList
     * @param boolean $messageArray
     */
    private function productSkusNotOnCart($productList = null){
        if(!$productList){
            $productList = JSON_decode($this->registry->registry('rfq_product_skus'), true);
            $productSku = array();
            foreach($productList as $list){
                $productSku[$list['name']] = array('qty'=>$list['qty'], 'value'=>$list['value']);
            }
            $productList = $productSku;
        }
        foreach ($productList as $key=>$item) {
            $catalogResourceModelProduct = $this->catalogProductResourceModel;
            $itemarray = explode('<br>', $key);
            $sku =  trim($itemarray[0]);
            $storeId = $this->storeManager->getStore()->getId();
            $item['id'] = $this->catalogProductResourceModel->getIdBySku($sku);
            $productStkType = $this->catalogProductResourceModel->getAttributeRawValue($item['id'], 'ecc_stk_type', $storeId);
            $productTypeId = $this->catalogProductResourceModel->getAttributeRawValue($item['id'], 'type_id', $storeId);
            $productName = $this->catalogProductResourceModel->getAttributeRawValue($item['id'], 'name', $storeId);
            if (!$productStkType && $productTypeId['type_id'] != 'configurable') {
                $this->_nonErpProducts[$key] = array('qty' => $item['qty'], 'price' => $item['value'], 'id' => $item['id'], 'name' => $productName);
            } else {
                $this->_erpProducts[$key] = array('qty' => $item['qty'], 'price' => $item['value'], 'id' => $item['id'], 'name' => $productName);
            }
        }
    }
    private function productsInCart($cart = null, $remove = false){
         //get product list from cart
        if(!$cart){
            $cart = $this->checkoutSession->getQuote();
        }
        foreach ($cart->getAllItems() as $item) {
            $storeId = $this->storeManager->getStore()->getId();
            $productSku = $this->catalogProductResourceModel->getAttributeRawValue($item['product_id'], 'sku', $storeId);

            //this is odd. Sku returns an array, the others return a value
            $productSku = is_array($productSku) ? $productSku['sku'] : $productSku;
            $productStkType = $this->catalogProductResourceModel->getAttributeRawValue($item['product_id'], 'ecc_stk_type', $storeId);
            $productTypeId = $this->catalogProductResourceModel->getAttributeRawValue($item['product_id'], 'type_id', $storeId);
            $productName = $this->catalogProductResourceModel->getAttributeRawValue($item['product_id'], 'name', $storeId);

            if (!$productStkType && $productTypeId['type_id'] != 'configurable') {
                $this->_nonErpProducts[$productSku] = array('qty' => $item->getQty(), 'price' => $item->getPrice(), 'id' => $item->getProductId(), 'name' => $productName);
                if ($remove) {
                    $item->delete();
                }
            } else {
                $this->_erpProducts[$productSku] = array('qty' => $item->getQty(), 'price' => $item->getPrice(), 'id' => $item->getProductId(), 'name' => $productName);
            }
        }

        if ($remove) {
            $this->registry->register("non_erp_parts_deleted", true);
            $cart->save();
        }
    }

    public function sendCustomEmail($subject, $toEmail, $body)
    {
        $ccUser = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/cc_email_to_user', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mail = new \Zend_Mail();
        $mail->setBodyHtml($body)
            ->setFrom('nonErpProducts@epicor.com', 'admin')
            ->addTo($toEmail)
            ->setSubject($subject);

        if ($ccUser) {
            $mail->addCc($this->_customerEmail);
        }

        $mail->send();
    }

    public function eccNonErpProductsActive() {
        return $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /*
     * Check if the cart contains non erp products, return true or false
     */
    public function cartContainsNonErpProducts() {
        $options = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$enabled) {
            return false;
        }

        if ($options == 'request') {
            $nonErpItemsInCart = false;
            $cart = $this->checkoutCartFactory->create()->getQuote()->getItemsCollection();;
            foreach ($cart as $item) {
                if (!$this->catalogProductResourceModel->getAttributeRawValue($item->getProductId(), 'ecc_stk_type', $this->storeManager->getStore()->getId())) {
                    //only flag as non erp product if not a configurable product
                    $productTypeId = $this->catalogProductResourceModel->getAttributeRawValue($item->getProductId(), 'type_id', $this->storeManager->getStore()->getId());
                    if ($productTypeId['type_id'] != 'configurable'){
                        return true;
                    }
                }
            }
        }
        return false;
    }
    /*
     * determine if a product is non-erp
     */

    public function isProductNonErp($productId) {
      //  if (!$this->catalogResourceModelProductFactory->create()->getAttributeRawValue($productId, 'ecc_stk_type', $this->storeManager->getStore()->getId())) {
        if (!$this->catalogProductResourceModel->getAttributeRawValue($productId, 'ecc_stk_type', $this->storeManager->getStore()->getId())) {
            return true;
        }
    }

    public function saveCustomerDetails($customerDetails){
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $store = $this->storeManager->getStore();

        $jsonData = json_decode($customerDetails, true);
        $customerData = array();
        $street = '';

        foreach ($jsonData as $key => $data) {
            //don't create new account if not required
            if($data['name'] == 'capturedetails[register]' && empty($data['value'])){
                return;
            }
            //don't bother if password is present or register checkbox
            if (in_array($data['name'], array('action_type', 'capturedetails[confirm_password]', 'form_key'))) {
                continue;
            }
            preg_match_all("/\[(.*?)\]/", $data['name'], $match);
            $matchValue = $match[1][0];
            if ($matchValue == 'street') {
                if (!empty($data['value'])) {
                    if ($street) {
                        $street .= ',' . $data['value'];
                    } else {
                        $street = $data['value'];
                    }
                }
                $data['value'] = $street;
            }
            if($matchValue == 'name'){
                //split name into first and last name
                $nameArray = array_values(array_filter(explode(' ', $data['value'])));
                $customerData['firstname'] = $nameArray[0];
                $customerData['lastname'] = isset($nameArray[1]) ? $nameArray[1] : '';
            }
            $customerData[$matchValue] = $data['value'];
        }

        $customer = $this->customerCustomerFactory->create();
        $customer->setWebsiteId($websiteId)
                    ->setStore($store);
        $customer->loadByEmail($customerData['email']);
        if($customer->getName() != ' '){
        //don't try to add if customer already exists
            $this->registry->register('customer_already_exists', true);
            return;
        }

        $customer->setFirstname($customerData['firstname'])
                 ->setLastname($customerData['lastname'])
                 ->setEmail($customerData['email'])
                 ->setPassword($customerData['customer_password']);
        try {
            $customer->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
        $address = $this->customerAddress;
        $address->setCustomerId($customer->getId())
                ->setFirstname($customer->getFirstname())
                ->setMiddleName($customer->getMiddlename())
                ->setLastname($customer->getLastname())
                ->setCountryId($customerData['country'])
                ->setPostcode($customerData['postcode'])
                ->setCity($customerData['city'])
                ->setTelephone($customerData['telephone'])
                ->setFax($customerData['fax'])
                ->setCompany($customerData['company'])
                ->setStreet($customerData['street'])
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            if($customerData['country'] == 'US'){
                $address->setRegionId($customerData['region_id']);
            }
        try {
            $address->save();
        } catch (\Zend_Http_Client_Exception $exc) {
            $this->_logger->error($exc->getMessage());
            //its fine its surposed to do this;
        }
    }
    public function getCommHelper(){
        return $this->commHelper;
    }
    public function getCommLocationsHelper(){
        return $this->commLocationsHelper;
    }

    /**
     * To get Products actual UOM, used in BSV, GOR and GQR.
     *
     * @param array $uomArr UOM array.
     * @param mixed $item   Order / Quote item.
     *
     * @return string
     */
    public function getProductUom(array $uomArr, $item)
    {
        if ($item->getProductType() === TypeConfigurable::TYPE_CODE) {
            $option = $item->getOptionByCode('simple_product');
            if (!empty($option)) {
                $uomCode = $option->getProduct()->getEccDefaultUom();
            } else {
                $uomCode = $this->getUom($uomArr[0]);
            }

            $uomCode = $uomCode ?? $item->getProduct()->getEccDefaultUom();
            return $uomArr[1] ?? $uomCode;
        } else {
            return $uomArr[1] ? $uomArr[1] : $item->getProduct()->getEccDefaultUom();
        }
    }


    /**
     * Get ERP address allowed value.
     *
     * @param mixed  $erpAccount Erp Account default.
     * @param string $type       Address type.
     *
     * @return boolean|null
     */
    public function getErpAddressAllowed($erpAccount, string $type='shipping')
    {
        $erpAllowed     = $erpAccount->getData($type.'_address_allowed');
        $erpAllowedEval = ($erpAllowed == null || $erpAllowed == 2);

        if ($erpAllowedEval) {
            return null;
        } else {
            return $erpAllowed;
        }

    }//end getErpAddressAllowed()


    /**
     * Get address allowed.
     *
     * @param mixed  $erpAccount Erp Account default.
     * @param string $level      Level.
     * @param string $type       Address type.
     *
     * @return boolean
     */
    public function getAddressAllowed($erpAccount, string $level='customer', string $type='shipping')
    {
        if (is_null($erpAccount)) {
            $erpAccount = $this->commHelper->getErpAccountInfo();
        }
        $customerMessageModel = $this->commMessageUploadCusFactory->create();
        $customerSession      = $this->customerSessionFactory->create();
        $customer             = $customerSession->getCustomer();

        $customerCustomAddressAllowed = ($level == 'customer') ? $customer->isCustomAddressAllowed($type) : null;
        $erpCustomAddressAllowed      = $this->getErpAddressAllowed($erpAccount, $type);
        $globalConfigValue            = $customerMessageModel->getConfigFlag('cus_create_addresses_'.$type);

        $allowed = $customerCustomAddressAllowed == null ?
            ($erpCustomAddressAllowed == null ?
                $globalConfigValue : $erpCustomAddressAllowed) : $customerCustomAddressAllowed;

        return $allowed;

    }//end getAddressAllowed()


    /**
     * Get data object factory.
     *
     * @return DataObjectFactory
     */
    public function getDataObjectFactory()
    {
        return $this->dataObjectFactory;

    }//end getDataObjectFactory()


}
