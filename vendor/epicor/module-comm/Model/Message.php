<?php
/**
 * Copyright © 2019-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


/**
 * Description of Message
 *
 * @author Paul.Ketelle
 * @method getConfigBase
 * @method setStatusCode()
 * @method getStatusCode()
 * @method getMessageSubject()
 * @method setMessageSubject()
 * @method getMessageType()
 * @method setMessageType()
 * @method setMessageSecondarySubject()
 * @method getMessageSecondarySubject()
 * @method setMessageCategory($cat)
 * @method getMessageCategory()
 * @method setMessageId($id)
 * @method getMessageId()
 * @method setLicenseType($type)
 * @method getLicenseType()
 * @method setMessageArray($array)
 * @method array getMessageArray()
 */
abstract class Message extends \Magento\Framework\Model\AbstractModel
{

    const MESSAGE_TYPE_UPLOAD = 'Upload';
    const MESSAGE_TYPE_REQUEST = 'Request';
    const MESSAGE_CATEGORY_PRODUCT = 'Product';
    const MESSAGE_CATEGORY_CATALOG = 'Catalog';
    const MESSAGE_CATEGORY_CUSTOMER = 'Customer';
    const MESSAGE_CATEGORY_LOCATION = 'Location';
    const MESSAGE_CATEGORY_LIST = 'List';
    const MESSAGE_CATEGORY_ORDER = 'Order';
    const MESSAGE_TYPE_BSV = 'BSV';
    const MESSAGE_TYPE_GOR = 'GOR';
    const MESSAGE_TYPE_MSQ = 'MSQ';
    const MESSAGE_CATEGORY_OTHER = 'Other';
    CONST MESSAGE_CATEGORY_XRATE = 'ExchangeRate';
    CONST MESSAGE_CATEGORY_QUOTES = 'Quotes';
    const DATE_FORMAT = 'yyyy-MM-ddTHH:mm:ssZ';
    const DAY_FORMAT = 'yyyy-MM-ddT00:00:00Z';

    protected $_trigger = null;
    protected $_msg_parent = null;
    protected $_xml_in = '';
    protected $_xml_out = '';
    public $_xml_in_log = null;
    protected $_messaging_helper;
    protected $_location_helper;
    protected $_cached = false;
    protected $_cachedStatus = 'none';
    protected $_cachedResponse;
    protected $_cachedRequest;
    protected $_url = '';
    protected $_erp = 'ECC';
    protected $_source = '';

