<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


use Magento\Quote\Model\Quote\Address;

class Messaging extends \Epicor\Comm\Helper\Data
{

    const MAGENTO_TO_ERP = 'm2e';
    const ERP_TO_MAGENTO = 'e2m';
    const COMPANY_LIMIT = 'customer/address/limit_company_length';
    const DEFAULT_ADDRESS_CODE = 'epicor_comm_enabled_messages/global_request/default_address_code';

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

    /***
     * @var \Magento\Sales\Model\Service\InvoiceServiceFactory
     */
    protected $invoiceServiceFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context
    )
    {
        $this->shipmentFactory = $context->getShipmentFactory();
        $this->invoiceServiceFactory = $context->getInvoiceServiceFactory();
        $this->salesOrderServiceFactory = $context->getSalesOrderServiceFactory();
        $this->salesConvertOrderFactory = $context->getSalesConvertOrderFactory();
        $this->commMessageUploadFactory = $context->getCommMessageUploadFactory();
        $this->commonXmlHelper = $context->getCommonXmlHelper();
        $this->commErpMappingCountryFactory = $context->getCommErpMappingCountryFactory();
        $this->commErpMappingOrderstatusFactory = $context->getCommErpMappingOrderstatusFactory();
        $this->commErpMappingPaymentFactory = $context->getCommErpMappingPaymentFactory();
        $this->commErpMappingCurrencyFactory = $context->getCommErpMappingCurrencyFactory();
        $this->commonErpMappingLanguageFactory = $context->getCommonErpMappingLanguageFactory();
        $this->commErpMappingShippingmethodFactory = $context->getCommErpMappingShippingmethodFactory();
        $this->transactionFactory = $context->getTransactionFactory();
        $this->salesResourceModelOrderCollectionFactory = $context->getSalesResourceModelOrderCollectionFactory();
        $this->customerconnectErpMappingErporderstatusFactory = $context->getCustomerconnectErpMappingErporderstatusFactory();
        $this->salesResourceModelOrderInvoiceCollectionFactory = $context->getSalesResourceModelOrderInvoiceCollectionFactory();
        $this->quoteQuoteAddressFactory = $context->getQuoteQuoteAddressFactory();
        $this->commResourceErpMappingCardtypeCollectionFactory = $context->getCommResourceErpMappingCardtypeCollectionFactory();
        $this->commResourceCustomerErpaccountCurrencyCollectionFactory = $context->getCommResourceCustomerErpaccountCurrencyCollectionFactory();
        $this->messageUploadModelReader = $context->getMessageUploadModelReader();
        $this->messageRequestModelReader = $context->getMessageRequestModelReader();
        $this->encryptor = $context->getEncryptor();
        parent::__construct($context);
    }

    /**
     * Function to standarize date string before it is passed to other functions (like strtotime)
     *
     * @param string $date
     * @return string $date
     */
    public function dateStandarize($date)
    {
        // Important note about strtotime php function
        // Dates in the m/d/y or d-m-y formats are disambiguated by looking at the separator between the various components: if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed.
        if (strpos($date, '/') !== false) {
            // if found replace '/' with '.' to make it in European format
            $date = str_replace('/', '.', $date);
        }

        return $date;
    }

    /**
     * Get Message Upload Object
     *
     * @param string $messageType
     * @return mixed
     */
    public function getUploadMessageObject($messageType, $messageBase = null)
    {
        $message_Comm = null;
        if ($messageBase === null) {
            $messageBase = $this->commMessageUploadFactory->create();
        }
        try {
            $base = null;
//            M1 > M2 Translation Begin (Rule 4)
//            $messageConfig = Mage::getConfig()->getXpath('global/xml_message_types/upload/' . $messageType );
//            if (!empty($messageConfig)) {
//                $messageConfig = array_pop($messageConfig);
//                /* @var $messageConfig Mage_Core_Model_Config_Element */
//                if (property_exists($messageConfig, 'base')) {
//                    $base = (string)$messageConfig->base;
//                }
//            }

            $messageConfig = $this->globalConfig->get('xml_message_types/upload/'.$messageType);
            if (isset($messageConfig['base']) && $messageConfig['base']) {
                $base = $messageConfig['base'];
            }
//           M1 > M2 Translation End

            if (!empty($base)) {
                //M1 > M2 Translation Begin (Rule 46)
                //$message_Comm = Mage::getModel($base . '/message_upload_' . $messageType);
                $message_Comm = $this->messageUploadModelReader->getModel($base, $messageType);
                //M1 > M2 Translation End
            } else if (strlen($messageType) <= 3 || in_array(strtoupper($messageType), array('CREU', 'CRRC', 'FREQ', 'FSUB'))) {
                //$message_Comm = $this->commMessageUploadFactory->create();
                $message_Comm = $this->messageUploadModelReader->getModel('epicor_comm',$messageType);
            } else if (strlen($messageType) > 3) {
                if ($messageType[0] == 's') {
                    //$message_Comm = $this->supplierconnectMessageUploadFactory->create();
                    $message_Comm = $this->messageUploadModelReader->getModel('supplierconnect',$messageType);
                } else if ($messageType[0] == 'c') {
                    //$message_Comm = $this->customerconnectMessageUploadFactory->create();
                    $message_Comm = $this->messageUploadModelReader->getModel('customerconnect',$messageType);
                }
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());exit;
        }
        $message = $message_Comm ?: $messageBase;

        $message->setMessageId($messageBase->getMessageId());
        $message->_xml_in_log = $messageBase->_xml_in_log;
        return $message;
    }

    /**
     * Process Xml Message
     *
     * @param string $messageXml
     * @param bool $checkValidation
     * @return \Magento\Framework\DataObject
     */
    public function processSingleMessage($messageXml, $checkValidation = false)
    {

        $this->registry->register('SkipEvent', true);

        $message = $this->commMessageUploadFactory->create();
        /* @var $message \Epicor\Comm\Model\Message\Upload */

        $message->setInXml($messageXml);
        $response = $this->dataObjectFactory->create();

        $response->setData(array("is_successful" => false, "msg" => "Invalid User Access", "xml_response" => "Invalid User Access", "is_authorized" => false, "is_valid_xml" => false));
        if ($checkValidation && !$this->validUpload()) {
            return $response;
        }
        $response->setIsAuthorized(true);

        $xmlHelper = $this->commonXmlHelper;
        try {

            $messageObj = $xmlHelper->convertXmlToVarienObject($message->getInXml());

            if ($messageObj === false || !$messageObj->getMessages() ||
                !$messageObj->getMessages()->getRequest() ||
                !$messageObj->getMessages()->getRequest()->getData('_attributes')
            ) {
                $message->setStatusDescription("Invalid Xml (100)");
                $message->processUpload();
            } else {
                $response->setIsValidXml(true);

                $message->setMessageType($messageObj->getMessages()->getRequest()->getData('_attributes')->getType());
                $message->setMessageId($messageObj->getMessages()->getRequest()->getData('_attributes')->getId());
                $messageType = strtolower($message->getMessageType());

                $message = $this->getUploadMessageObject($messageType, $message);
                /* @var $message \Epicor\Comm\Model\Message\Upload */
                if ($message->isActive(null, true)) {
                    $waited = 0;
                    $message->enterProcessQueue();
                    $messageQueue = $this->scopeConfig->getValue('Epicor_Comm/queue_message/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    while ((!$message->readyToProcess() && $waited < $message->getTimeout()) || (!$message->readyToProcess() && !$messageQueue)) {
                        $waited++;
                        sleep(1);
                    }
                    if ($message->readyToProcess()) {
                        $message->process($messageXml, $messageObj);
                    } else {
                        $message->setStatusDescription($message->getErrorDescription($message::STATUS_GENERAL_ERROR, 'Timed out waiting in queue after ' . $message->getTimeout() . ' seconds'));
                        $message->setStatusCode($message::STATUS_GENERAL_ERROR);
                        $message->processUpload();
                    }
                    $message->leaveProcessQueue();
                } else {
                    if ($message->isLicensed()) {
                        $message->setStatusDescription($message->getErrorDescription($message::STATUS_MESSAGE_NOT_SUPPORTED, $message->getMessageType()));
                        $message->setStatusCode($message::STATUS_MESSAGE_NOT_SUPPORTED);
                    } else {
                        $message->setStatusDescriptionText($message->getErrorDescription($message::STATUS_ERP_LICENSE_REQUIRED));
                        $message->setStatusCode($message::STATUS_ERP_LICENSE_REQUIRED);
                    }
                    $message->processUpload();
                }
            }
        } catch (\Exception $e) {
            $message->setStatusDescription("Invalid Xml (101)");
            $message->processUpload();
        }

        $response->setMsg($message->getStatusDescription());
        $response->setIsSuccessful($message->isSuccessfulStatusCode());
        $response->setType($message->getMessageType());
        $response->setId($message->getMessageId());
        $response->setXmlResponse($message->getSentXml());

        $this->eventManager->dispatch(strtolower($message->getMessageType()) . '_upload_complete_after', array(
            'data_object' => $this,
            'message' => $message,
        ));

        $this->registry->unregister('SkipEvent');
        return $response;
    }

    /**
     * Convert Varien Key to Xml Tag <br>
     * e.g <br>
     *    my_key => myKey<br>
     *    custom_discounted_tax_price => customDiscountedTaxPrice
     *
     * @param type $key
     * @return type
     */
    public function varienKeyToXmlTag($key)
    {
        return preg_replace_callback(
            '/([a-z0-9])_([a-z])/', function($matches) {
            return $matches[1] . strtoupper($matches[2]);
        }, $key
        );

        // alternative method would have been
//      return reg_replace('/([a-z0-9])_([a-z])/e', "'\\1'.strtoupper('\\2')", $key);
    }

    /**
     * Convert Varien Key to Xml Tag <br>
     * e.g <br>
     *    my_key => myKey<br>
     *    custom_discounted_tax_price => customDiscountedTaxPrice
     *
     * @param type $key
     * @return type
     */
    public function xmlTagToVarienKey($tag)
    {

        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $tag));
    }

    /**
     *
     * @param string $url
     * @param string $user
     * @param string $pass
     * @return string
     */
    public function connectionTest($url = null, $user = null, $pass = '******')
    {
        $result = 'Unknown Error';

        $this->getConnectionDetails($url, $user, $pass);

        try {
            $msg = $this->getHeartBeatMessage('Network Test');
            $msg->setApiUser($user);
            $msg->setUrl($url);
            $msg->setApiPassword($pass);

            if ($msg->sendMessage())
                $result = 'true';
            elseif ($msg->http_status_code == 401)
                $result = __('Invalid Username / Password');
            else
                $result = $msg->getStatusDescription();
        } catch (\Exception $e) {
            $result = __('Error Occured - ') . $e->getMessage();
        }

        return $result;
    }

    private function getConnectionDetails(&$url = null, &$user = null, &$pass = '******')
    {

        $url = $url ?: $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $user = $user ?: $this->scopeConfig->getValue('Epicor_Comm/licensing/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($pass == '******' || is_null($pass))
            $pass = $this->encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    /**
     *
     * @param string $url
     * @param string $user
     * @param string $pass
     * @return string
     */
    public function requestLicense($url = null, $user = null, $pass = '******')
    {
        $result = __('Failed to retrieve License');

        $this->getConnectionDetails($url, $user, $pass);

        try {
            $msg = $this->commMessageRequestLicsFactory->create();
            if ($msg->isActive(null, false, true)) {
                $msg->setApiUser($user);
                $msg->setUrl($url);
                $msg->setApiPassword($pass);

                if ($msg->sendMessage()) {
                    $data = "[licenseTypes]\n";
                    foreach ($msg->getLicenseTypes() as $key => $value) {
                        $data .= "$key=$value\n";
                    }
                    $data .= "\n[licenseInfo]\n";
                    $data .= "erp_url=$url\n";
                    $data .= "site_url=" . $this->scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0) . "\n";
                    $data .= "recheck=" . strtotime('+7days') . "\n";
                    $data .= "expires=" . strtotime('+14days') . "\n";

                    // $data = chunk_split(base64_encode($data));
                    $data = chunk_split($this->encryptWithPassword($data, 'Epicor_Encrypt' . $url . 'violin1234', false), 32);

                    // Old License Path not allowed to save license path on root folder
                    //$licensePath = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . 'ecc.lic';
                    // New License Path inside pub directory
                    $licensePath = $this->directoryList->getPath('pub') . DIRECTORY_SEPARATOR . 'ecc.lic';

                    //M1 > M2 Translation Begin (Rule p2-5.5)
                    //if (file_put_contents(Mage::getBaseDir() . DS . 'ecc.lic', $data) === false)
                    if (file_put_contents($licensePath, $data) === false)
                        //M1 > M2 Translation End
                        $result = __("Failed to save License file");
                    else {
                        //M1 > M2 Translation Begin (Rule p2-5.5)
                        //chmod(Mage::getBaseDir() . DS . 'ecc.lic', 0660);
                        chmod($licensePath, 0660);
                        //M1 > M2 Translation End

                        //M1 > M2 Translation Begin (Rule P2-2)
                        //Mage::getConfig()->saveConfig('Epicor_Comm/licensing/cert_file', 'ecc.lic');
                        $this->resourceConfig->saveConfig('Epicor_Comm/licensing/cert_file', 'ecc.lic', \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                        //M1 > M2 Translation End

                        $result = __("License Saved");
                        //M1 > M2 Translation Begin (Rule P2-5.6)
                        //Mage::getConfig()->reinit();

                        //M1 > M2 Translation End
                    }
                }
            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * Gets the P21 token from the specified url
     *
     * @param string $url
     * @param string $user
     * @param string $pass
     *
     * @return string
     */
    public function getP21Token($url = null, $user = null, $pass = '******')
    {
        $result = __('Unknown Error');

        $url = $url ?: $this->scopeConfig->getValue('Epicor_Comm/licensing/p21_token_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $user = $user ?: $this->scopeConfig->getValue('Epicor_Comm/licensing/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($pass == '******' || is_null($pass)) {
            $pass = $this->encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        }

        try {
            $connection = new \Zend_Http_Client();
            $adapter = new \Zend_Http_Client_Adapter_Curl();
            $connection->setUri($url);
//$adapter->setCurlOption(CURLOPT_URL, $this->url);
            $adapter->setCurlOption(CURLOPT_HEADER, FALSE);
            if ($this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/curlproxy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $adapter->setCurlOption(CURLOPT_PROXY, $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/curlproxy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            }
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, FALSE);
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, FALSE);
            $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 1);

// post options
            $adapter->setCurlOption(CURLOPT_POST, 1);
            $adapter->setCurlOption(CURLOPT_TIMEOUT, 20000);

            $connection->setAdapter($adapter);
            $connection->setConfig(array('strict' => false));

            $connection->setHeaders('username', $user);
            $connection->setHeaders('client_secret', $pass);
            $connection->setHeaders('grant_type', 'client_credentials');

            $response = $connection->request(\Zend_Http_Client::POST);

            $helper = $this->commonXmlHelper;
            $obj = $helper->convertXmlToVarienObject($response->getBody());

            if ($response->getStatus() == '200') {
                if ($obj->getData('act:token') instanceof \Magento\Framework\DataObject) {
                    $token = $obj->getData('act:token');
                    if ($token->getAccessToken()) {
                        $result = __('Token retrieved successfully');
                        //M1 > M2 Translation Begin (Rule P2-2)
                        //Mage::getConfig()->init()->saveConfig('Epicor_Comm/licensing/p21_token', $token->getAccessToken());
                        $this->resourceConfig->saveConfig('Epicor_Comm/licensing/p21_token', $token->getAccessToken(), \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                        //M1 > M2 Translation End

                    } else {
                        $result = __('No token received in response from ERP');
                    }
                } else {
                    $result = __('Could not retrive token');
                }
            } else {
                $result = __('Connection error, status returned was: ' . $response->getStatus());
            }
        } catch (\Exception $e) {
            $result = __('Error Occured - ') . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get the Message to be used as the Heart Beat Message
     *
     * @param string $sessionId
     * @return \Epicor\Comm\Model\Message\Request
     */
    public function getHeartBeatMessage($sessionId)
    {

        $msg_type = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/connection_test_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //$msg = $this->commMessageRequestFactory->create();
        $msg = $this->messageRequestModelReader->getModel('epicor_comm', $msg_type);
        /* @var $msg /Epicor/Comm/Model/Message/Request */
        $msg->setIsDeamon(true);
        $msg->setSessionId($sessionId);
        return $msg;
    }

    /**
     * Convert Magento Country Code to ERP Country Code
     *
     * @deprecated <b>Use:</b> getCountryCodeMapping($countryCode, $direction)
     * @param string $magentoCountryCode
     * @return string
     */
    public function getErpCountryCode($magentoCountryCode)
    {
        return $this->getCountryCodeMapping($magentoCountryCode);
    }

    /**
     * Convert ERP Country Code to Magento Country Code
     *
     * @deprecated <b>Use:</b> getCountryCodeMapping($countryCode, $direction)
     * @param string $magentoCountryCode
     * @return string
     * @
     */
    public function getMagentoCountryCode($erpCountryCode)
    {
        return $this->getCountryCodeMapping($erpCountryCode, self::ERP_TO_MAGENTO);
    }

    /**
     * Convert Country Code  Magento -> ERP -> Magento
     *
     * when converting ERP -> Magento, if no mapping is found and the code is not
     * a valid country code then it will default to configured default country
     * @param string $countryCode
     * @param string $direction
     * @param string $returnDefault Yes Return Default Country else True or false
     *
     * @return string
     */
    public function getCountryCodeMapping($countryCode, $direction = self::MAGENTO_TO_ERP,$returnDefault=TRUE)
    {
        $search = 'magento_id';
        $result = 'erp_code';
        $setDefault = false;

        if ($direction == self::ERP_TO_MAGENTO) {
           $search = 'erp_code';
            $result = 'magento_id';
        }

        $retcountryCode = FALSE;
        $country_mapping = $this->commErpMappingCountryFactory->create()->loadMappingByStore($countryCode, $search);
        if (trim($country_mapping->getData($result)) != null) {
            $countryCode = $country_mapping->getData($result);
                                          $retcountryCode = $countryCode;
        } elseif ($direction == self::ERP_TO_MAGENTO) {
            try {
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
                $retcountryCode = $countryModel;
                /* @var $countryModel Mage_Directory_Model_Country */
            } catch (\Exception $e) {
                if ($this->scopeConfig->getValue('epicor_comm_field_mapping/general_mapping/address_country_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    throw new \Exception('Address Country is required');
                } else {
                    $setDefault = true;
                }
            }
        }
        if(!$returnDefault){
            return $retcountryCode;
        }
        if (trim($countryCode) == null || $setDefault && $returnDefault) {
            $messagingDefault = $this->scopeConfig->getValue('epicor_comm_field_mapping/general_mapping/address_country_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $magentoStoreDefault = $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $countryCode = $messagingDefault ?: $magentoStoreDefault;
        }

        return $countryCode;
    }


    public function getOrderStatusDescription($code, $description = '')
    {
        $erp = $this->getOrderMapping($code);
        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    public function getAddressTypeMapping($code, $direction = self::ERP_TO_MAGENTO)
    {
        $address_model = $this->commCustomerErpaccountAddressFactory->create();
        /* @var $address_model Epicor_Comm_Model_Customer_Erpaccount_Address */
        $mapping_data = $address_model->getMappingData();
        if ($direction == self::MAGENTO_TO_ERP)
            $mapping_data = array_flip($mapping_data);

        $new_code = null;
        if (array_key_exists($code, $mapping_data))
            $new_code = $mapping_data[$code];

        return $new_code;
    }

    /**
     * Check for valid username and password in http headers
     *
     * @return boolean
     */
    public function validUpload()
    {
        if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'e10') {
            list($http_username, $http_password) = $this->getHttpAuthorizationCredentials();

            $api_username = $this->scopeConfig->getValue('Epicor_Comm/licensing/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $api_password = $this->encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            $valid = ($http_username == $api_username && $http_password == $api_password);
        } else {
            $valid = true;
        }

        return $valid;
        return true;
    }

    public function getHttpAuthorizationCredentials()
    {
        $credentials = array(null, null);
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $credentials = array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        } else {
            foreach ($_SERVER as $key => $value) {
                if (strpos(strtolower($key), 'authorization') !== false) {
                    $credentials = explode(':', base64_decode(substr($value, 6)));
                }
            }
        }
        return $credentials;
    }

    /**
     * Return the ERP order state / status for the given erp code.
     * @param type $erpCode
     */
    public function getOrderMapping($erpCode)
    {
        /* @var $model Epicor_Comm_Model_Erp_Mapping_Orderstatus */
        $model = $this->commErpMappingOrderstatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    public function getPaymentMethodMap($magento_code)
    {
        /* @var $model Epicor_Comm_Model_Erp_Mapping_Payment */
        $model = $this->commErpMappingPaymentFactory->create();
        $erp = $model->loadMappingByStore($magento_code, 'magento_code');

        if (!isset($erp) || $erp->getErpCode() == null) {
            $erp = $this->commErpMappingPaymentFactory->create();
            $erp->setErpCode($magento_code);
            $erp->setMagentoCode($magento_code);
            $erp->setPaymentCollected('Y');
        }
        return $erp;
    }

    /**
     * convert Currency Code from <br/> <b>Magento -> ERP</b> <br/> or <br/> <b>ERP -> Magento</b>
     *
     * If null is passed to the currencyCode then it will use the current store baseCurrencyCode
     *
     * @param string $currencyCode
     * @param string $direction
     * @return string
     */
    public function getCurrencyMapping($currencyCode = null, $direction = self::MAGENTO_TO_ERP)
    {
        $search = 'magento_id';
        $result = 'erp_code';

        if ($direction == self::ERP_TO_MAGENTO) {
            $search = 'erp_code';
            $result = 'magento_id';
        }

        if (trim($currencyCode) == null) {
            $currencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        }

        $model = $this->commErpMappingCurrencyFactory->create();
        $currency_mapping = $model->loadMappingByStore($currencyCode, $search);

        if (!is_null($currency_mapping->getData($result))) {
            $currencyCode = $currency_mapping->getData($result);
        }

        return $currencyCode;
    }

    /**
     * convert Language Code from <br/> <b>Magento -> ERP</b> (returns a string) <br/> or <br/> <b>ERP -> Magento</b> (returns an array)
     *
     * @param string $currencyCode
     * @param string $direction
     * @return string|array
     */
    public function getLanguageMapping($languageCode, $direction = self::MAGENTO_TO_ERP)
    {
        $search = 'magento_id';
        $result = 'erp_code';
        $model = $this->commonErpMappingLanguageFactory->create();

        if ($direction == self::ERP_TO_MAGENTO) {
            $search = 'erp_code';
            $result = 'language_codes';
            $language_mapping = $model->loadMappingByStore($languageCode, $search);
        } else {
            $language_mapping = $model->loadMappingByStore(array('like' => '%' . $languageCode . '%'), 'language_codes');
        }

        if (!is_null($language_mapping->getData($result))) {
            $languageCode = $language_mapping->getData($result);
        }

        if ($direction == self::ERP_TO_MAGENTO)
            $languageCode = explode(', ', $languageCode);

        return $languageCode;
    }

    /**
     * convert Shipping method mapping from <br/> <b>Magento -> ERP</b> <br/> or <br/> <b>ERP -> Magento</b>
     *
     * If no shipping method found then default is returned
     *
     * @param string $shippingMethod
     * @param string $direction
     * @return string
     */
    public function getShippingMethodMapping($shippingMethod, $direction = self::MAGENTO_TO_ERP, $useDefault = true)
    {
        $orig = $shippingMethod;
        $search = 'shipping_method_code';
        $result = 'erp_code';

        if ($direction == self::ERP_TO_MAGENTO) {
            $search = 'erp_code';
            $result = 'shipping_method_code';
        }

        $default = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_shipping_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!empty($shippingMethod)) {
            $model = $this->commErpMappingShippingmethodFactory->create();
            /* @var $model Epicor_Comm_Model_Erp_Mapping_Shippingmethod */
            $mapping = $model->loadMappingByStore($shippingMethod, $search);
            if (!is_null($mapping->getData($result))) {
                $shippingMethod = $mapping->getData($result);
            } else if ($useDefault && !empty($default)) {
                $shippingMethod = $default;
            } else {
                $shippingMethod = $orig;
            }
        } else {
            $shippingMethod = $default;
        }

        return $shippingMethod;
    }

    /**
     * Invoices a given order.
     * @param \Magento\Sales\Model\Order $order
     */
    public function invoiceOrder(&$order, $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE)
    {
        try {
            if (!$order->canInvoice()) {
                $order->addStatusHistoryComment('Epicor Auto Invoice: Order cannot be invoiced.');
                $order->Save();
            } else {
                //START Handle Invoice

                //M1 > M2 Translation Begin (Rule p2-1)
                //$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice = $this->invoiceServiceFactory->create()->prepareInvoice($order);
                //M1 > M2 Translation End
                $invoice->setRequestedCaptureCase($captureCase);
//                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment('Epicor Auto Invoice: Order Invoiced.', false);
                $transactionSave = $this->transactionFactory->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
            }
        } catch (\Exception $e) {
            $order->addStatusHistoryComment('Epicor Auto Invoice: Error Occurred During invoicing.');
            $order->Save();
        }
    }

    /**
     * Ships a given order.
     * @param \Magento\Sales\Model\Order $order
     */
    public function shipOrder(&$order, $source = 'Auto Shipping')
    {
        try {
            if (!$order->canShip()) {
                $order->addStatusHistoryComment($source . ': Order cannot be shipped.');
                $order->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE, 'ERP Update: Order Completed');
//                $order->sendOrderCommentNotification($order, 'ERP Update: Order Marked as cancelled refund may be required.', 'Order Cancellation');
                $order->Save();
            } else {
                $shipment = $this->shipmentFactory->create($order);
                //$shipment = $order->prepareShipment();
                $shipment->register();
                $order->setIsInProcess(true);
                $order->addStatusHistoryComment($source . ': Order Shipped', false);
                $transactionSave = $this->transactionFactory->create()
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder());
                $transactionSave->save();
            }
        } catch (\Exception $e) {
            $order->addStatusHistoryComment('Shipping: Error Occurred During Shipping');
            $order->Save();
        }
    }

    public function getNextOrders()
    {
        $collection = $this->salesResourceModelOrderCollectionFactory->create();
        /* @var $collection  Mage_Sales_Model_Resource_Order_Collection */
        $pageSize = $this->scopeConfig->getValue('epicor_comm_enabled_messages/sod_request/orders_per_run', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $collection->addAttributeToSort('last_sod_update', 'ASC');
        $collection->addFieldToFilter(
            'state', array(
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                \Magento\Sales\Model\Order::STATE_HOLDED,
            )
        );
        $collection->addFieldToFilter('ecc_gor_sent', true);
        $collection->addFieldToFilter('ecc_erp_order_number', array('notnull' => null));
        $collection->setPage(1, $pageSize);
        return $collection->getItems();
    }

    private function sendOrderCommentNotification(&$order, $txt, $source = 'Auto SOD: Error')
    {
        $order->addStatusHistoryComment($txt);
        $this->sendMagentoMessage(
            $txt, $source, $this->scopeConfig->getValue('epicor_comm_enabled_messages/sod_request/error_severity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), '/admin/sales_order/view/order_id/' . $order->getId()
        );
    }

    /**
     * Updates a given order.
     * @param \Magento\Sales\Model\Order $order
     */
    public function updateOrder(&$order, $data)
    {

        $mapping = $this->getOrderMapping($data['statusCode']);

        if (!$mapping->isEmpty() && $mapping->getState() && $mapping->getStatus()) {
            if (($mapping->getState() != $order->getState()) || ($mapping->getStatus() != $order->getStatus())) {
                if ($order->getState() == \Magento\Sales\Model\Order::STATE_HOLDED) {
                    if ($order->canUnhold()) {
                        $order->unhold();
                    } else {
                        $this->sendOrderCommentNotification($order, 'ERP Update: Could not take order off hold');
                    }
                    $order->save();
                }

                switch ($mapping->getState()) {
                    case \Magento\Sales\Model\Order::STATE_COMPLETE:
                        $this->shipOrder($order, 'ERP Update');
                        break;
                    case \Magento\Sales\Model\Order::STATE_HOLDED:
                        $order->setStatus($mapping->getState(), $mapping->getStatus(), 'ERP Update: Order on Hold');
                        break;
                    case \Magento\Sales\Model\Order::STATE_CANCELED:
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, 'ERP Update: Order Cancelled');
                        $order->sendOrderCommentNotification($order, 'ERP Update: Order Marked as cancelled refund may be required.', 'Order Cancellation');
                        break;
                    case \Magento\Sales\Model\Order::STATE_PROCESSING:
                        $order->addStatusHistoryComment('ERP Update: Processing ' . $mapping->getCode());
                        if ($order->getState() == \Magento\Sales\Model\Order::STATE_HOLDED) {
                            if ($order->canUnhold()) {
                                $order->unhold();
                            } else {
                                $this->sendOrderCommentNotification($order, 'ERP Update: Could not take order off hold');
                            }
                        } else {
                            $order->setStatus($mapping->getState(), $mapping->getStatus(), 'ERP Update: Processing: ' . $mapping->getCode());
                        }
                        break;
                    default:
                        $order->setStatus($mapping->getState(), $mapping->getStatus(), 'ERP Update: ' . $mapping->getCode());
                        break;
                }
            }
        }
    }

    /**
     * Gets the next batch of products for an MSQ request
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getNextProducts()
    {
        $msqsPerPage = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/scheduledmsqbatchsize', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $products = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products->addAttributeToSort('ecc_last_msq_update', 'ASC');
        $products->addFieldToFilter('type_id', array('neq' => 'grouped'));
        $products->setPage(1, $msqsPerPage);
        return $products->getItems();
    }

    /**
     * Retrieve Message Type from config.xml
     *
     * @param string $type
     * @return string
     */
    public function getMessageType($type = 'XxX')
    {
        $type = strtolower($type);

        $message_description = strtoupper($type) . ' - Unknown';
        //M1 > M2 Translation Begin (Rule 4)
        //$xml_message = Mage::getConfig()->getXpath('global/xml_message_types/request/' . $type) ?: Mage::getConfig()->getXpath('global/xml_message_types/upload/' . $type . '/label');
        $xml_message = $this->globalConfig->get('xml_message_types/request/' . $type) ? : $this->globalConfig->get('xml_message_types/upload/' . $type . '/label');
        //M1 > M2 Translation End
        if ($xml_message !== false)
            $message_description = $xml_message;

        return (string) $message_description;
    }

    /**
     * Retrieve Message Types from config.xml
     *
     * @return array
     */
    public function getSimpleMessageTypes($type = null)
    {
        //M1 > M2 Translation Begin (Rule 4)
        //$syncMsgs = (array) Mage::getConfig()->getNode('global/xml_message_types/' . $type);
        $syncMsgs = (array) $this->globalConfig->get('xml_message_types/' . $type);
        //M1 > M2 Translation End
        $msgLabel = array();
        if (!$this->scopeConfig->getValue('epicor_comm_field_mapping/crrc_mapping/active')) {      // remove if CRRC is not available
            unset($syncMsgs['return_reason_codes']);
        }
        $sortedMsgs = array();
        foreach ($syncMsgs as $sync) {
            $syncArray = (array) $sync;
            $sortedMsgs[$syncArray['sort_order']] = $sync;
        }
        ksort($sortedMsgs);
        foreach ($sortedMsgs as $key => $syncMsg) {
            $msgArray = (array) $syncMsg;
            $removeFromMessageArray = array();
            foreach ($msgArray['messages'] as $id => $val) {

                $model = $this->getUploadMessageObject($id);
                if (!$model->isActive(null, true)) {
                    $removeFromMessageArray[$id] = $id;
                }
            }
            $messages = array_diff_key((array) $msgArray['messages'], $removeFromMessageArray);     // if message is flagged for removal take out of messages array

            if ($messages) {
                $msgLabel[strtoupper($key)] = array('label' => $msgArray['label'], 'value' => $messages);
            }
        }

        return $msgLabel;
    }

    public function getMessageTypes($type = null)
    {
        //M1 > M2 Translation Begin (Rule 4)
        //$request = (array) Mage::getConfig()->getNode('global/xml_message_types/request');

        //$upload = (array) Mage::getConfig()->getNode('global/xml_message_types/upload');
        $request = (array) $this->globalConfig->get('xml_message_types/request');

        $upload = (array) $this->globalConfig->get('xml_message_types/upload');
        //M1 > M2 Translation End
        $_upload = array();
        foreach ($upload as $key => $uploadMessage) {
            $model = $this->getUploadMessageObject($key);
            if ($model->isActive(null, true)) {
                $_upload[$key] = $uploadMessage;
            }
        }
        $uploadLabel = array();
        foreach ($_upload as $key => $uploadMessage) {
            $uploadArray = (array) $uploadMessage;
            $uploadLabel[strtoupper($key)] = $uploadArray['label'];
        }
        $all = array_merge($request, $uploadLabel);
        $messages = ($type == 'request') ? $request : (($type == 'upload') ? $_upload : $all);
        ksort($messages);
        return $messages;
    }

    public function getMessageTypeWeighting($type = null)
    {

        //M1 > M2 Translation Begin (Rule 4)
        //$upload = (array) Mage::getConfig()->getNode('global/xml_message_types/upload');
        $upload = (array) $this->globalConfig->get('xml_message_types/upload');
        //M1 > M2 Translation End
        foreach ($upload as $key => $uploadMessage) {
            $uploadArray = (array) $uploadMessage;
            $messageWeightingOrder[$uploadArray['order']] = strtoupper($key);
        }
        ksort($messageWeightingOrder);
        return $messageWeightingOrder;
    }

    /**
     * Returns the week day name from a number
     *
     * @param integer $day - day of week
     *
     * @return string - day of week
     */
    public function getWeekDayName($day)
    {
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$weekdays = Mage::app()->getLocale()->getOptionWeekdays();
        $weekdays = $this->_localeResolver->getLocale()->getOptionWeekdays();
        //M1 > M2 Translation End
        $dayName = '';

        foreach ($weekdays as $weekday) {
            if ($weekday['value'] == $day) {
                $dayName = $weekday['label'];
            }
        }

        return $dayName;
    }

    /**
     * Returns the week day number from a name
     *
     * @param integer $day - day of week
     *
     * @return string - day of week
     */
    public function getWeekDayNumber($day)
    {
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$weekdays = Mage::app()->getLocale()->getOptionWeekdays();
        $weekdays = $this->_localeResolver->getLocale()->getOptionWeekdays();
        //M1 > M2 Translation End

        $dayNum = '';

        foreach ($weekdays as $weekday) {
            if ($weekday['label'] == $day) {
                $dayNum = $weekday['value'];
            }
        }

        return $dayNum;
    }

    public function removeCurrencyCodePrefix($value)
    {
        $price = array();
        $priceArray = array();
        $priceArray = str_split(strval($value));
        foreach ($priceArray as $char) {
            if (is_numeric($char) || $char == '.') {
                $price[] = $char;
            }
        }
        return intval(implode('', $price));
    }

    public function removeCurrencyCodePrefixDP($value)          // removes currency code prefix and returns decimal placesa
    {
        $price = array();
        $priceArray = array();
        $priceArray = str_split(strval($value));
        foreach ($priceArray as $char) {
            if (is_numeric($char) || $char == '.') {
                $price[] = $char;
            }
        }
        return implode('', $price);
    }

    public function getErpOrderStatusDescription($code, $description = '')
    {
        $erp = $this->getErpOrderMapping($code);
        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    public function getErpOrderMapping($erpCode)
    {
        /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Erporderstatus */
        $model = $this->customerconnectErpMappingErporderstatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    public function refundOrder($order, $onlineOrOffline)
    {
        if ($order->getId()) {
            $invoice = $this->salesResourceModelOrderInvoiceCollectionFactory->create()->addFieldToFilter('order_id', array('eq' => $order->getId()))
                ->getFirstItem();
            if ($order->canCreditmemo()) {
                //         $this->_refundOrder($order, $this->getItems());
                $totalQty = 0;
                $totalRefunded = 0;
                $convertOrder = $this->salesConvertOrderFactory->create();
                $creditmemo = $convertOrder->toCreditmemo($order);
                $itemcount = count($order->getAllItems());
                foreach ($order->getAllItems() as $order_item) {
                    /* @var $order_item Mage_Sales_Model_Order_Item */
                    $qty = $order_item->getQtyOrdered();
                    $item = $convertOrder->itemToCreditmemoItem($order_item);
                    $item->setQty($qty);
                    $creditmemo->addItem($item);
                    $totalQty += $qty;
                    $totalRefunded += $qty;
                }
                $creditmemo->setTotalQty($totalQty);

                if ($order->getTotalQtyOrdered() == $totalRefunded) {
                    $creditmemo->setBaseShippingAmount((float) $order->getBaseShippingAmount());
                } else {
                    $creditmemo->setBaseShippingAmount(0);
                }

                if (!$invoice->isObjectNew()) {                   // if invoice exists, copy to creditmemo
                    $creditmemo->setInvoice($invoice);
                }
                $creditmemo->collectTotals();
                $creditmemo->sendEmail(true, "The value of this order has been refunded");
                $creditmemo->setEmailSent(true);

                //this bit should be replaced with offline or online value
                if ($onlineOrOffline == 'offline') {
                    $creditmemo->setOfflineRequested(true);
                } else {
                    $creditmemo->setRefundRequested(true);
                    $creditmemo->setDoTransaction(true);
                }
                $creditmemo->register();
                $this->transactionFactory->create()->addObject($creditmemo)->addObject($creditmemo->getOrder())->save();
            } else {
                $this->customerSession->addError(__('Can\'t refund order that was not invoiced'));
            }
        }
    }

    public function getAllErpPaymentMappingValues($paymentMethod)
    {
        return $this->commErpMappingPaymentFactory->create()->load($paymentMethod, 'magento_code');
    }

    public function gorOnlinePreventRepricing($paymentMethod)
    {
        /* @var $model Epicor_Comm_Model_Erp_Mapping_Payment */
        $model = $this->commErpMappingPaymentFactory->create();
        $value = $model->loadMappingByStore($paymentMethod, 'magento_code');
        return $value->getGorOnlinePreventRepricing();
    }

    public function gorOfflinePreventRepricing($paymentMethod)
    {
        /* @var $model Epicor_Comm_Model_Erp_Mapping_Payment */
        $model = $this->commErpMappingPaymentFactory->create();
        $value = $model->loadMappingByStore($paymentMethod, 'magento_code');
        return $value->getGorOfflinePreventRepricing();
    }

    public function bsvOnlinePreventRepricing($paymentMethod)
    {
        /* @var $model Epicor_Comm_Model_Erp_Mapping_Payment */
        $model = $this->commErpMappingPaymentFactory->create();
        $value = $model->loadMappingByStore($paymentMethod, 'magento_code');
        return $value->getBsvOnlinePreventRepricing();
    }

    public function bsvOfflinePreventRepricing($paymentMethod)
    {
        /* @var $model Epicor_Comm_Model_Erp_Mapping_Payment */
        $model = $this->commErpMappingPaymentFactory->create();
        $value = $model->loadMappingByStore($paymentMethod, 'magento_code');
        return $value->getBsvOfflinePreventRepricing();
    }

    public function getOrderRepriceValue($order, $paymentMethod = '', $msg)
    {
        $lowerCaseMsg = strtolower($msg);
        if ($order->getEccQuoteId()) {
            $prevent_reprice = true;
        } else {

            /* @var $model Epicor_Comm_Model_Erp_Mapping_Payment */
            $model = $this->commErpMappingPaymentFactory->create();
            $value = $model->loadMappingByStore($paymentMethod, 'erp_code');
            $prevent_reprice = $this->scopeConfig->isSetFlag("epicor_comm_enabled_messages/{$lowerCaseMsg}_request/{$lowerCaseMsg}_prevent_repricing", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($paymentMethod) {
                if (strtoupper($msg) == 'GOR') {
                    if ($this->registry->registry("offline_order_{$order->getId()}")) {
                        $prevent_reprice = ($value->getGorOfflinePreventRepricing()) ? $value->getGorOfflinePreventRepricing() : $prevent_reprice;
                    } else {    //order is online
                        $prevent_reprice = ($value->getGorOnlinePreventRepricing()) ? $value->getGorOnlinePreventRepricing() : $prevent_reprice;
                    }
                } else {   // must be BSV
                    if ($this->registry->registry("offline_order_{$order->getId()}")) {
                        $prevent_reprice = ($value->getBsvOfflinePreventRepricing()) ? $value->getBsvOfflinePreventRepricing() : $prevent_reprice;
                    } else {     //order is online
                        $prevent_reprice = ($value->getBsvOnlinePreventRepricing()) ? $value->getBsvOnlinePreventRepricing() : $prevent_reprice;
                    }
                }
            }
            $prevent_reprice = ($prevent_reprice == 'Y') ? true : false;
        }
        return $prevent_reprice;
    }

    /**
     * Sends an MSQ for the provided products
     *
     * @param \Magento\Catalog\Model\Product / Collection of Mage_Catalog_Model_Product $products
     * @param string $trigger
     *
     * @return \Epicor_Comm_Helper_Messaging
     */
    public function getCommMessageRequestMsqFactory()
    {
        return  $this->commMessageRequestMsqFactory;
    }
    public function sendMsq($products, $trigger = '')
    {
        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq \Epicor\Comm\Model\Message\Request\Msq */
        $msq->setTrigger($trigger);

        $controller = $this->request->getControllerName();
        $action = $this->request->getControllerName();

        if (!$this->registry->registry('SkipEvent') && !($controller == 'cart' && in_array($action, array('add', 'updatePost')))) {

            $transportObject = $this->dataObjectFactory->create();
            $transportObject->setProducts($products);
            $transportObject->setMessage($msq);

            $this->_eventManager->dispatch('msq_sendrequest_before', array(
                'data_object' => $transportObject,
                'message' => $msq,
            ));
            $products = $transportObject->getProducts();

            if ($msq->isActive('triggers_' . $trigger)) {
                $send = false;
                if ($products instanceof \Magento\Catalog\Model\Product) {
                    $this->_eventManager->dispatch('set_is_salable_before_msq', array('product' => $products));
                    $msq->addProduct($products, 1);
                    $send = true;
                } else if (count($products) > 0) {
                    foreach ($products as $product) {
                        $this->_eventManager->dispatch('set_is_salable_before_msq', array('product' => $product));
                        $msq->addProduct($product, 1);
                    }
                    $send = true;
                }
                if ($send) {
                    $msq->sendMessage();
                }
            }

            $this->_eventManager->dispatch('msq_sendrequest_after', array(
                'data_object' => $transportObject,
                'message' => $msq,
            ));
        }

        return $this;
    }

    public function formatAddress($address = null, $typeOfAddress = null)
    {
        $addressData = is_object($address) ? $address->getData() : array();

        $defaultAddressCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($address == 'default' || empty($addressData)) {                             // if no address data supplied use defaults
            $customer = $this->getCustomer();
            /* @var $customer \Epicor\Comm\Model\Customer */
            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $checkout = $this->checkoutSession->getQuote();
            $this->registry->unregister('QuantityValidatorObserver');
            /* @var $checkout \Magento\Checkout\Model\Session */

            $address = $this->quoteQuoteAddressFactory->create();
            /* @var $address Address */
            $address->setEccErpAddressCode($defaultAddressCode);

            if ($typeOfAddress == 'billing') {
                if ($checkout->getBillingAddress()->getPostcode()) {
                    $address = $checkout->getBillingAddress();
                } else if ($customer->getDefaultBillingAddress()) {
                    $address = $customer->getDefaultBillingAddress();
                }
            } else {
                if ($checkout->getShippingAddress()->getPostcode()) {
                    $address = $checkout->getShippingAddress();
                } else if ($customer->getDefaultShippingAddress()) {
                    $address = $customer->getDefaultShippingAddress();
                }
            }

            $addressData = $address->getData();
        }

        if ($address instanceof Address || $address instanceof \Magento\Sales\Model\Order\Address) {
            $name = $address->getName();
        } else {
            if ($address->getCustomer()) {
                $name = $address->getCustomer()->getName();
            } else {
                $name = $address->getName();
            }
        }
        if (!empty($addressData)) {
            $addressData = array(
                'addressCode' => $this->getAddressCode($address),
                'contactName' => $this->stripNonPrintableChars($name),
                'name' => $this->stripNonPrintableChars($address->getCompany()),
                'address1' => $this->stripNonPrintableChars($address->getStreetLine(1)),
                'address2' => $this->stripNonPrintableChars($address->getStreetLine(2)),
                'address3' => $this->stripNonPrintableChars($address->getStreetLine(3)),
                'city' => $this->stripNonPrintableChars($address->getCity()),
                'county' => $this->stripNonPrintableChars($this->getRegionNameOrCode($address->getCountryId(), $address->getRegion())),
//                'county' => $this->stripNonPrintableChars($address->getRegion()),
                'country' => $this->getErpCountryCode($address->getCountryId()),
                'postcode' => $this->stripNonPrintableChars($address->getPostcode()),
                'emailAddress' => $this->stripNonPrintableChars($address->getEmail()),
                'telephoneNumber' => $this->stripNonPrintableChars($address->getTelephone()),
                'mobileNumber' => $this->stripNonPrintableChars($address->getEccMobileNumber()),
                'faxNumber' => $this->stripNonPrintableChars($address->getFax()),
                'carriageText' => $this->stripNonPrintableChars($address->getCarriageText())
            );
        }
        return $addressData;
    }

    /**
     * Get ECC ERP address code.
     *
     * @param Address $address
     * @return mixed
     */
    public function getAddressCode($address)
    {
        $addressCode = $address->getEccErpAddressCode();
        if (empty($addressCode) && $addressCode !== 0 && $addressCode !== '0') {
            return $this->scopeConfig->getValue(
                self::DEFAULT_ADDRESS_CODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $addressCode;
    }

    /**
     * convert Cardtype Code from <br/> <b>Magento -> ERP</b> <br/> or <br/> <b>ERP -> Magento</b>
     *
     * @param string $paymentMethod
     * @param string $cardtypeCode
     * @param string $direction
     * @return string
     */
    public function getCardTypeMapping($paymentMethod, $cardTypeCode = null, $direction = self::MAGENTO_TO_ERP)
    {
        $origCardTypeCode = $cardTypeCode;
        $search = 'magento_code';
        $result = 'erp_code';

        if ($direction == self::ERP_TO_MAGENTO) {
            $search = 'erp_code';
            $result = 'magento_code';
        }

        $store_id = $this->storeManager->getStore()->getStoreId();
        $mapping = $this->commResourceErpMappingCardtypeCollectionFactory->create()->addFieldToFilter('payment_method', array('eq' => $paymentMethod))
            ->addFieldToFilter($search, array('eq' => $cardTypeCode))
            ->addFieldToFilter('store_id', $store_id)
            ->getFirstItem();
        if ($store_id != 0 && is_null($mapping->getId())) {
            $mapping = $this->commResourceErpMappingCardtypeCollectionFactory->create()->addFieldToFilter('payment_method', array('eq' => $paymentMethod))
                ->addFieldToFilter($search, array('eq' => $cardTypeCode))
                ->addFieldToFilter('store_id', 0)
                ->getFirstItem();
        }

        if (!is_null($mapping->getData($result))) {
            $cardTypeCode = $mapping->getData($result);
        }

        if ($origCardTypeCode == $cardTypeCode && $paymentMethod != 'all') {
            $cardTypeCode = $this->getCardtypeMapping('all', $cardTypeCode, $direction);
        }

        return $cardTypeCode;
    }

    public function getBaseCurrencyForWebsite($website)
    {

        // base currency
        $store = $this->storeManager->getWebsite($website)->getDefaultStore();
        $baseCurrencyForSite = $this->storeManager->getStore($store)->getBaseCurrencyCode();
        return $baseCurrencyForSite;
    }

    public function getAcceptedCurrenciesForErp($erpAccountId)
    {
        $acceptedCurrencies = array();
        $validErpAccountCurrencies = $this->commResourceCustomerErpaccountCurrencyCollectionFactory->create()->addFieldToFilter('erp_account_id', array('eq' => $erpAccountId))
            ->getData();
        if (!empty($validErpAccountCurrencies)) {
            foreach ($validErpAccountCurrencies as $key => $accountCurrency) {
                $acceptedCurrencies[] = $accountCurrency['currency_code'];
            }
        }
        return $acceptedCurrencies;
    }

    public function setFieldValue($field, $value, $configLocation, $erpCustomerGroup)
    {
        if (!is_null($value)) {
//            if(!$this->_accountUpdate || $this->_accountUpdate && $this->_uploadModel->isUpdateable($configLocation)){
            if ($this->_uploadModel->isUpdateable($configLocation, $this->_accountUpdate)) {
                if ($field == 'name') {
                    $this->setName($erpCustomerGroup, $value);
                } else {
                    $erpCustomerGroup->setData($field, $value);
                }
            }
        }
    }

    /**
     * Sends a message to the ERP
     *
     * @param string $base - Module the message resides in
     * @param string $messageType - (lowecase) message name
     * @param array $data - data to be passed to the message
     * @param array $searches - search criteria to be passed to the message
     * @param array $methods - methods & params to be passed to the message
     *
     * @return array
     */
    public function sendErpMessage($base, $messageType, $data = array(), $searches = array(), $methods = array())
    {
        $success = false;
        $error = '';
        try {
            //M1 > M2 Translation Begin (Rule 46)
            //$message = Mage::getModel($base . '/message_request_' . $messageType);
            $message = $this->messageRequestModelReader->getModel($base, $messageType);
            //M1 > M2 Translation End
            /* @var $message \Epicor\Comm\Model\Message\Request */

            $messageTypeCheck = $message->getHelper('epicor_comm/messaging')->getMessageType(strtoupper($messageType));

            if ($message->isActive() && $messageTypeCheck) {

                $message->addData($data);

                if (!empty($searches)) {
                    foreach ($searches as $key => $conditions) {
                        foreach ($conditions as $condition => $value) {
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    $message->addSearchOption(
                                        $this->convertStringToCamelCase($key), $condition, $v
                                    );
                                }
                            } else {
                                $message->addSearchOption(
                                    $this->convertStringToCamelCase($key), $condition, $value
                                );
                            }
                        }
                    }
                }

                if (!empty($methods)) {
                    foreach ($methods as $methodName => $params) {
                        if (method_exists($message, $methodName)) {
                            if (!is_array($params)) {
                                $params = array($params);
                            }
                            call_user_func_array(array($message, $methodName), $params);
                        }
                    }
                }

                $success = $message->sendMessage();

                if (!$success) {
                    $error = $message->getStatusDescription();
                }
            }
        } catch (\Exception $e) {
            $success = false;
            $error = $e->getMessage();
            echo $error;
            die();
        }

        return array(
            'success' => $success,
            'message' => $message,
            'error' => $error
        );
    }

    /**
     * Checks to see if a message is enabled
     *
     * @param string $base - Module the message resides in
     * @param string $messageType - (lowecase) message name
     *
     * @return array
     */
    public function isMessageEnabled($base, $messageType)
    {
        $enabled = false;
        try {
            //M1 > M2 Translation Begin (Rule 46)
            //$message = Mage::getSingleton($base . '/message_request_' . strtolower($messageType));
            $message = $this->messageRequestModelReader->getModel($base, $messageType);
            //M1 > M2 Translation End

            /* @var $message Epicor_Comm_Model_Message_Request */

            $messageTypeCheck = $this->getMessageType(strtoupper($messageType));

            if ($message->isActive() && $messageTypeCheck) {
                $enabled = true;
            }
        } catch (\Exception $e) {
            $enabled = false;
        }

        return $enabled;
    }
    /**
     * Gets the next batch of products for an MSQ request
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getNextScheduledMsqProducts()
    {
        $msqsPerPage = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/scheduledmsqbatchsize', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $products = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */

        $products->addAttributeToSelect('ecc_last_msq_update','left');
        $products->setFlag('no_product_filtering', true);
        $products->addAttributeToSelect('sku');
        $products->addAttributeToSelect('ecc_uom');
        $products->addAttributeToSelect('ecc_default_uom');
        $products->addAttributeToSelect('ecc_lead_time');

        $msqForNonErp = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/msq_for_non_erp_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($msqForNonErp == false) {
            $products->addAttributeToSelect('ecc_pricing_sku','left');
            $products->addAttributeToSelect('ecc_stk_type','left');

            $conditions = array(
                array('attribute' => 'ecc_stk_type', 'notnull' => true),
                array('attribute' => 'ecc_pricing_sku', 'notnull' => true)
            );

            $products->addAttributeToFilter($conditions);
        }

        $products->getSelect()->order(array('ecc_last_msq_update ASC', 'e.entity_id ASC'));
        $products->setPage(1, $msqsPerPage);

        return $products->getItems();
    }

    /**
     * Get the types of auto sync
     *
     * @return array
     */
    public function getAutoSyncType($messages = false)
    {
        $messagesList = [
            'customer' => ['CUS', 'CUCO', 'CAD', 'CXR', 'CRRC', 'LOC', 'CUSR', 'CURP', 'CUPG', 'CCCN'],
            'part' => ['STK', 'STG', 'SGP', 'STT', 'ALT', 'CPN', 'PAC'],
            'sales' => ['SOU', 'CREU', 'GQR'],
            'supplier' => ['SUSP', 'SUCO'],
            'misce' => ['FREQ', 'FSUB']
        ];
        if (!$messages) {
            $messagesList = array_keys($messagesList);
        }
        return $messagesList;
    }


    /**
     * Set and validate name.
     *
     * @param $erpCustomerGroup
     * @param $value
     *
     * @throws \Exception
     */
    public function setName(&$erpCustomerGroup, $value)
    {
        $addressLimitEnabled = $this->isAddressLimitEnabled();
        $characterLimit = $this->scopeConfig->getValue(self::COMPANY_LIMIT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($addressLimitEnabled && strlen($value) > $characterLimit) {
            throw new \Exception(__('Account Name exceeds character limit of '.$characterLimit.'.'), \Epicor\Comm\Model\Message::STATUS_REJECTED);
        } else {
            $erpCustomerGroup->setData('name', $value);
        }

    }//end setName()


    /**
     * Validate address name.
     *
     * @param $address_name
     *
     * @throws \Exception
     */
    public function validateAddressName($address_name)
    {
        $addressLimitEnabled = $this->isAddressLimitEnabled();
        $characterLimit      = $this->scopeConfig->getValue(self::COMPANY_LIMIT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($addressLimitEnabled && strlen($address_name) > $characterLimit) {
            throw new \Exception(__('Address Name exceeds character limit of '.$characterLimit.'.'), \Epicor\Comm\Model\Message::STATUS_REJECTED);
        }

    }//end validateAddressName()


}
