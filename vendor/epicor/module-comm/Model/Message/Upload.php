<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message;


/**
 * Description of Upload
 *
 * @author David.Wylie
 * @method getConfigBase()
 * @method int getStoreId()
 * @method setStoreId($id)
 * @method setTimeout($seconds)
 * @method setDeadlockCount(int $count)
 * @method int getDeadlockCount()
 * @method int getMaxDeadlockRetries()
 * @method setMaxDeadlockRetries(int $maxRetries)
 */
class Upload extends \Epicor\Comm\Model\Message
{

    /**
     *
     * @var \Epicor\Common\Model\Xmlvarien
     */
    protected $erpData;
    protected $_erpData;
    protected $_stores;
    protected $_storeIds;
    protected $_company = null;
    protected $_maxDeadlockRetriesDefault = 0;

    const DEFAULT_TIMEOUT = 60;

    protected $_validTagsArray = array();
    protected $_mandatoryTagsArray = array();
    protected $_tagsValid = true;
    protected $_returnMessages = array();
    protected $_abandonUpload;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Queue\CollectionFactory
     */
    protected $commResourceMessageQueueCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Message\QueueFactory
     */
    protected $commMessageQueueFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $indexerFactory;

    protected $_indexerModes;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commResourceMessageQueueCollectionFactory = $context->getCommResourceMessageQueueCollectionFactory();
        $this->commMessageQueueFactory = $context->getCommMessageQueueFactory();
        $this->logger = $context->getLogger();
        $this->registry = $context->getRegistry();
        $this->moduleReader=$context->getModuleReader();
        $this->indexerFactory = $context->getIndexerFactory();

        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->_msg_parent = parent::MESSAGE_TYPE_UPLOAD;
        $this->setMessageType('X');
        $this->setStatusCode(self::STATUS_UNKNOWN);
        $queueTimeOut = $this->scopeConfig->getValue('Epicor_Comm/queue_message/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: self::DEFAULT_TIMEOUT;
        $this->setTimeout($queueTimeOut);
        $this->setConfigBase('epicor_comm_field_mapping/');
        $this->setDeadlockCount(0);
        $this->setMaxDeadlockRetries($this->_maxDeadlockRetriesDefault);

    }


    public function resetProcessFlags()
    {
        
    }

    public function processAction()
    {
        
    }

    /**
     * checks if this message is first in the queue for its category type and 
     * returns true if it is.
     * 
     * return bool
     */
    public function readyToProcess()
    {
        $queue = $this->commResourceMessageQueueCollectionFactory->create();
        /* @var $queue \Epicor\Comm\Model\ResourceModel\Message\Queue\Collection */
        $queue->addFieldToFilter('message_category', $this->getMessageCategory());
        $queue->setOrder('created_at', 'ASC');
        $queue_item = $queue->getFirstItem();
        return $queue_item->getMessageId() == $this->getUniqueId();
    }

    /**
     * Add message to process queue
     */
    public function enterProcessQueue()
    {
        $this->setUniqueId(uniqid('', true));
        $queue_item = $this->commMessageQueueFactory->create();
        /* @var $queue_item \Epicor\Comm\Model\Message\Queue */
        $queue_item->setMessageId($this->getUniqueId());
        $queue_item->setMessageCategory($this->getMessageCategory());
        $queue_item->setCreatedAt(microtime(true));
        $queue_item->save();
    }

    /**
     * Remove message to process queue
     */
    public function leaveProcessQueue()
    {
        $queue_item = $this->commMessageQueueFactory->create()->load($this->getUniqueId(), 'message_id');
        /* @var $queue_item Epicor_Comm_Model_Message_Queue */
        $queue_item->delete();
    }

    public function buildResponse()
    {
        $message = $this->getMessageTemplate();
        $message['messages']['response']['body'] = array(
            'status' => array(
                'code' => $this->getStatusCode(),
                'description' => $this->getStatusDescription(),
            )
        );
        $this->setOutXml($message);
    }