    const STATUS_SUCCESS = '200';
    const STATUS_UNKNOWN = '001';
    const STATUS_MESSAGE_NOT_SUPPORTED = '002';
    const STATUS_REQUEST_FORMAT_ERROR = '003';
    const STATUS_CUSTOMER_NOT_ON_FILE = '004';
    const STATUS_INTERNAL_DEAMON_ERROR = '005';
    const STATUS_ERP_UPDATE_FAILED = '006';
    const STATUS_XML_TAG_MISSING = '007';
    const STATUS_GENERAL_ERROR = '008';
    const STATUS_CARD_ERROR = '009';
    const STATUS_INVALID_LANGUAGE_CODE = '010';
    const STATUS_PRODUCT_NOT_ON_FILE = '011';
    const STATUS_ORDER_NOT_FOUND = '012';
    const STATUS_INVOICE_NOT_FOUND = '012';
    const STATUS_ERP_LICENSE_REQUIRED = '013';
    const STATUS_DELIVERY_METHOD_ERROR = '014';
    const STATUS_INVALID_ACCOUNT_CODE = '015';
    const STATUS_ERP_ACCOUNT_DOESNT_EXIST = '015a';
    const STATUS_VALUES_DO_NOT_TALLY = '016';
    const STATUS_INVALID_TAX_CODE = '017';
    const STATUS_INVALID_PRODUCT_CODE = '018';
    const STATUS_EXPLODED_PRODUCT_NOT_FOUND = '018a';
    const STATUS_EXPLODED_PRODUCT_TYPE_NOT_ALLOWED = '018b';
    const STATUS_SERVICE_OFFLINE = '019';
    const STATUS_INVALID_COMPANY = '020';
    const STATUS_INVALID_TYPE = '021';
    const STATUS_REJECTED = '022';
    const STATUS_PRODUCT_GROUP_NOT_ON_FILE = '022';
    const STATUS_INVALID_PRODUCT_GROUP_PARENTS = '023';
    const STATUS_INVALID_ADDRESS = '024';
    const STATUS_INVALID_CONTACT = '025';
    const STATUS_FILE_NOT_FOUND = '026';
    const STATUS_ERROR_SAVING_FILE = '027';
    const STATUS_ERROR_READING_FILE = '028';
    const STATUS_URL_NOT_AVAILABLE = '029';
    const STATUS_UNKNOWN_FILEID = '030';
    const STATUS_INVALID_RETURNS_NUMBER = '030';
    const STATUS_RETURNS_NUMBER_NOT_ON_FILE = '031';
    const STATUS_INVALID_CUSTOMER_TYPE = '032';
    const STATUS_CUSTOMER_PARENT_NOT_FOUND = '033';
    const STATUS_INVALID_CUSTOMER_PARENT_TYPE = '034';
    const STATUS_ERROR_CREATING_TAX_CLASS = '035';
    const STATUS_INVALID_LOCATION_CODE = '035';
    const STATUS_LOCATION_NOT_ON_FILE = '036';
    const STATUS_LOCATIONS_NOT_ON_FILE = '037';
    const STATUS_CONNECTION_ERROR = 'X1';
    const STATUS_PHP_ERROR = 'X2';
    const STATUS_URL_ERROR = 'X3';
    const STATUS_WARNING = '901';
    const STATUS_VOUCHER_WARNING = '902';
    const STATUS_DUPLICATE_ORDER = '903';
    const STATUS_DUPLICATE_CUSTOMER_REF = '904';
    const STATUS_USER_DEFINED_FIELD_INVALID = '905';
    const STATUS_BRANDING_NOT_SUPPLIED = '906';
    const STATUS_RELATED_PRODUCT_NOT_ON_FILE = '907';
    const STATUS_INVOICE_ADDRESS_NOT_SUPPLIED_ERROR = '908';

