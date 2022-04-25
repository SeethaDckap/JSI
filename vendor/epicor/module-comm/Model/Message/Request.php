<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message;


/**
 * Base class for zall request messages.
 * @author David. wylie
 * 
 * @method setDirection($direction)
 * @method setErp($erp)
 * @method setLegacyHeader($legacyHeader)
 * @method getCustomerGroupId()
 * @method setIsDeamon(bool $isDeamon)
 * @method bool getIsDeamon()
 * @method setConnectionSuccessful(bool $success)
 * @method bool getConnectionSuccessful()
 * 
 * @method int getStoreId()
 */
abstract class Request extends \Epicor\Comm\Model\Message
{

    protected $_curlproxy = '';
    protected $_usesoap = false;
    protected $_soapver = '';
    protected $_encsoapdata = false;
    protected $_usecdata = false;
    protected $_api_username = '';
    protected $_api_password = '';
    protected $_company = '';
    protected $_brand = null;
    protected $_pools = '';
    public $http_status_code = null;
    protected $_searchInCriteria = array();
    protected $_searchCriteria = array();
    protected $_mergedSearches = array();
    protected $_accountNumbers = array();
    protected $_displayData = array();
    protected $requestHeaders = array();

    /**
     *
     * @var \Magento\Sales\Model\Order
     */
    public $_order;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Session\GenericFactory
     */
    protected $genericFactory;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $configConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Construct object and set variables based on config values
     * Set default customer group id based on session.
     */
     /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commHelper = $context->getCommHelper();
        $this->genericFactory = $context->getGenericFactory();
        $this->configConfig = $context->getConfigConfig();
        $this->customerSession = $context->getCustomerSession();
        $this->logger = $context->getLogger();
        $this->request = $context->getRequest();
        $this->encryptor = $context->getEncryptor();
        $this->registry = $context->getRegistry();

        parent::__construct($context, $resource, $resourceCollection, $data);


        $this->setMessageCategory(self::MESSAGE_CATEGORY_OTHER);
        $this->_msg_parent = parent::MESSAGE_TYPE_REQUEST;
        $this->_url = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_curlproxy = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/curlproxy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_usesoap = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/usesoap', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_soapver = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/soapver', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_usecdata = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/usecdata', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_encsoapdata = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/encsoapdata', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_api_username = $this->scopeConfig->getValue('Epicor_Comm/licensing/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_api_password = $this->encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));


        $this->_brand = $this->commHelper->getStoreBranding();
        $this->_company = $this->_brand->getCompany();
        $this->_pools = @unserialize($this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/pools', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

//        $this->setMessageId(md5(uniqid()));
        $uuid1 = uniqid('', true);
        $uuid2 = uniqid('', true);
        $uuid3 = uniqid('', true);

        $uuid = str_replace('.', '', sprintf(
            '%s-%s-%s-%s-%s-%s', substr($uuid1, -11, 5), substr($uuid1, -5, 6), substr($uuid2, -12, 5), substr($uuid2, -7, 6), substr($uuid3, -13, 5), substr($uuid3, -6, 4)
        ));
        $this->setMessageId($uuid);

        $commHelper = $this->commHelper;
        /* @var $commHelper \Epicor\Comm\Helper\Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if ($erpAccount) {
        $this->setCustomerGroupId($erpAccount->getId());
        }
        $this->setDirection('request');

    }

    public function setUrl($url)
    {
        $this->_url = $url;
    }

    public function setApiUser($user)
    {
        $this->_api_username = $user;
    }

    public function setApiPassword($pass)
    {
        $this->_api_password = $pass;
    }

    /**
     * 
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->setCustomerGroupId($customer->getEccErpaccountId());
    }

    /**
     * Set the customer group id for the message.
     * @param int $customerGroupId
     */
    public function setCustomerGroupId($customerGroupId)
    {
//do the parent method.
        parent::setCustomerGroupId($customerGroupId);
//get account number then set the legacy header.
        $helper = $this->commMessagingHelper->create();
        $accountNumber = $helper->getErpAccountNumber($customerGroupId, $this->getStoreId());
        $this->setAccountNumber($accountNumber);
    }

    public function getAccountNumber($withPrefix = false)
    {
        $account_number = $this->getData('account_number');
        if (!$withPrefix) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $parts = explode($delimiter, $account_number, 2);
            $account_number = $parts[count($parts) - 1];
        }
        return $account_number;
    }

    /**
     * Format the given xml for sending.
     * This adds soap encoding and appropriate cdata.
     */
    protected function formatXmlForSending()
    {

//$headers = array_slice($this->requestHeaders, 0);
        $requestXML = $this->_xml_out;
        if ($this->_usesoap) {
// Need to add some wrapping to the message.
// Find where the actual XML starts (i.e. ignore preamble)
            $startpos = strpos($requestXML, "<messages>");

            if ($startpos !== false) {
                $requestXML = substr($requestXML, $startpos);
            }

// Check if we need to encode the content i.e. replace & with &amp;, < with &lt; and > with &gt;
            if ($this->_encsoapdata) {
                $requestXML = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $requestXML);
            }

// Check if we need to wrap the content with a CDATA wrapper.
            if ($this->_usecdata) {
                $requestXML = "<![CDATA[" . $requestXML . "]]>";
            }

// Encase the XML with the SOAP wrapping.
            $requestXML = $this->startSOAP . $requestXML . $this->endSOAP;
        }
        $this->_xml_out = $requestXML;
    }