    /**
     * Run any setup logic before processing message
     */
    public function beforeProcessAction()
    {
        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_upload_processaction_before', array(
            'data_object' => $this,
            'message' => $this,
        ));
    }

    /**
     * Run any clear down logic before processing message
     */
    public function afterProcessAction()
    {
        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_upload_processaction_after', array(
            'data_object' => $this,
            'message' => $this,
        ));
    }
    
    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $varien_object
     * @return string
     */
    public function processUpload($varien_object = null)
    {
        $this->logInitial();
        $processing = false;
        $runAfterProcess = false;
        try {
            if ($varien_object) {
                $request = $varien_object->getVarienDataFromPath('messages/request/body');
                /* $request Epicor\Common\Model\Xmlvarien */
                $request = $this->processDataMapping($request);
                $this->setRequest($request);
                $xml = $this->getLog()->getXmlIn();
                $legacyHeader = '';
                $rawLegacyHeader = array();
                preg_match("/<legacyHeader>([^<]+)<\/legacyHeader>/", $xml, $rawLegacyHeader);
                if (count($rawLegacyHeader) > 1) {
                    $legacyHeader = $rawLegacyHeader[1];
                }
                $this->setLegacyHeader($legacyHeader);
                $this->beforeProcessAction();
                $processing = true;
                $runAfterProcess = true;
                while ($processing && $this->getDeadlockCount() <= $this->getMaxDeadlockRetries()) {
                    try {
                        $this->logger->info($this->getLog()->getId().' : '.$this->getDeadlockCount());
                        $this->resetProcessFlags();
                        $this->validateXmlschema($xml);
                        $this->processAction();
                        $this->processPayload('upload', 'request');
                        $processing = false;
                    } catch (\Exception $processError) {
                        if ($this->getMaxDeadlockRetries() > 0 && stripos($processError->getMessage(), 'Deadlock') !== false) {
                            $this->setDeadlockCount($this->getDeadlockCount() + 1);
                            if ($this->getDeadlockCount() <= $this->getMaxDeadlockRetries()) {
                                $this->setStatusDescriptionText('Retried ' . $this->getDeadlockCount() . ' time(s) due to deadlocks');
                            } else {
                                throw new \Exception('Failed to process after ' . $this->getDeadlockCount() . ' retries');
                            }
                        } else {
                            throw $processError;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $error_code = str_pad($e->getCode(), 3, 0, STR_PAD_LEFT);

            if (!$this->isErrorStatusCode($error_code) && !$this->isSuccessfulStatusCode($error_code) && !$this->isWarningStatusCode($error_code))
                $error_code = self::STATUS_UNKNOWN;

            $this->setStatusCode($error_code);
            $this->setStatusDescription($e->getMessage());
        }
        if ($runAfterProcess) {
            $this->afterProcessAction();
        }
        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_upload_buildresponse_before', array(
            'data_object' => $this,
            'message' => $this,
        ));
        $this->buildResponse();
        $this->processPayload('upload', 'response');
        $this->eventManager->dispatch(strtolower($this->getMessageType()) . '_upload_buildresponse_after', array(
            'data_object' => $this,
            'message' => $this,
        ));
        $this->processStatusCode();
        return $this->_xml_out;
    }

    public function processStatusCode()
    {
        $desc = $this->getStatusDescription();
        $this->setIsSuccessful(true);
        $status = \Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_SUCCESS;
        $this->setIsSuccessful(true);
        if ($this->isErrorStatusCode()) {
            $this->setIsSuccessful(false);
            $status = \Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_ERROR;
        } else if (!empty($desc)) {
            $this->setIsSuccessful(false);
            $status = \Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_WARNING;
        }
        $this->logCompleted($status);
    }

    /**
     * Generates the message template for use when creating requests.
     * @return type
     */
    public function getMessageTemplate()
    {

        $template = array('messages' => array(
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
                        'source' => $this->_source,
                        'erp' => $this->_erp,
                    ),
                    'body' => array(
                    ),
                )
            )
        );

        if ($this->getHelper()->isLegacyErp()) {
            $template['messages']['response']['header']['legacyheader'] = $this->getLegacyHeader();
        }

        return $template;
    }

    /**
     * Sets Erp Data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $data
     */
    public function setErpData($data)
    {
        $this->erpData = $data;
    }

    /**
     * Returns true|false to whether a data element exists
     * 
     * @param string $configEntry
     * @param \Epicor\Common\Model\Xmlvarien $data
     * @return bool
     */
    public function hasVarienData($configEntry, $data = null)
    {
        if ($data == null)
            $data = &$this->erpData;

        if ($data instanceof \Epicor\Common\Model\Xmlvarien) {
            $fullConfigEntry = $this->getConfigBase() . $configEntry;
            $path = $this->scopeConfig->getValue($fullConfigEntry, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $return_value = $data->hasVarienDataFromPath($path);
        } else
            $return_value = false;

        return $return_value;
    }

    /**
     * Return the data form the varien object for the given config path.
     * @param string $configEntry
     * @param \Epicor\Common\Model\Xmlvarien $data
     * @return varien_object
     */
    public function getVarienData($configEntry, $data = null)
    {
        if ($data == null)
            $data = &$this->erpData;

        if ($data instanceof \Epicor\Common\Model\Xmlvarien) {
            $fullConfigEntry = $this->getConfigBase() . $configEntry;
            $return_value = $data->getVarienData($fullConfigEntry);
        } else
            $return_value = null;

        return $return_value;
    }

    public function getVarienDataFlag($configEntry, $data = null)
    {
        if ($data == null)
            $data = &$this->erpData;

        if ($data instanceof \Epicor\Common\Model\Xmlvarien) {
            $fullConfigEntry = $this->getConfigBase() . $configEntry;
            $return_value = $data->getVarienDataFlag($fullConfigEntry);
        } else
            $return_value = null;

        return $return_value;
    }

    public function getVarienDataFlagWithDefaultConfig($configEntry, $data, $defaultConfig)
    {

        if ($data == null)
            $data = &$this->erpData;

        if ($data instanceof \Epicor\Common\Model\Xmlvarien) {
            $fullConfigEntry = $this->getConfigBase() . $configEntry;
            $fullDefaultConfig = $this->getConfigBase() . $defaultConfig;
            $return_value = $data->getVarienDataFlagWithDefaultConfig($fullConfigEntry, $fullDefaultConfig);
        } else
            $return_value = null;


        return $return_value;
    }

    public function getVarienDataWithDefaultConfig($configEntry, $data, $defaultConfig)
    {
        if ($data == null)
            $data = &$this->erpData;

        if ($data instanceof \Epicor\Common\Model\Xmlvarien) {
            $fullConfigEntry = $this->getConfigBase() . $configEntry;
            $fullDefaultConfig = $this->getConfigBase() . $defaultConfig;
            $return_value = $data->getVarienDataWithDefaultConfig($fullConfigEntry, $fullDefaultConfig);
        } else
            $return_value = null;

        return $return_value;
    }

    public function getVarienDataArray($configEntry, $data = null)
    {
        if ($data == null) {
            $data = &$this->erpData;
        }
        $fullConfigEntry = $this->getConfigBase() . $configEntry;
        return $data->getVarienDataArray($fullConfigEntry);
    }

    /**
     * Set a field in the varent object based on the given config entry.
     * @param type $configEntry
     * @param type $erpData
     * @param type $newValue
     */
    public function setVarienData($configEntry, &$data, $newValue)
    {
        if ($data == null) {
            $data = &$this->erpData;
        }
        $fullConfigEntry = $this->getConfigBase() . $configEntry;

        return $data->setVarienData($fullConfigEntry, $newValue);
    }

    public function isUpdateable($config, $exists = true)
    {
        $path = $this->getConfigBase() . $config;
        $flag = $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
        return (!$exists || $exists == $flag);
    }

    /**
     * @TODO Add full brand and multi language support
     * @param type $langCode
     */
    public function setLanguageSpecificStore($langCode)
    {
        //add code here
    }

    /**
     * 
     * @param string $accountCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpAccount($accountCode, $type = 'Customer')
    {
        return $this->getHelper()->getErpAccountByAccountNumber($accountCode, $type);
    }

    public function process($xml, $messageObj = null)
    {
        $helper = $this->commonXmlHelper->create();
        if (empty($messageObj)) {
            $messageObj = $helper->convertXmlToVarienObject($xml);
        }
        //M1 > M2 Translation Begin (Rule p2-6.5)
        //$this->setStoreId(Mage::app()->getDefaultStoreView()->getId());
        $this->setStoreId($this->storeManager->getDefaultStoreView()->getId());
        //M1 > M2 Translation End
        //$this->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_CODE);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        //M1 > M2 Translation End
        $this->registry->register('isSecureArea', true);
        $response = $this->processUpload($messageObj);
        $this->registry->unregister('isSecureArea');
        return $response;
    }

    /**
     * Loads the stores from the brands provided
     * 
     * @return array
     */
    protected function _loadStores($erpData = null, $throwError = false)
    {

        
        $erpData = ($erpData) ?: $this->erpData;

        $brands = $erpData->getVarienDataArrayFromPath('brands/brand');

        if (is_null($this->_stores)) {
            $this->_stores = array();
            $this->_storeIds = array();

            if (!empty($brands)) {
                if (!is_array($brands)) {
                    $brands = array($brands);
                }

                foreach ($brands as $brand) {
                    $brandStores = $this->getHelper()->getStoreFromBranding($brand->getCompany(), $brand->getSite(), $brand->getWarehouse(), $brand->getGroup());
                    $this->_stores = $this->_stores + $brandStores;
                }
            } else {
                $brandStores = $this->getHelper()->getStoreFromBranding(null);
                $this->_stores = $this->_stores + $brandStores;
            }

            if (empty($this->_stores) && $throwError) {
                throw new \Exception(
                'Provided Brands do not match any stores', self::STATUS_GENERAL_ERROR
                );
            } else {
                foreach ($this->_stores as $store) {
                    $this->_storeIds[] = $store->getId();
                }
            }
        }

        return $this->_stores;
    }

    /**
     * Works out which websites to assign the contact to based on config / branding and currencies
     * 
     * @return array() 
     */
    protected function _getWebsitesForCurrenciesAndBranding($currencies)
    {

        if (!is_array($currencies)) {
            $currencies = array($currencies);
        }

        // check the config for the customer scope
        // 1 - customers per website
        // 0 - global customers

        if ($this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $stores = $this->_loadStores($this->getRequest());

            $websites = array();

            foreach ($stores as $store) {
                /* @var $store Mage_Core_Model_Store */
                if (!in_array($store->getWebsiteId(), $websites) && in_array($store->getWebsite()->getBaseCurrencyCode(), $currencies)) {
                    $websites[] = $store->getWebsiteId();
                }
            }
        } else {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$websites = array(Mage::app()->getDefaultStoreView()->getWebsiteId());
            $websites = array($this->storeManager->getDefaultStoreView()->getWebsiteId());
            //M1 > M2 Translation End
        }

        return $websites;
    }

    /**
     * Works out which websites to assign the contact to based on config / branding 
     * 
     * @return array() 
     */
    protected function _getWebsitesForBranding()
    {

        // check the config for the customer scope
        // 1 - customers per website
        // 0 - global customers

        if ($this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $stores = $this->_loadStores($this->getRequest());

            $websites = array();

            foreach ($stores as $store) {
                /* @var $store Mage_Core_Model_Store */
                if (!in_array($store->getWebsiteId(), $websites)) {
                    $websites[] = $store->getWebsiteId();
                }
            }
        } else {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$websites = array(Mage::app()->getDefaultStoreView()->getWebsiteId());
            $websites = array($this->storeManager->getDefaultStoreView()->getWebsiteId());
            //M1 > M2 Translation End
        }

        return $websites;
    }

    /**
     * Disables all indexing that is set to update on save
     * 
     * Performance - leaving as update on save adds overhead to the message
     */
    protected function _disableIndexing()
    {
        //M1 > M2 Translation Begin (Rule 3)
        // $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
        //foreach ($pCollection as $process) {
        //    if ($process->getMode() == Mage_Index_Model_Process::MODE_REAL_TIME) {
        //        $this->_indexerModes[] = $process->getId();
        //        $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        //    }
        // }
        /** @var \Magento\Framework\Indexer\IndexerInterface[] $indexers */
        $indexers = $this->indexerFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            if($indexer->isScheduled()){
                $this->_indexerModes[] = $indexer->getId();
                $indexer->setScheduled(false);
            }
        }
        //M1 > M2 Translation End
    }

    /**
     * Resets all indexes to what they were before (values stored from _disableIndexing)
     */
    protected function _resetIndexing()
    {
        //M1 > M2 Translation Begin (Rule 3)
        //$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
        //foreach ($pCollection as $process) {
        //    if (in_array($process->getId(), $this->_indexerModes)) {
        //       $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
        //    }
        //}
        /** @var \Magento\Framework\Indexer\IndexerInterface[] $indexers */
        $indexers = $this->indexerFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            if(in_array($indexer->getId(),$this->_indexerModes)){
                $indexer->setScheduled(true);
            }
        }
    }

    public function validateXmlschema($xmlData)
    {
        //M1 > M2 Translation Begin (Rule P2-5.7)
        //$schemaFilePath = Mage::getModuleDir('base', 'Epicor_Common') . DS . "xsd" . DS . 'upload' . DS . strtolower($this->getMessageType()) . ".xsd";
        $schemaFilePath =$this->moduleReader->getModuleDir('etc', 'Epicor_Common') . DIRECTORY_SEPARATOR . "xsd" . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . strtolower($this->getMessageType()) . ".xsd";
        //M1 > M2 Translation End

        //Need to be fixed in M2 Beta3 if (file_exists($schemaFilePath)) {
        if (file_exists($schemaFilePath)) {
            libxml_use_internal_errors(true);
            $xml = new \DOMDocument();
            $xml->preserveWhiteSpace = false;
            $xml->loadXML($xmlData);
            $schemaData = file_get_contents($schemaFilePath);
            $messageType = strtolower($this->getMessageType());
            
            $schemaFilePath = "urn:magento:module:Epicor_Common:etc/xsd/upload/{$messageType}.xsd";
            //get schema data from file path, run $xml->schemaValidate();
            $errors = \Magento\Framework\Config\Dom::validateDomDocument($xml, $schemaFilePath);
            
            if (!empty($errors)) {               
                $this->setStatusCode(self::STATUS_XML_TAG_MISSING);
                throw new \Exception('Failed to validate xml: ' . implode(", ", $errors), '007');
            }
            libxml_use_internal_errors(false);
        }
    }

    /**
     * Loads the companies from the brands provided
     * 
     * @return array
     */
    protected function _loadStoresFromCompanyBranding($erpData = null)
    {


        $erpData = ($erpData) ?: $this->erpData;

        $brands = $erpData->getVarienDataArrayFromPath('brands/brand');
        $stores = array();
        $storeIds = array();

        if (!empty($brands)) {
            if (!is_array($brands)) {
                $brands = array($brands);
            }
            foreach ($brands as $brand) {
                $stores = $this->getHelper()->getStoresFromCompanyBranding($brand->getCompany());
            }
        } else {
            $stores = $this->getHelper()->getStoresFromCompanyBranding(null);
        }

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        }
        return $storeIds;
    }

    /**
     * Returns Message Company
     *
     * @return \Magento\Framework\DataObject $brands
     */
    protected function getCompany()
    {
        if (is_null($this->_company)) {
            $brand = null;
            $erpData = $this->erpData;
            $brands = $erpData->getVarienDataArrayFromPath('brands/brand');
            if (!empty($brands)) {
                if (is_array($brands)) {
                    $brand = $brands[0];
                }
            }
            if (empty($brand) || !$brand->getCompany()) {
                $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            }
            $this->_company = $brand->getCompany();
        }
        return $this->_company;
    }

}