    public $success_status_codes = array(
        self::STATUS_SUCCESS => 'Message Successful',
    );
    public $error_status_codes = array(
        self::STATUS_UNKNOWN => 'Unknown',
        self::STATUS_MESSAGE_NOT_SUPPORTED => 'Message %s not supported',
        self::STATUS_REQUEST_FORMAT_ERROR => 'Request format error',
        self::STATUS_CUSTOMER_NOT_ON_FILE => 'Customer account %s not on file',
        self::STATUS_INTERNAL_DEAMON_ERROR => 'Internal deamon error',
        self::STATUS_ERP_UPDATE_FAILED => 'ERP update failed',
        self::STATUS_XML_TAG_MISSING => 'Expected XML tag missing - "%s"',
        self::STATUS_GENERAL_ERROR => 'General error - %s',
        self::STATUS_CARD_ERROR => 'Card error - %s',
        self::STATUS_INVALID_LANGUAGE_CODE => 'Invalid language code %s',
        self::STATUS_PRODUCT_NOT_ON_FILE => 'Product %s not on file',
        self::STATUS_ORDER_NOT_FOUND => 'Order/Invoice %s not found',
        self::STATUS_ERP_LICENSE_REQUIRED => 'License key not valid for this feature',
        self::STATUS_DELIVERY_METHOD_ERROR => 'Delivery method error - %s',
        self::STATUS_INVALID_ACCOUNT_CODE => 'Invalid customer account Code "%s"',
        self::STATUS_ERP_ACCOUNT_DOESNT_EXIST => 'ERP Account Does not exist',
        self::STATUS_VALUES_DO_NOT_TALLY => 'Values do not tally',
        self::STATUS_INVALID_TAX_CODE => '%s tax class "%s" does not exist',
        self::STATUS_INVALID_PRODUCT_CODE => 'Invalid product code "%s"',
        self::STATUS_EXPLODED_PRODUCT_NOT_FOUND => 'Exploded part "%s" with uom "%s" not found',
        self::STATUS_EXPLODED_PRODUCT_TYPE_NOT_ALLOWED => 'Exploded part type "%s" not allowed',
        self::STATUS_SERVICE_OFFLINE => 'Service offline',
        self::STATUS_INVALID_COMPANY => 'Invalid company or company mismatch - "%s"',
        self::STATUS_INVALID_TYPE => 'Invalid %s type - "%s"',
        self::STATUS_REJECTED => '%s rejected - "%s"',
        self::STATUS_PRODUCT_GROUP_NOT_ON_FILE => 'Product group %s not on file',
        self::STATUS_INVALID_PRODUCT_GROUP_PARENTS => 'Invalid product group parents: %s',
        self::STATUS_INVALID_ADDRESS => 'Invalid address (%s): %s',
        self::STATUS_INVALID_CONTACT => 'Invalid contact: %s',
        self::STATUS_CONNECTION_ERROR => 'Connection error',
        self::STATUS_PHP_ERROR => 'PHP error',
        self::STATUS_URL_ERROR => 'Url error',
        self::STATUS_FILE_NOT_FOUND => 'File Not Found',
        self::STATUS_ERROR_SAVING_FILE => 'Error Saving File',
        self::STATUS_ERROR_READING_FILE => 'Error Reading File',
        self::STATUS_URL_NOT_AVAILABLE => 'Url Not Available',
        self::STATUS_UNKNOWN_FILEID => 'Unknown File ID - "%s"',
        self::STATUS_INVALID_RETURNS_NUMBER => 'Returns code is empty - "%s"',
        self::STATUS_RETURNS_NUMBER_NOT_ON_FILE => 'Returns code is not on file - "%s"',
        self::STATUS_INVALID_CUSTOMER_TYPE => 'Unknown Customer Type - "%s"',
        self::STATUS_CUSTOMER_PARENT_NOT_FOUND => 'Parent account not found - "%s"',
        self::STATUS_INVALID_CUSTOMER_PARENT_TYPE => 'Parent type is invalid - "%s". %s',
        self::STATUS_ERROR_CREATING_TAX_CLASS => '%s tax class "%s" does not exist and was not created due to error',
        self::STATUS_INVALID_CUSTOMER_PARENT_TYPE => 'Parent type is invalid - "%s". %s',
        self::STATUS_INVALID_LOCATION_CODE => 'Location code is invalid - "%s"',
        self::STATUS_LOCATION_NOT_ON_FILE => 'Location code is not on file - %s',
        self::STATUS_BRANDING_NOT_SUPPLIED => 'Branding not Supplied, but is Required. Message Rejected',
        self::STATUS_INVOICE_ADDRESS_NOT_SUPPLIED_ERROR => 'No Invoice Address Supplied'
    );
    public $warning_status_codes = array(
        self::STATUS_WARNING => 'Warning',
        self::STATUS_VOUCHER_WARNING => 'Voucher warning',
        self::STATUS_DUPLICATE_ORDER => 'Duplicate Order',
        self::STATUS_DUPLICATE_CUSTOMER_REF => 'Duplicate Customer Ref',
        self::STATUS_RELATED_PRODUCT_NOT_ON_FILE => 'Warning: Related Product Not On File',
        self::STATUS_USER_DEFINED_FIELD_INVALID => 'Invalid User Defined Fields',
    );
    public $forceNotificationsForStatusCodes = array(
        self::STATUS_DUPLICATE_CUSTOMER_REF,
    );

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Common\Helper\XmlFactory
     */
    protected $commonXmlHelper;