    /**
     * Initialise the soap variables.
     */
    private function soapInit()
    {
        $soapVersion = "1.1";

        $this->startSOAP = "";
        $this->endSOAP = "";

        if (isset($this->_soapver)) {
            $soapVersion = ((strcasecmp($this->_soapver, "1.2") == 0) ? "1.2" : "1.1");
        }

        $this->_soapver = $soapVersion;

        if ($this->_usesoap) {
            $this->requestHeaders = array("POST /TroposWebSalesInterface.asmx HTTP/1.1",
                "Host: localhost"
            );

// Check the soap version.
            if (strcasecmp($this->_soapver, "1.2") == 0) {
// This will go before the XML we generated.
                $this->startSOAP .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
                $this->startSOAP .= "<soap12:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap12=\"http://www.w3.org/2003/05/soap-envelope\">\n";
                $this->startSOAP .= "<soap12:Body>\n";
                $this->startSOAP .= "<ProcessMessage xmlns=\"http://epicor.com/\">\n";
                $this->startSOAP .= "<MessageBody>";

// And this bit afterwards, to complete the wrapping.
                $this->endSOAP .= "</MessageBody>\n";
                $this->endSOAP .= "</ProcessMessage>\n";
                $this->endSOAP .= "</soap12:Body>\n";
                $this->endSOAP .= "</soap12:Envelope>";

// Also need to specify the additional headers.
                $this->requestHeaders['Content-Type'] = 'application/soap+xml; charset=utf-8';
            } else {
// This will go before the XML we generated.
                $this->startSOAP .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
                $this->startSOAP .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
                $this->startSOAP .= "<soap:Body>\n";
                $this->startSOAP .= "<ProcessMessage xmlns=\"http://epicor.com/\">\n";
                $this->startSOAP .= "<MessageBody>";

// And this bit afterwards, to complete the wrapping.
                $this->endSOAP .= "</MessageBody>\n";
                $this->endSOAP .= "</ProcessMessage>\n";
                $this->endSOAP .= "</soap:Body>\n";
                $this->endSOAP .= "</soap:Envelope>";

// Also need to specify the additional headers.
                $this->requestHeaders['Content-Type'] = 'text/xml; charset=utf-8';
                $this->requestHeaders['SOAPAction'] = '"http://epicor.com/ProcessMessage"';
            }
        }
    }

    /**
     * Initialise a http client setting the appropriate curl options.
     * @return \Zend_Http_Client
     */
    protected function initHttpClient()
    {
        $connection = new \Zend_Http_Client();
        $adapter = new \Zend_Http_Client_Adapter_Curl();
        $connection->setUri($this->_url);
//$adapter->setCurlOption(CURLOPT_URL, $this->url);
        $adapter->setCurlOption(CURLOPT_HEADER, FALSE);
        if ($this->_curlproxy) {
            $adapter->setCurlOption(CURLOPT_PROXY, $this->_curlproxy);
        }
        $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, FALSE);
        $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, FALSE);
        $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 1);