    /**
     * @var \Epicor\Comm\Helper\MessagingFactory
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\LocationsFactory
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Common\Model\DataMapping
     */
    protected $dataMapping;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->commMessageLogFactory = $context->getCommMessageLogFactory();
        $this->request = $context->getRequest();
        $this->storeManager = $context->getStoreManager();
        $this->eventManager = $context->getEventManager();
        $this->commonXmlHelper = $context->getCommonXmlHelper();
        $this->commMessagingHelper = $context->getCommMessagingHelper();
        $this->commLocationsHelper = $context->getCommLocationsHelper();
        $this->dataMapping = $context->getDataMappingFactory();
        $this->commonXmlvarienFactory = $context->getCommonXmlvarienFactory();

        $this->_source = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/source', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        parent::__construct($context, $context->getRegistry(), $resource, $resourceCollection, $data);


    }


    /**
     * Get Error Code Description
     * @param string $code
     * @param mixed $args [optional]
     * @return type
     */
    public function getErrorDescription($code)
    {
        $args = func_get_args();
        $args[0] = $this->error_status_codes[$code];
        array_push($args, null, null, null, null, null, null, null);
        return call_user_func_array('sprintf', $args);
        return sprintf($this->error_status_codes[$code], $argv);
    }

    public function getWarningDescription($code)
    {
        $args = func_get_args();
        $args[0] = $this->warning_status_codes[$code];
        array_push($args, null, null, null, null, null, null, null);
        return call_user_func_array('sprintf', $args);
        return sprintf($this->warning_status_codes[$code], $argv);
    }

    /**
     * Get Message Trigger Action
     *
     * @return string
     */
    public function getErrorAction($error = true)
    {
        if ($error) {
            return $this->scopeConfig->getValue($this->getConfigBase(\Magento\Store\Model\ScopeInterface::SCOPE_STORE) . 'error_action');
        } else {
            return $this->scopeConfig->getValue($this->getConfigBase(\Magento\Store\Model\ScopeInterface::SCOPE_STORE) . 'warning_action');
        }
    }

    /**
     * Get Error Notification Severity
     *
     * @return string
     */
    public function getNotificationSeverity($error = true)
    {
        if ($error) {
            return $this->scopeConfig->getValue($this->getConfigBase() . 'error_severity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            return $this->scopeConfig->getValue($this->getConfigBase() . 'warning_severity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * Is Admin Nofification Required on Error
     *
     * @return bool
     */
    public function isAdminNotificationRequired($error = true)
    {
        if ($error) {
            return $this->scopeConfig->getValue($this->getConfigBase() . 'error_magento_notifcation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            return $this->scopeConfig->getValue($this->getConfigBase() . 'error_magento_notifcation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
    

    /**
     * Is User Nofification Required on Error
     *
     * @return bool
     */
    public function isUserNotificationRequired($error = true)
    {
        if ($error)
            return $this->scopeConfig->isSetFlag($this->getConfigBase() . 'error_user_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        else
            return $this->scopeConfig->isSetFlag($this->getConfigBase() . 'warning_user_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * get User Nofification message on Error
     *
     * @return bool
     */
    public function userNotificationMessage($error = true, $genericErrorTxt, $erpErrorTxt)
    {
        if ($error) {
             $erpError =  $this->scopeConfig->isSetFlag($this->getConfigBase() . 'error_user_notification_erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $erpError =  $this->scopeConfig->isSetFlag($this->getConfigBase() . 'warning_user_notification_erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        if ($erpError) {
            $errorText = $erpErrorTxt;
        } else {
            $errorText = $genericErrorTxt;
        }

        return $errorText;
    }

    /**
     *
     * Is Message Enabled, Is System Online, Is System Comms Enabled
     * and is the message enabled for sent trigger point
     *
     * @param string $trigger
     * @param bool $ignore_offline_status
     * @return bool
     */
    public function isActive($trigger = null, $ignore_offline_status = false, $ignore_licensing_check = false)
    {
        $licensed = ($ignore_licensing_check) ? $ignore_licensing_check : $this->isLicensed();
        $active = $licensed && $this->getConfigFlag('active') && !$this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/disable_comms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($active && !$ignore_offline_status)
            $active = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/failed_msg_online', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($active && !is_null($trigger))
            $active = $this->getConfigFlag($trigger);

        return $active;
    }

    public function isLicensed()
    {
        $licensed = false;
        $valid_license_types = $this->getHelper()->getValidLicenseTypes();
        $message_valid_license_types = $this->getLicenseType();
        if (!is_array($message_valid_license_types))
            $message_valid_license_types = array($message_valid_license_types);

        foreach ($message_valid_license_types as $license_type) {
            $licensed = in_array($license_type, $valid_license_types);
            if ($licensed)
                break;
        }

        return $licensed;
    }

    /**
     * Is Email Nofification Required on Error
     *
     * @return bool
     */
    public function isEmailNotificationRequired($error = true)
    {
        if ($error) {
            return $this->scopeConfig->getValue($this->getConfigBase() . 'error_email_notifcation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            return $this->scopeConfig->getValue($this->getConfigBase() . 'warning_email_notifcation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * Returns the XML message sent.
     * This can be the message sent to an ERP or the response to an upload.
     * @return string
     */
    public function getSentXml()
    {
        return $this->_xml_out;
    }

    /**
     * Sets the XML message to be sent.
     * This can be the message sent to an ERP or the response to an upload.
     * @param string $value
     * @return this
     */
    public function setSentXml($xml)
    {
        $this->_xml_out = $value;
        return $this;
    }

    /**
     * Returns the XMl message received.
     * This can be the response to an XML request or an upload from the erp.
     * @return string
     */
    public function getReceivedXml()
    {
        return $this->_xml_in;
    }

    /**
     * Sets the XML message received.
     * This can be the response to an XML request or an upload from the erp.
     * @param string $value
     * @return this
     */
    public function setReceivedXml($xml)
    {
        $this->_xml_in = $value;
        return $this;
    }

    /**
     *
     * Initalise Log for message
     *
     * @param bool $logAll
     */
    protected function logInitial($logAll = true)
    {
        $log = $this->commMessageLogFactory->create();
        /* @var $log \Epicor\Comm\Model\Message\Log */
        $msgType = $this->getMessageType();
        $log->setMessageType($msgType);
        $log->setMessageParent($this->_msg_parent);
        $log->setCached($this->_cachedStatus);
        $category = $this->getMessageCategory();
        $log->setMessageCategory($category);
        $log->setXmlIn($this->_xml_in_log);
        $log->setXmlOut($this->_xml_out);
        $log->setUrl($this->request->getServer('REQUEST_URI'));
        $log->setStore($this->storeManager->getStore()->getName());
        $log->setErpUrl($this->_url);
        $log->startTiming();
        $log->setMessageStatus($log::MESSAGE_STATUS_INPROGRESS);
        $this->eventManager->dispatch('log_message_initial', array('log' => $log));
        if ($logAll) {
            $log->save();
        }
        $this->setLog($log);
    }

    /**
     *
     * Get Message Specific Store Config Values
     *
     * @param string $config
     * @return string
     */
    public function getConfig($config)
    {
        $path = $this->getConfigBase() . $config;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Message Specific Store Config Flag Values
     *
     * @param $config
     * @param string $scope
     * @param null $store
     * @return bool
     */
    public function getConfigFlag($config, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store = null)
    {
        $path = $this->getConfigBase() . $config;
        return $this->scopeConfig->isSetFlag($path, $scope, $store);
    }

    /**
     *
     * Finalise Log for Message
     *
     * @param string $status
     */
    protected function logCompleted($status, $logAll = true)
    {
        // $this->status will only be set if status has changed in an observer
        if($this->getStatus()){
            $status = $this->getStatus();
        }
        $log = $this->getLog();
        $log->setXmlIn($this->_xml_in_log);
        $log->setXmlOut($this->_xml_out);
        $log->endTiming();
        $code = $this->getStatusCode();
        $log->setStatusCode($code);
        $desc = $this->getStatusDescription();
        $log->setStatusDescription($desc);
        $log->setMessageStatus($status);
        $log->setCached($this->_cachedStatus);
        $log->setUrl($this->request->getServer('REQUEST_URI'));
        $log->setStore($this->storeManager->getStore()->getName());
        $log->setErpUrl($this->_url);
        $subject = $this->getMessageSubject();
        $log->setMessageSubject($subject);
        $secondarySubject = $this->getMessageSecondarySubject();

        $log->setMessageSecondarySubject($secondarySubject);
        if ($logAll) {
            $log->save();
        }
        $this->eventManager->dispatch('log_message_completed', array('log' => $log));
    }

    abstract function getMessageTemplate();

    protected function setOutXml($array)
    {
        $this->setMessageArray($array);
        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_converttoxml_before', array(
            'data_object' => $this,
            'message' => $this,
        ));

        $this->_xml_out = $this->commonXmlHelper->create()->convertArrayToXml($this->getMessageArray());

        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_request_converttoxml_after', array(
            'data_object' => $this,
            'message' => $this,
        ));
    }

    protected function getOutXml()
    {
        return $this->_xml_out;
    }

    public function getInXml()
    {
        return $this->_xml_in;
    }

    public function setInXml($xml)
    {
        $logxml = $xml;
        if (!$this->_xml_in_log) {
            if (mb_detect_encoding($xml) != 'UTF-8') {
                preg_match('/charset="?([^" ]+)/', @$_SERVER['HTTP_CONTENT_TYPE'], $matches);
                $charset = (isset($matches[1]) && !empty($matches[1])) ? $matches[1] : 'UTF-8';
                $logxml = mb_convert_encoding($xml, 'UTF-8', $charset);
            }
            $this->_xml_in_log = $logxml;
        }

        $this->_xml_in = $xml;
    }

    public function setStatusDescription($str)
    {
        $oldstr = $this->getStatusDescriptionText();
        if (empty($oldstr)) {
            $newstr = $str;
        } else {
            $newstr = "$oldstr: $str";
        }
        $this->setStatusDescriptionText($newstr);
    }

    public function getStatusDescription()
    {
        return $this->getStatusDescriptionText();
    }

    /**
     *
     * Is Status Code a Success Code
     *
     * @return bool
     */
    public function isSuccessfulStatusCode($statusCode = null)
    {
        if (is_null($statusCode))
            $statusCode = $this->getStatusCode();

        return array_key_exists($statusCode, $this->success_status_codes);
    }

    /**
     *
     * Is Status Code a Warning Code
     *
     * @return bool
     */
    public function isWarningStatusCode($statusCode = null)
    {
        if (is_null($statusCode))
            $statusCode = $this->getStatusCode();

        return array_key_exists($statusCode, $this->warning_status_codes);
    }

    /**
     *
     * Is Status Code an Error Code
     *
     * @return bool
     */
    public function isErrorStatusCode($statusCode = null)
    {
        if (is_null($statusCode))
            $statusCode = $this->getStatusCode();

        return array_key_exists($statusCode, $this->error_status_codes);
    }

    /**
     *
     * Get the Messaging Helper
     *
     * @param string $helper
     * @return \Epicor\Comm\Helper\Messaging
     */
    public function getHelper()
    {
        if (!$this->_messaging_helper)
            $this->_messaging_helper = $this->commMessagingHelper->create();

        return $this->_messaging_helper;
    }

    /**
     *
     * Get the Messaging Helper
     *
     * @param string $helper
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getLocationHelper()
    {
        if (!$this->_location_helper)
            $this->_location_helper = $this->commLocationsHelper->create();

        return $this->_location_helper;
    }

    /**
     * Gets an array of child data from a group Array
     *
     * e.g. unitOfMeasures > unitOfMeasure
     * ('unit_of_measures', 'unit_of_measure', $erpData)
     *
     * will return an array of the data in the unitOfMeasure tag
     *
     * @param string $groupRef
     * @param string $childRef
     * @param \Epicor\Comm\Model\Xmlvarien $erpData
     *
     * @return array
     */
    public function _getGroupedDataArray($groupRef, $childRef, $erpData)
    {
        $groupRef =( isset($erpData[$groupRef]) ) ? $erpData[$groupRef]:false;
       // $childRef = 'getasarray' . ucfirst($this->getHelper()->convertStringToCamelCase($childRef));
        $group = $groupRef;
        //$result = $group && is_array($group) ? $group[$childRef] : array();
        if($group && is_array($group)){
            if(isset($group[$childRef])){
                return (isset($group[$childRef][0])) ? $group[$childRef]: [$group[$childRef]];
            }else{
                return [];
            }
            
        }else{
            return [];            
        }
      //  return $group && is_array($group) ? (isset($group[$childRef][0])) ? $group[$childRef]: [$group[$childRef]] : array();
    }

    /**
     * Gets an array of child data from a group Object
     *
     * e.g. unitOfMeasures > unitOfMeasure
     * ('unit_of_measures', 'unit_of_measure', $erpData)
     *
     * will return an array of the data in the unitOfMeasure tag
     *
     * @param string $groupRef
     * @param string $childRef
     * @param \Epicor\Comm\Model\Xmlvarien $erpData
     *
     * @return array
     */
    public function _getGroupedData($groupRef, $childRef, $erpData)
    {
        $groupRef = 'get' . ucfirst($this->getHelper()->convertStringToCamelCase($groupRef));
        $childRef = 'getasarray' . ucfirst($this->getHelper()->convertStringToCamelCase($childRef));
        $group = $erpData->$groupRef();
        return $group && is_object($group) ? $group->$childRef() : array();
    }

    protected function processPayload($type, $action)
    {

        $this->eventManager->dispatch('ecc_' . $type . '_processpayload_' . $action, array(
            'data_object' => $this,
            'message' => $this,
        ));


        $this->eventManager->dispatch('ecc_' . $type . '_' . strtolower($this->getMessageType()) . '_processpayload_' . $action, array(
            'data_object' => $this,
            'message' => $this,
        ));
        if (($type == 'request' && $action == 'request') || ($type == 'upload' && $action == 'response')) {
            $this->_xml_out = $this->commonXmlHelper->create()->convertArrayToXml($this->getMessageArray());
        }
        return;
    }
    
     public function unsetStatusDescription()
    {
        $this->setStatusDescriptionText('');
    }

    /**
     * @param \Epicor\Common\Model\Xmlvarien $request
     * @return mixed
     */
    protected function processDataMapping($request)
    {
        $dataMappingModal = $this->dataMapping->create();
        /* @var $dataMappingModal \Epicor\Common\Model\DataMapping */
        $mappingData = $dataMappingModal->getByType($this->getMessageType());
        if(count($mappingData) > 0) {
            foreach ($mappingData as $data){
                /* @var $data \Epicor\Common\Model\DataMapping */

                //get UD Value
                $mappedValue = null;
                if(preg_match('/"/', $data->getMappedTag())) {
                    $mappedValue = str_replace('"',"",$data->getMappedTag());
                } else {
                    $mappedTags = explode(">",$data->getMappedTag());
                    $mappedValue = $this->getUserDefinedValue($request,$mappedTags);
                }
                if(!$mappedValue) {
                    continue;
                    //Skip to set in request
                }

                //Get Request TaG
                $this->getRequestTag($request, $data->getOrignalTag(), $mappedValue);
            }
        }

        return $request;
    }

    /**
     * get User Defined Value.
     *
     * @param array $requestTag RequestTag.
     * @param array $mappedTags MappedTags.
     *
     * @return bool|string
     */
    protected function getUserDefinedValue($requestTag, $mappedTags)
    {
        $requestValue = null;
        foreach ($mappedTags as $mappedTag) {
            $tag = ucfirst($mappedTag);
            $method = 'get' . $tag;
            if($requestTag->$method()){
                $requestTag = $requestTag->$method();
            } else {
                $requestTag = $requestTag->getData($mappedTag) ?: $requestTag->getData($this->varienFormat($mappedTag));
            }
            if (is_object($requestTag)) {
                continue;
            } elseif (is_string($requestTag)) {
                return $requestTag;
            } else {
                //no user defined Data
                return false;
            }//enf if
        }//end foreach

        return false;

    }//end getUserDefinedValue()


    private function varienFormat($key)
    {
        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $key));
    }

    protected function getRequestTag($request, $orignalTag,$mappedValue)
    {

        $isAttribute = false;
        $attributeName = null;
        $tags = $orignalTag;
        if (strpos($orignalTag, '+') !== false) {
            $originalTagWithAttr = explode("+", $orignalTag);
            if(count($originalTagWithAttr) == 2){
                $tags = $originalTagWithAttr[0]; // tags
                $attributeName = $originalTagWithAttr[1]; // attribute
                $isAttribute = true;
            } else {
                //skip to add value
                return;
            }
        }

        $tempTagSequence = $tagSequence = array();

        if($tags){
            $tempTagSequence = $tagSequence = explode(">", $tags);
        }


        //$request->setVarienDataFromPath(implode("/",$originalTags),$mappedValue);
        $requestTag = $request;
        $prevTag = null;
        $isNewObjArray = false;
        $skipAttribute = false;
        foreach ($tagSequence as $key => $value) {
            $tag = ucfirst($value);
            $getMethod = 'get' . $tag;
            $setMethod = 'set' . $tag;
            if (is_object($requestTag->$getMethod())) {
                $requestTag = $requestTag->$getMethod();
                $prevTag = $value;
                unset($tempTagSequence[$key]);
                continue;
            } elseif (is_string($requestTag->$getMethod()) && (count($tagSequence)-1) == $key) {
                unset($tempTagSequence[$key]);
                $requestTag->$setMethod($mappedValue);
                break;
            } elseif (is_array($requestTag->$getMethod())) {
                unset($tempTagSequence[$key]);
                foreach ($requestTag->$getMethod() as $keyR => $valueR) {
                    if ($isAttribute) { //with attribute key
                        $this->getRequestTag(
                            $valueR,
                            implode("+", array(implode(">", $tempTagSequence), $attributeName)),
                            $mappedValue
                        );
                    } else { //without attribute key
                        $this->getRequestTag($valueR, implode(">", $tempTagSequence), $mappedValue);
                    }
                }
                $skipAttribute = true;
                break;
            } else {
                //new variant object
                //$requestTag->setVarienDataFromPath(implode("/",$tempTagSequence),$mappedValue);
                $pathParts = $tempTagSequence;
                if (!$isAttribute) {
                    $lastTag = array_pop($pathParts);
                }

                foreach ($pathParts as $newTag) {
                    if (!$requestTag->getData($newTag)) {
                        $setNewObj = 'set' . $newTag;
                        $getNewObj = 'get' . $newTag;
                        if($prevTag === $newTag."s"){
                            //create Array object
                            $isNewObjArray = true;
                            $requestTag->$setNewObj([$this->commonXmlvarienFactory->create()]);
                        } else {
                            $requestTag->$setNewObj($this->commonXmlvarienFactory->create());
                        }
                        $prevTag = $newTag;

                    }
                    $requestTag = $requestTag->$getNewObj();
                    if($isNewObjArray && isset($requestTag[0])){
                        $requestTag = $requestTag[0];
                    }
                }

                if (!$isAttribute) {
                    $setValMethod = 'set' . $lastTag;
                    $requestTag->$setValMethod($mappedValue);
                }
                break;
            }
        }

        //Attribute
        if ($isAttribute && !$skipAttribute) {
            $tagAttribute = ucfirst($attributeName);
            $setAttribute = 'set'.$tagAttribute;
            if ($requestTag->getData("_attributes")) {
                $requestTag->getData("_attributes")->$setAttribute($mappedValue);
            } else {
                $requestTag->setData("_attributes", $this->commonXmlvarienFactory->create());
                $requestTag->getData("_attributes")->$setAttribute($mappedValue);
            }
        }
        return true;
    }
}