// post options
        $adapter->setCurlOption(CURLOPT_POST, 1);
        /*start of fix WSO-6512*/
        $config = array('timeout' => 0);
        $connection->setConfig($config);
        $adapter->setCurlOption(CURLOPT_CONNECTTIMEOUT,$this->getMessageTimeout());
        /*end of fix WSO-6512*/
        $adapter->setCurlOption(CURLOPT_TIMEOUT, $this->getMessageTimeout());
        $connection->setAdapter($adapter);
        $this->soapInit();
        return $connection;
    }

    /**
     * Loads the timeout for the message (will either be the default or message specific)
     * 
     * @return integer
     */
    private function getMessageTimeout()
    {
        $timeout = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $msgTimeout = $this->scopeConfig->getValue('xml_message/timeouts/' . strtolower($this->getMessageType()));

        if (!empty($msgTimeout)) {
            $timeout = $msgTimeout;
        }

        return (int) $timeout;
    }

    /**
     * post xml to erp.
     * @param \Zend_http_client $connection
     * @throws \Exception
     */
    private function send(\Zend_Http_Client $connection = null)
    {
        if ($connection == null) {
            $connection = $this->initHttpClient();
        }
        $this->formatXmlForSending();
        $connection->setHeaders('Content-Length', strlen($this->_xml_out));
        if ($this->_usesoap) {
            foreach ($this->requestHeaders as $k => $v) {
                $connection->setHeaders($k, $v);
            }
        }

        if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'p21') {
            $connection->setHeaders('Authorization', 'Bearer ' . $this->scopeConfig->getValue('Epicor_Comm/licensing/p21_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        } else {
            $connection->setAuth($this->_api_username, $this->_api_password);
        }

        $commHelper = $this->commHelper;
        $isREST = $commHelper->isEnableRest();
        if (is_array($this->_pools)) {
            foreach ($this->_pools as $licenseCode => $licenseKey) {
                $licensingPools[$licenseCode] = $licenseKey;
            }
            $connection->setHeaders('LicensePools',
                $isREST ? json_encode($licensingPools) : base64_encode(json_encode($licensingPools)));

            if (array_key_exists('ecc_web_service', $this->_pools)) {
                $connection->setHeaders('License',
                    $isREST ? json_encode(array('Claimedlicense' => $this->_pools['ecc_web_service'])) : base64_encode(json_encode(array('Claimedlicense' => $this->_pools['ecc_web_service']))));
            }
        }

        $callSettings = array(
            'Company' => $this->_company
        );
        if ($isREST) {
            $connection->setHeaders('CallSettings', json_encode($callSettings));
            $connection->setRawData(json_encode(array("xmlInBytes" => base64_encode($this->_xml_out))),
                'application/json');
        } else {
            $connection->setHeaders('CallSettings', base64_encode(json_encode($callSettings)));
            $connection->setRawData($this->_xml_out, 'text/xml');
        }

        try {

            $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_sendxml_before', array(
                'data_object' => $this,
                'message' => $this,
                'connection' => $connection,
            ));

            $response = $connection->request(\Zend_Http_Client::POST);
            $responseBody = null;
            if ($isREST) {
                $responseBody = $this->convertRestToXml($response);
            } else {
                $responseBody = $response->getBody();
            }
            $this->setInXml($responseBody);
            $this->http_status_code = $response->getStatus();
            $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_sendxml_after', array(
                'data_object' => $this,
                'message' => $this,
                'response' => $response,
            ));
            if ($response->getStatus() == 200) {
                $this->cleanXml();

                $this->validateResponse();
                $this->setConnectionSuccessful(true);
            } else {
                throw new \Exception('Invalid HTTP status code : ' . $response->getStatus());
            }
        } catch (\Zend_Http_Client_Exception $e) {
            $this->connectionError(self::STATUS_CONNECTION_ERROR, $e);
        } catch (\Exception $e) {
            $this->_xml_in_log = "Headers:\n" . $response->getHeadersAsString() . "\n\nBody:\n" . $response->getBody();
            $this->connectionError(self::STATUS_PHP_ERROR, $e);
        } catch (\Zend_Uri_Exception $e) {
            $this->connectionError(self::STATUS_URL_ERROR, $e);
        }
    }

    private function connectionError($code, $e)
    {
        $this->setConnectionSuccessful(false);
        $this->setStatusCode($code);
        $this->setStatusDescription($e->getMessage());
    }

    /**
     * Process received XML.
     * This will clean SOAP xml to wash away the crud.
     */
    protected function cleanXml()
    {
        if ($this->_usesoap) {
// We need to strip out the SOAP information.
            $startPos = strpos($this->_xml_in, "<ProcessMessageResult>") + strlen("<ProcessMessageResult>");
            $endPos = strrpos($this->_xml_in, "</ProcessMessageResult>");

// This will leave us with the bit in the middle i.e. the XML we actually want.
            $strippedXML = substr($this->_xml_in, $startPos, $endPos - $startPos);

// We need to decode the content i.e. replace &amp; with &, &lt; with < and &gt; with >

            $find = array('&lt;', '&gt;', '&amp;');
            $replace = array('<', '>', '&');
// Check if we need to unwrap the content from a CDATA wrapper.
            if ($this->_usecdata) {
                $find[] = "<![CDATA[";
                $find[] = "]]>";
                $replace[] = "";
                $replace[] = "";
            }
            $cleanedXML = str_replace($find, $replace, $strippedXML);
// Add the XML heading back on.
            $this->_xml_in = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" . $cleanedXML;
        }
    }

    /**
     * Validate the given xml. This includes checking that:
     * -response has the expected structure.
     * -response is xml.
     * -response is for the correct message.
     * @throws \Exception
     */
    protected function validateResponse()
    {
        $helper = $this->commonXmlHelper->create();
         
        $valid = false;

        //Set Response bassed on messageType 
        if(in_array($this->getMessageType(),$this->commHelper->getArrayMessages())){
            $obj = $helper->convertXmlToArraynew($this->_xml_in);
            
            if ( isset($obj['messages']) && isset($obj['messages']['response']) && isset($obj['messages']['response']['@attributes']) &&
                 $this->getMessageType() == $obj['messages']['response']['@attributes']['type'] &&
                 isset($obj['messages']['response']['body']) && isset($obj['messages']['response']['body']['status'])
                 && isset($obj['messages']['response']['body']['status']['code']) &&
                 $obj['messages']['response']['body']['status']['code'] != null ) {
                 $valid = true;
                 $this->setResponse($obj['messages']['response']['body']);
                 $this->setStatusCode($obj['messages']['response']['body']['status']['code']);
                 if (isset($obj['messages']['response']['body']['status']['description']) && !empty($obj['messages']['response']['body']['status']['description']))
                    $this->setStatusDescription($obj['messages']['response']['body']['status']['description']);
            }
            
        }else{
            $obj = $helper->convertXmlToVarienObject($this->_xml_in);
            if (($obj instanceof \Magento\Framework\DataObject) &&
                ($obj->getMessages() instanceof \Magento\Framework\DataObject) &&
                ($obj->getMessages()->getResponse() instanceof \Magento\Framework\DataObject) &&
                ($obj->getMessages()->getResponse()->getData('_attributes') instanceof \Magento\Framework\DataObject) &&
                ($this->getMessageType() == $obj->getMessages()->getResponse()->getData('_attributes')->getType()) &&
                ($obj->getMessages()->getResponse()->getBody() instanceof \Magento\Framework\DataObject) &&
                ($obj->getMessages()->getResponse()->getBody()->getStatus() instanceof \Magento\Framework\DataObject) &&
                ($obj->getMessages()->getResponse()->getBody()->getStatus()->getCode() != null)) {
                $valid = true;
                if($this->getRequestMessageBody()) {
                    $response = $obj->getMessages()->getResponse();
                    /* $response Epicor\Common\Model\Xmlvarien */
                    // Data Mapping
                    $response = $this->processDataMapping($response);
                    $this->setResponse($response);
                    $this->setStatusCode($this->getResponse()->getBody()->getStatus()->getCode());
                    if ($this->getResponse()->getBody()->getStatus()->getDescription())
                        $this->setStatusDescription($this->getResponse()->getBody()->getStatus()->getDescription());                    
                } else {
                    $response = $obj->getMessages()->getResponse()->getBody();
                    /* $response Epicor\Common\Model\Xmlvarien */
                    // Data Mapping
                    $response = $this->processDataMapping($response);
                    $this->setResponse($response);
                    $this->setStatusCode($this->getResponse()->getStatus()->getCode());
                    if ($this->getResponse()->getStatus()->getDescription())
                        $this->setStatusDescription($this->getResponse()->getStatus()->getDescription());                    
                }
            }
        }
        if (!$valid) {
            throw new \Exception('XML received is of incorrect structure or type.');
        }
    }

    /**
     * Generates the message template for use when creating requests.
     * @return array
     */
    public function getMessageTemplate()
    {
        $template = array('messages' => array(
                'request' => array(
                    '_attributes' => array(
                        'type' => $this->getMessageType(),
                        'id' => $this->getMessageId()
                    ),
                    'header' => array(
                        //M1 > M2 Translation Begin (Rule 32)
                        //'datestamp' => $this->getHelper()->getLocalDate(time(), self::DATE_FORMAT),
                        'datestamp' => $this->getHelper()->getLocalDate(time(), \IntlDateFormatter::LONG),
                        //M1 > M2 Translation End
                        'source' => $this->_source,
                        'erp' => $this->_erp,
                        'cached' => $this->_cachedStatus
                    ),
                    'body' => array(
                        'brand' => array(
                            'company' => $this->_brand->getCompany(),
                            'site' => $this->_brand->getSite(),
                            'warehouse' => $this->_brand->getWarehouse(),
                            'group' => $this->_brand->getGroup()
                        ),
                        'payload' => $this->getPayload(),
                    ),
                )
            )
        );

        if ($this->getHelper()->isLegacyErp()) {
            $legacyHeader = $this->EKOgetCustomerLegacyHeader($this->getAccountNumber());
            $template['messages']['request']['header']['legacyheader'] = $legacyHeader;
        }

        return $template;
    }

    protected function EKOgetCustomerLegacyHeader($accountCode = null)
    {
        if (is_numeric($accountCode)) {
            $leftPaddedAccount = str_pad($accountCode, 6, " ", STR_PAD_LEFT);
            $account = str_pad($leftPaddedAccount, 10);
        } else {
            $account = str_pad($accountCode, 10);
        }
        $session = str_pad(
            substr($this->genericFactory->create()->getEncryptedSessionId(), 0, 10)
            , 10);
        $statusCode = str_pad('0', 1);
        $messageVer = str_pad($this->getConfig('messagever'), 3, '0', STR_PAD_LEFT);
        $systemCode = str_pad('', 3);
        $spare = str_pad('', 9);
        $header = $session . $account . $statusCode . $messageVer . $systemCode . $spare;
        return $header;
    }

    private function processError($error = true)
    {
        $helper = $this->commHelper;
        $msg = $this->getMessageType();
        $code = $this->getStatusCode();
        $msgDesc = @$this->error_status_codes[$code]? @$this->error_status_codes[$code]: 'Unknown error';
        $statusDescription = $this->getStatusDescription();
        $errorTitle = "A $msgDesc occurred on a $msg";
        $erpErrorTitle = "$errorTitle\n Erp returned: \n $statusDescription ";
        //M1 > M2 Translation Begin (Rule 25)
        //$date = now();
        $date = date('Y-m-d H:i:s');
        //M1 > M2 Translation End
        /* @var $statusDescription string */
        $errorMessage = "$errorTitle at $date <br/> Erp returned: <br/> $statusDescription ";
        $contentMsg = "{$errorTitle} at {$date}";
        if ($this->isEmailNotificationRequired($error)) {
            $storeId = $this->storeManager->getStore()->getId();
            $to = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $name = $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            /** @var $model Mage_Core_Model_Email_Template */
            $vars = [
                        'adminName' => $name,
                        'adminEmail' => $to,
                        'contentMsg' => $contentMsg,
                        'contentDesc' => $statusDescription,
                    ];
            $from = [   'email' => $this->scopeConfig->getValue('trans_email/ident_general/email',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        'name' => $this->scopeConfig->getValue('trans_email/ident_general/name',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    ];

            $helper->sendTransactionalEmail('epicor_comm_message_error_email_template', $from, $to, $name, $vars, $storeId = null);
            
        }
        if ($this->isAdminNotificationRequired($error)) {
            $logId = $this->getLog()->getId();
            $errorMessage .= "<a onClick='goToMessageUrl(\"$logId\");'>View Log</a>";

            $helper->sendMagentoMessage($errorMessage, $errorTitle, $this->getNotificationSeverity($error));
        }
        if ($this->isUserNotificationRequired($error) || in_array($this->getStatusCode(), $this->forceNotificationsForStatusCodes)) {
            $errorAction = (
                $this->getErrorAction($error) == 'CONTINUE' && !in_array($this->getStatusCode(), $this->forceNotificationsForStatusCodes)
                ) ? 'Notice' : 'Error';

            $helper->showNotification($this->userNotificationMessage($error, $erpErrorTitle, $statusDescription), $errorAction, $msg);
        }


        if ($this->doErrorCount($error)) {
            $offlineCount = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/failed_msg_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) + 1;
            $offlineLimit = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/failed_msg_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $siteOnline = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/failed_msg_online', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //M1 > M2 Translation Begin (Rule P2-5.6)
            //$config = Mage::getConfig();
            //M1 > M2 Translation End
            $this->configConfig
                ->setSection('Epicor_Comm')
                ->setWebsite(null)
                ->setStore(null)
                ->setGroups(array(
                    'xmlMessaging' => array(
                        'fields' => array(
                            'failed_msg_count' => array(
                                'value' => $offlineCount
                            )
                        )
                    )
                ))
                ->save();

            if ($offlineCount >= $offlineLimit) {
                $this->configConfig
                    ->setSection('Epicor_Comm')
                    ->setWebsite(null)
                    ->setStore(null)
                    ->setGroups(array(
                        'xmlMessaging' => array(
                            'fields' => array(
                                'failed_msg_online' => array(
                                    'value' => false
                                )
                            )
                        )
                    ))
                    ->save();
                //check that site is online and that the email has not already been sent before sending
                if($siteOnline && !$this->registry->registry('site_offline')){
                    $this->registry->register('site_offline', true);
                    $this->commHelper->sendEmailWhenSiteOffline();
                 }
            }

            //M1 > M2 Translation Begin (Rule P2-5.6)
            // $config->reinit();

            //M1 > M2 Translation End

            //M1 > M2 Translation Begin (Rule p2-6.11)
            //Mage::app()->reinitStores();
            $this->storeManager->reinitStores();
            //M1 > M2 Translation End


            SWITCH ($this->getErrorAction($error)) {
                case 'CONTINUE':
                    break;
                case 'ERROR':
                    throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    break;
                case 'OFFLINE-SITE':
                    $this->storeManager->getStore()->setConfig('Epicor_Comm/xmlMessaging/failed_msg_count', $offlineLimit);
                    $this->storeManager->getStore()->setConfig('Epicor_Comm/xmlMessaging/failed_msg_online', false);
                    break;
            }
        }
    }

    /**
     * 
     * @param type $store_id
     */
    public function setStoreId($store_id = null)
    {
        $this->setData('store_id', $store_id);
        $this->_brand = $this->getHelper()->getStoreBranding($store_id);
        $this->_company = $this->_brand->getCompany();
    }

    /**
     * 
     * @param type $brand
     */
    public function setBranding($brand)
    {
        $this->_brand = $brand;
        $this->_company = $this->_brand->getCompany();
    }

    protected function setMessageSubjects()
    {
        $customerSession = $this->customerSession;
        if ($customerSession->isLoggedIn()) {
            $email = $customerSession->getCustomer()->getEmail();
        } else {
            $email = $this->getEmail() ?: 'Guest';
        }

        if (!$this->getMessageSubject()) {
            $this->setMessageSubject($email);
        }

        if (!$this->getMessageSecondarySubject()) {
            $this->setMessageSecondarySubject($email);
        }
    }

    /**
     * 
     * Send the message and process result.
     * 
     * @param \Zend_Http_Client $connection
     * @return bool
     */
    public function sendMessage(\Zend_Http_Client $connection = null)
    {
        
        $result = false;

        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_buildrequest_before', array(
            'data_object' => $this,
            'message' => $this,
        ));

        try {
            $request = $this->buildRequest();
            //don't send a bsv on missing quoteid message if the setting in config says not to
            if(strpos($request, 'Missing Quote Id') !== false
                && strtolower($this->getMessageType()) == 'bsv'
                && !$this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/bsv_request/missing_quote_id',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
                    return;
                }


            $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_buildrequest_after', array(
                'data_object' => $this,
                'message' => $this,
            ));
            $this->processPayload('request', 'request');
            if ($request === false) {
                return;
            }
        } catch (\Exception $e) {
            $request = $e->getMessage();
            $this->logger->log(200, $e->getMessage());
        }

        $logGlobal = $this->scopeConfig->isSetFlag('epicor_comm_message_logging/global_logging/override_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $logAll = ($logGlobal) ? : $this->getConfigFlag('log_all_requests');

        $this->setMessageSubjects();

        $this->logInitial($logAll);
        $logLastInsertedID = $this->getLog()->getId();
        $traceStarted      = false;
        if ($request === true) {
            if(extension_loaded('appoptics')) {
                if(!appoptics_trace_started()) {
                    $traceStarted = true;
                    appoptics_start_trace('customtrace');
                }
                appoptics_log_entry($this->getMessageType() . '_TIME');
            }
            if (!$this->_cached) {
                $this->send($connection);
            } else {
                $this->prepareCachedResponse();
            }

            $this->updateResponseFromCache();

            $this->processStatusCode();

            if(extension_loaded('appoptics')) {
                appoptics_log(null, 'info', array('Log ID' => $logLastInsertedID));
                appoptics_log_exit($this->getMessageType() . '_TIME');
                if($traceStarted && appoptics_trace_started()) {
                    appoptics_end_trace();
                }
            }
            if ($this->getConnectionSuccessful()) {
                $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_processresponse_before', array(
                    'data_object' => $this,
                    'message' => $this,
                ));
               if(in_array($this->getMessageType(),$this->commHelper->getArrayMessages())){                   
                $result = $this->processResponseArray();
               }else {
                $result = $this->processResponse();
              }
                $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_processresponse_after', array(
                    'data_object' => $this,
                    'message' => $this,
                    'result' => $result
                ));                
            }            
        } else {
            $this->setStatusDescription('Failed to Build Message: ' . $request);
            $this->logCompleted(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_ERROR);
            $this->processError(true);
        }

        return $result;
    }

    /**
     * Sets up the response object & status for a cached messaged
     */
    public function prepareCachedResponse()
    {
        $message = array('messages' => array(
                'response' => array(
                    '_attributes' => array(
                        'type' => $this->getMessageType(),
                        'id' => $this->getMessageId()
                    ),
                    'header' => array(
                        //M1 > M2 Translation Begin (Rule 32)
                        //'datestamp' => $this->getHelper()->getLocalDate(time(), self::DATE_FORMAT),
                        'datestamp' => $this->getHelper()->getLocalDate(time(), \IntlDateFormatter::LONG),
                        //M1 > M2 Translation End
                        'source' => 'Cached Data',
                        'erp' => $this->_erp,
                        'cached' => 'fully'
                    ),
                    'body' => $this->getCachedResponseBaseBody(),
                )
            )
        );

        /* @var $helper \Epicor\Common\Helper\Xml */
        $helper = $this->commonXmlHelper->create();
        $xml = $helper->convertArrayToXml($message);
        if(in_array($this->getMessageType(),$this->commHelper->getArrayMessages())){
            $obj = $helper->convertXmlToArraynew($xml);
            $this->setResponse($obj['messages']['response']['body']); 
        }else{
            $obj = $helper->convertXmlToVarienObject($xml);
            $this->setResponse($obj->getMessages()->getResponse()->getBody());
        }
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription($this->success_status_codes[self::STATUS_SUCCESS]);
        $this->setConnectionSuccessful(true);
    }

    /**
     * Returns any base message body needed for the message
     * @return array
     */
    public function getCachedResponseBaseBody()
    {
        return array('status' =>
            array(
                'code' => self::STATUS_SUCCESS,
                'description' => 'Fully Cached Data',
                'erpErrorCode' => ''
            )
        );
    }

    /**
     * Updates the response object from any cached data
     */
    public function updateResponseFromCache()
    {
        $helper = $this->commonXmlHelper->create();
        /* @var $helper \Epicor\Common\Helper\Xml */

        if (!empty($this->_cachedResponse)) {
            
            if(in_array($this->getMessageType(),$this->commHelper->getArrayMessages())){
                $cacheArray = array('fromCache' => $this->_cachedResponse);
            }else{
                $cacheArray = array('fromCache' => $helper->varienToArray($this->_cachedResponse));
            }        
            $xml = $helper->convertArrayToXml($cacheArray);
            $this->_xml_in_log = !empty($this->_xml_in_log) ? $this->_xml_in_log . str_replace('<?xml version="1.0"?>', '', $xml) : $xml;
        }

        if (!empty($this->_cachedRequest)) {
            $cacheArray = array('useCache' => $helper->varienToArray($this->_cachedRequest));
            $xml = $helper->convertArrayToXml($cacheArray);
            $this->_xml_out = !empty($this->_xml_out) ? $this->_xml_out . str_replace('<?xml version="1.0"?>', '', $xml) : $xml;
        }
    }

    public function processStatusCode()
    {
        $logGlobal = $this->scopeConfig->isSetFlag('epicor_comm_message_logging/global_logging/override_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $logAll = ($logGlobal) ?: $this->getConfigFlag('log_all_requests');
        $connectionSuccess = $this->getConnectionSuccessful();
        if ($connectionSuccess) {
            $code = $this->getStatusCode();
            if ($this->isSuccessfulStatusCode()) {
                $this->setIsSuccessful(true);                
                $this->logCompleted(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_SUCCESS, $logAll);
            } else if ($this->isWarningStatusCode()) {
                $this->setIsSuccessful(true);
                $this->logCompleted(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_WARNING);
                $this->processError(false);
            } else {
                //error or unknown code.
                $this->setIsSuccessful(false);
                $this->logCompleted(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_ERROR);
                $this->processError(true);
            }
        } else {
            $this->logCompleted(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_ERROR);
            $this->processError(true);
        }
    }

    public function addSecondaryAccountNumbers()
    {
        $parms = $this->request->getParams();
        if (array_key_exists('selectederpaccts', $parms)) {
            $decoded = $this->commHelper->getUrlDecoder()->decode($parms['selectederpaccts']);
            $erpAcctArray = explode(',', $decoded);
            foreach ($erpAcctArray as $value) {
                if(isset($this->_accountNumbers['accountNumber']) && in_array(stripslashes($value), $this->_accountNumbers['accountNumber'])){
                    continue;
                }else{
                    $this->_accountNumbers['accountNumber'][] = stripslashes($value);
                }
            }
        }
    }

    public function addSearchOption($fieldName, $condition, $value)
    {
        $searchVariable = '_searchCriteria';
        if (strtoupper($condition) == 'IN') {
            $searchVariable = "_searchInCriteria";
            foreach ($value as $arrayValue) {
                $search_values['inValue'][] = stripslashes($arrayValue);
            }
            $value = $search_values;
        }
        $this->{$searchVariable}['search'][] = array(
            'criteria' => $fieldName,
            'condition' => $condition,
            'value' => ($searchVariable == '_searchCriteria') ? $this->stripSlashesDeep(stripslashes($value)) : $value
        );
        return $this;
    }

    public function mergeSearches()
    {
        if (array_key_exists('search', $this->_searchCriteria) && array_key_exists('search', $this->_searchInCriteria)) {
            $this->_mergedSearches['search'] = array_merge($this->_searchCriteria['search'], $this->_searchInCriteria['search']);
        } elseif (array_key_exists('search', $this->_searchCriteria)) {
            $this->_mergedSearches['search'] = $this->_searchCriteria['search'];
        } elseif (array_key_exists('search', $this->_searchInCriteria)) {
            $this->_mergedSearches['search'] = $this->_searchInCriteria['search'];
        }
    }

    public function addDisplayOption($fieldName, $value)
    {
        $this->_displayData[$fieldName] = $value;
        return $this;
    }

     /**
     * Returns whether error cound shall be done for the message
     * 
     * @param bool $error
     * @return bool
     */
    public function doErrorCount($error)
    {
        return $error;
    }
    
    /**
     * Added to remove slash that Mageento 2 adds to the filter value when there is an underscore
     * 
     * @param string $value
     */
    public function stripSlashesDeep($value)
    {
        if (stripos($value, "\_") !== false) {
            $value = stripslashes($value);
        }
        return $value;
    }
    public abstract function processResponse();

    public abstract function buildRequest();
    
    /*
     * Convert array value from Integer to String
     */
    public function checkValueDataType($value =null){
        if($value == null || $value == 0){
            return '0.0000';
        }else{
            return $value;
        }
    }

    /**
     * @param \Zend_Http_Response $response
     * @return bool|string
     */
    private function convertRestToXml(\Zend_Http_Response $response)
    {
        $restBody = json_decode($response->getBody(), 1); // Json convert to array
        if (isset($restBody["returnObj"])) {
            $responseBody = base64_decode($restBody["returnObj"]);
            $dom = new \DOMDocument();
            $dom->loadXML($responseBody);
            $dom->formatOutput = true;
            $responseBody = $dom->saveXML();
        } else {
            $this->_logger->error("REST service error: Please validate Config ERP URL or REST service config.");
            $this->_logger->error("REST Response Error body: " . $response->getBody());
            $responseBody = $response->getBody();
        }
        return $responseBody;
    }
}
