<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

use Magento\Store\Model\ScopeInterface;

class Cron
{


    const DATEFORMATE = "Y-m-d H:i:s";

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Model\ResourceModel\File\CollectionFactory
     */
    protected $commonResourceFileCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesResourceModelOrderCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    /**
     * @var \Epicor\Comm\Model\Message\QueueFactory
     */
    protected $commMessageQueueFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\SynFactory
     */
    protected $commMessageRequestSynFactory;

    /**
     * @var Epicor\Comm\Logger\Autosync\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Syn\Log\CollectionFactory
     */
    protected $commResourceSynLogCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Syn\LogFactory
     */
    protected $commSynLogFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Entity\Register\CollectionFactory
     */
    protected $commResourceEntityRegisterCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory
     */
    protected $commResourceCustomerReturnModelCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $catalogResourceModelCategoryCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Catalog\Category\Image\Sync
     */
    protected $commCatalogCategoryImageSyncHelper;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\FulltextFactory
     */
    protected $catalogSearchResourceModelFulltextFactory;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;


    protected $salesArOrderCollectionFactory;

    protected $quoteArFactory;

    protected $arpaymentsHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var \Epicor\Common\Model\Cron|null
     */
    private $commonCron;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\ResourceModel\File\CollectionFactory $commonResourceFileCollectionFactory,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesResourceModelOrderCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Model\Message\QueueFactory $commMessageQueueFactory,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Epicor\Comm\Model\Message\Request\SynFactory $commMessageRequestSynFactory,
        \Epicor\Comm\Logger\Autosync\Logger $logger,
        \Magento\Framework\App\CacheInterface $cache,
        \Epicor\Comm\Model\ResourceModel\Syn\Log\CollectionFactory $commResourceSynLogCollectionFactory,
        \Epicor\Comm\Model\Syn\LogFactory $commSynLogFactory,
        \Epicor\Comm\Model\ResourceModel\Entity\Register\CollectionFactory $commResourceEntityRegisterCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory $commResourceCustomerReturnModelCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catalogResourceModelCategoryCollectionFactory,
        \Epicor\Comm\Helper\Catalog\Category\Image\Sync $commCatalogCategoryImageSyncHelper,
        \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\CollectionFactory $salesArOrderCollectionFactory,
        \Epicor\Customerconnect\Model\ArPayment\QuoteFactory $quoteArFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\CatalogSearch\Model\ResourceModel\FulltextFactory $catalogSearchResourceModelFulltextFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Epicor\Common\Model\Cron $commonCron = null
    )
    {
        $this->storeManager = $storeManager;
        $this->catalogSearchResourceModelFulltextFactory = $catalogSearchResourceModelFulltextFactory;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->commonResourceFileCollectionFactory = $commonResourceFileCollectionFactory;
        $this->commFileHelper = $commFileHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->salesResourceModelOrderCollectionFactory = $salesResourceModelOrderCollectionFactory;
        $this->eventManager = $eventManager;
        $this->commHelper = $commHelper;
        $this->commMessageLogFactory = $commMessageLogFactory;
        $this->commMessageQueueFactory = $commMessageQueueFactory;
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->commMessageRequestSynFactory = $commMessageRequestSynFactory;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->commResourceSynLogCollectionFactory = $commResourceSynLogCollectionFactory;
        $this->commSynLogFactory = $commSynLogFactory;
        $this->commResourceEntityRegisterCollectionFactory = $commResourceEntityRegisterCollectionFactory;
        $this->commResourceCustomerReturnModelCollectionFactory = $commResourceCustomerReturnModelCollectionFactory;
        $this->catalogResourceModelCategoryCollectionFactory = $catalogResourceModelCategoryCollectionFactory;
        $this->resourceConfig = $resourceConfig;
        $this->commCatalogCategoryImageSyncHelper = $commCatalogCategoryImageSyncHelper;
        $this->quoteRepository = $quoteRepository;
        $this->_localeDate = $localeDate;
        $this->salesArOrderCollectionFactory = $salesArOrderCollectionFactory;
        $this->quoteArFactory = $quoteArFactory;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->ioFile = $ioFile;
        $this->commonCron = $commonCron;
    }

    /**
     * Function used to submit files to the ERP in the background
     */
    public function submitFilesToErp()
    {
        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(\Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        //M1 > M2 Translation End
        if (!$this->registry->registry('isSecureArea')) {
            $this->registry->register('isSecureArea', true);
        }

        $frequency = $this->scopeConfig->getValue('epicor_comm_enabled_messages/fsub_request/frequency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($frequency == 'scheduled') {
            $maxFiles = $this->scopeConfig->getValue('epicor_comm_enabled_messages/fsub_request/frequency_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $collection = $this->commonResourceFileCollectionFactory->create();
            /* @var $collection Epicor_Common_Model_Resource_File_Collection */
            $collection->addFieldToFilter('source', 'web');
            $collection->addFieldToFilter('action', array('neq' => ''));
            $collection->addOrder('created_at', 'ASC');
            if (!empty($maxFiles)) {
                $collection->setCurPage(1, $maxFiles);
            }

            $helper = $this->commFileHelper;
            /* @var $helper Epicor_Comm_Helper_File */

            foreach ($collection->getItems() as $file) {
                /* @var $file Epicor_Common_Model_File */
                $helper->submitFile($file, $file->getAction());
            }
        }

        $this->registry->unregister('isSecureArea');
    }

    /**
     * Function used to update orders from the ERP
     */
    public function scheduleSod()
    {

//        $helper = Mage::helper('epicor_comm/messaging');
//        /* @var $helper Epicor_Comm_Helper_Messaging */
//        $sod = Mage::getModel('epicor_comm/message_request_sod');
//        /* @var $sod Epicor_Comm_Model_Message_Request_Sod */
//        if ($sod->isActive('schedule')) {
//            $orders = $helper->getNextOrders();
//            foreach ($orders as $order) {
////send sod
//                /* @var $order Mage_Sales_Model_Order */
//                $sod = Mage::getModel('epicor_comm/message_request_sod');
//                /* @var $sod Epicor_Comm_Model_Message_Request_Sod */
//                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
//                $sod->setCustomer($customer);
//                $sod->setOrder($order);
//                $sod->setIsOrderUpdate(true);
//                $sod->setSessionId('Scheduled Sod');
//                $sod->sendMessage();
//            }
//        }
    }

    /**
     * Check for any GORs that have not been sent successfully
     */
    public function offlineOrders()
    {
        $curentDate = $this->dateTimeFactory->create()->gmtDate();
        $offlineGorFileName = 'offlinegorexist.php';
        $offlineGorFolder = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) .
            DIRECTORY_SEPARATOR . 'offlinegor';
        $offlineGorFileExist = $offlineGorFolder . DIRECTORY_SEPARATOR . $offlineGorFileName;

        $timeDifference = 0;
        if ($this->driverFile->isExists($offlineGorFileExist)) {
            $fileModifiedDate = filemtime($offlineGorFileExist);
            $curentDateTimeStamp = strtotime($curentDate);
            $timeDifference = round(abs($curentDateTimeStamp - $fileModifiedDate) / 60);
        }

        if ($this->driverFile->isExists($offlineGorFileExist) && $timeDifference <= 60) {
            /*$this->commHelper->sendMagentoMessage(
                    "Nothing to do - $curentDate - $timeDifference", "Offline Order Processing", \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE
            );*/
            return;
        } else {

            if (!$this->driverFile->isExists($offlineGorFolder)) {
                $this->ioFile->mkdir($offlineGorFolder, 0775);
            }
            $this->ioFile->open(array('path' => $offlineGorFolder));
            $this->ioFile->write($offlineGorFileName, '', 0777);
            /*$this->commHelper->sendMagentoMessage(
                    "Lets do something - $curentDate - $timeDifference", "Offline Order Processing", \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE
            );*/

            $helper = $this->commMessagingHelper;
            /* @var $helper Epicor_Comm_Helper_Messaging */
            $helper->setPhpTimeLimits();
            $helper->setPhpMemoryLimits();


            $msg = $helper->getHeartBeatMessage('Offline Deamon');

            if ($msg->isActive(null, true)) {
                $continue = $msg->sendMessage();
            } else {
                $continue = true;
            }

            if ($continue) {

                $statuses = array();
                foreach (explode(',', $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/valid_order_statuses', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) as $status) {
                    $statuses[] = array('eq' => $status);
                }
                $collection = $this->salesResourceModelOrderCollectionFactory->create();
                /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
                $collection->addFieldToFilter('ecc_gor_sent', 0);
                $collection->addFieldToFilter('arpayments_quote', 0);
                $collection->addFieldToFilter('status', $statuses);
                $collection->addFieldToFilter('created_at', array("lteq" => date(DATE_ATOM, strtotime('10 mins ago'))));
                $collection->setPageSize(300);
                $orders = $collection->getItems();

                $ordersSent = 0;
                $ordersFailed = 0;
                $ordersSuccessMsg = 'Successful: ';
                $ordersFailedMsg = 'Failed: ';
                foreach ($orders as $order) {

                    // if gor not active, don't try to send
                    $gorOnline = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/active',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId());

                    if (!$gorOnline) {
                        continue;
                    }


                    if (!$this->registry->registry("offline_order_{$order->getId()}")) {
                        $this->registry->register("offline_order_{$order->getId()}", true);
                    }

                    $this->eventManager->dispatch('sales_order_save_commit_after', array(
                        'data_object' => $order,
                        'order' => $order,
                    ));

                    $this->registry->unregister("offline_order_{$order->getId()}");
                    $orderId = $order->getIncrementId();
                    if ($order->getEccGorSent() == \Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_ERROR) {
                        $ordersFailed++;
                        $ordersFailedMsg .= "</br>$orderId";
                        $log = $this->registry->registry('last_log');
                        if ($log) {
                            $logId = $log->getId();
                            $ordersFailedMsg .= "<a onClick='goToMessageUrl(\"$logId\");'> View Log</a>";
                        }
                        $ordersSent++;
                    } else if ($order->getEccGorSent() == \Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_SENT) {
                        $ordersSuccessMsg .= "$orderId,";
                        $ordersSent++;
                    }
                }
                if ($ordersSent > 0) {
                    $this->commHelper->sendMagentoMessage(
                        "sent $ordersSent orders of which $ordersFailed failed <br/>$ordersSuccessMsg<br/>$ordersFailedMsg", "Offline Order Processing", \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE
                    );
                }
            }
            if ($this->driverFile->isExists($offlineGorFileExist)) {
                $this->driverFile->deleteFile($offlineGorFileExist);
            }
        }
    }

    public function cleanLog()
    {
        $log = $this->commMessageLogFactory->create();
        /* @var $log Epicor_Comm_Model_Message_Log */
        $log->clean();
    }

    public function cleanMessageQueue()
    {
        $queue = $this->commMessageQueueFactory->create();
        /* @var $queue Epicor_Comm_Model_Message_Queue */
        $queue->clean();
    }

    /**
     * Schedule MSQ
     */
    public function scheduleMsq()
    {
        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(\Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        //M1 > M2 Translation End
        if (!$this->registry->registry('SkipEvent')) {
            $this->registry->register('SkipEvent', true);
        }
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq Epicor_Comm_Model_Message_Request_Msq */
        // check if schedule MSQ is enabled && not running
        if ($msq->isActive('scheduledmsq')) {

            $erpAccountId = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/scheduledmsqcustomer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $products = $helper->getNextScheduledMsqProducts();
            $msq->setTrigger('scheduled');
            $msq->setCustomerGroupId($erpAccountId);
            $msq->setSaveProductDetails(true);
            $msq->setSessionId('Scheduled Msq');

            foreach ($products as $product) {
                $msq->addProduct($product, 1, false);
            }

            $msq->sendMessage();
            unset($products);
        }
        $this->registry->unregister('SkipEvent');
    }

    public function autoSync()
    {
        $syncTypes = $this->commMessagingHelper->getAutoSyncType();
        foreach ($syncTypes as $syncType) {
            $prefixpath = 'epicor_comm_enabled_messages/syn_request/';
            if ($this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                $simple = $this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_simple_messages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $advanced = $this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_advanced_messages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $language = $this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_language', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $freq_value = $this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_frequency_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $freq_unit = $this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_frequency_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $freqValue = $freq_value * $freq_unit;
                $syncStores = explode(',', $this->scopeConfig->getValue($prefixpath . $syncType . '_autosync_stores', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                $nextDateInSynConfig = $this->commonCron->getDirectConfigValue($prefixpath . $syncType . '_autosync_next_date_from_in_syn');
                $nextRunDateConfig = $this->commonCron->getDirectConfigValue($prefixpath . $syncType . '_autosync_next_rundate');
                $startDateConfig = $this->commonCron->getDirectConfigValue($prefixpath . $syncType . '_autosync_start_date');
                $nextDateFromInSyn = strtotime($nextDateInSynConfig);
                $startDateAndTime = strtotime($startDateConfig);
                $nextRunDateAndTime = strtotime($nextRunDateConfig);

                $curentDate = $this->_localeDate->date()->format(self::DATEFORMATE);
                $currentTimestamp = strtotime($curentDate);
                if ($startDateAndTime <= $currentTimestamp && ($nextRunDateAndTime == null || $nextRunDateAndTime <= $currentTimestamp)) {
                    // save current timestamp as last run timestamp

                    $this->autoSyncLog('=========================================================');
                    $this->autoSyncLog('Running ' . $syncType . ' Auto-Sync, date checks passed');
                    $this->autoSyncLog('Now: ' . date(self::DATEFORMATE, $currentTimestamp));
                    $this->autoSyncLog('Start Date: ' . $startDateConfig);
                    $this->autoSyncLog('Next From Date: ' . $nextDateInSynConfig);
                    $this->autoSyncLog('Next Run Date: ' . $nextRunDateConfig);

                    $messagesArray = [];
                    if ($advanced) {       // if advanced messages exist the advanced option has been selected - ignore simple
                        $messages = explode(',', $advanced);
                    } else {
                        $simpleMessages = $this->commMessagingHelper->getSimpleMessageTypes('sync');
                        $messageLabels = explode(',', $simple);      // need to determine the codes from the name

                        foreach ($simpleMessages as $msg) {
                            if (in_array($msg['label'], $messageLabels)) {
                                $messagesArray[] = implode(",", $msg['value']);
                            }
                        }
                        $messages = explode(",", implode(',', $messagesArray));
                    }

                    $messageWeighting = $this->commMessagingHelper->getMessageTypeWeighting();
                    $sortedMessageWeighting = array_intersect($messageWeighting, $messages); // order selected messages according to weighting

                    $this->autoSyncLog('Messages: ' . implode(',', $sortedMessageWeighting));

                    $websites = array();
                    $stores = array();
                    if (!empty($syncStores)) {
                        foreach ($syncStores as $storeId) {
                            if (strpos($storeId, 'website_') !== false) {
                                $websites[] = str_replace('website_', '', $storeId);
                            } else {
                                $stores[] = str_replace('store_', '', $storeId);
                            }
                        }
                    }

                    $syn = $this->commMessageRequestSynFactory->create();
                    /* @var $syn \Epicor\Comm\Model\Message\Request\Syn */
                    if (!empty($websites)) {
                        $syn->setWebsites($websites);
                    }
                    if (!empty($stores)) {
                        $syn->setStores($stores);
                    }
                    $syn->addMessageType($sortedMessageWeighting);
                    $syn->addLanguage($language);
                    $fromDate = date(self::DATEFORMATE, $nextDateFromInSyn ? $nextDateFromInSyn : $startDateAndTime);
                    $fromDatetimestamp = strtotime($fromDate);
                    $fromDate = $this->commHelper->UTCwithOffset($fromDatetimestamp);
                    $syn->setFrom($fromDate);
                    $syn->setTrigger('Autosync');

                    $this->autoSyncLog('Sending SYN with formatted from date of ' . $fromDate);

                    if ($syn->sendMessage()) {
                        $this->autoSyncLog('SYN Successful, updating next run dates');
                        $this->setConfigDates($nextRunDateAndTime, $freqValue, $syncType);
                    }
                    $this->autoSyncLog('=========================================================');
                }
            }
        }
    }

    /**
     * Logs a message for the auto sync
     *
     * @param string $message
     */
    protected function autoSyncLog($message)
    {
        $this->logger->info($message);
    }

    public function setConfigDates($nextRunDateAndTime, $freqValue, $syncType)
    {
        $curentDate = $this->_localeDate->date()->format(self::DATEFORMATE);
        $currentTimestamp = strtotime($curentDate);
        $currentDateAndTime = strtotime(date(DATE_ATOM, $currentTimestamp));
        $nextRunDateAndTime = $nextRunDateAndTime ? $nextRunDateAndTime : $currentTimestamp;
        $newNextRunDateAndTime = $nextRunDateAndTime + $freqValue;
        if ($newNextRunDateAndTime <= $currentTimestamp) {
            $nextRunDateAndTime = $currentTimestamp + $freqValue;
        } else {
            $nextRunDateAndTime = $newNextRunDateAndTime;
        }

        $nextRunDateAndTime = date(self::DATEFORMATE, $nextRunDateAndTime);
        $nextDateFromInSynNew = date(self::DATEFORMATE, $currentDateAndTime);

        $this->autoSyncLog('Setting Next run time to : ' . $nextRunDateAndTime);
        $this->autoSyncLog('Setting Next From Date to : ' . $nextDateFromInSynNew);
        $prefixpath = 'epicor_comm_enabled_messages/syn_request/';
        $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_next_rundate', $nextRunDateAndTime, 'default', 0);
        $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_next_date_from_in_syn', $nextDateFromInSynNew, 'default', 0);
        $this->cache->clean(array('CONFIG'));
    }

    public function cleanupSynLog()
    {
        if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/logcleanup_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

            $value = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/logcleanup_limit_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $unit = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/logcleanup_limit_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if ($value && $unit) {
                $beforeDate = date('Y-m-d H:i:s', strtotime('-' . $value . ' ' . $unit));
                $collection = $this->commResourceSynLogCollectionFactory->create();
                /* @var $collection Epicor_Comm_Model_Resource_Syn_Log_Collection */

                $collection->addFieldToFilter('created_at', array('to' => $beforeDate));

                foreach ($collection->getItems() as $synLog) {
                    /* @var $synLog Epicor_Comm_Model_Syn_log */
                    $synLog = $this->commSynLogFactory->create()->load($synLog->getEntityId());
                    $synLog->delete();
                }
            }
        }
    }

    public function purgeData()
    {
        $collection = $this->commResourceEntityRegisterCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Entity_Register_Collection */
        $collection->addFieldToFilter('to_be_deleted', 1);

        $types = array();

        foreach ($collection->getItems() as $item) {
            /* @var $item Epicor_Comm_Model_Entity_Register */
            $types[$item->getType()][] = $item;
        }

        if (!empty($types)) {
            foreach ($types as $type => $items) {
                $this->eventManager->dispatch('epicor_comm_entity_purge_' . strtolower($type), array('items' => $items));
            }
        }

        foreach ($collection->getItems() as $item) {
            /* @var $item Epicor_Comm_Model_Entity_Register */
            $item->delete();
        }
    }

    public function submitReturnsToErp()
    {
        $collection = $this->commResourceCustomerReturnModelCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Return_Collection */
        $collection->addFieldToFilter('submitted', 1);
        $collection->addFieldToFilter('erp_sync_action', array('neq' => ''));
        $collection->addFieldToFilter('erp_sync_status', array('eq' => 'N'));

        $returns = $collection->getItems();

        if (!empty($returns)) {
            foreach ($returns as $return) {
                /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
                $return->sendToErp();
            }
        }
    }

    public function cleanOldReturns()
    {
        $enabled = $this->scopeConfig->isSetFlag('epicor_comm_returns/returns/unsubmitted_cleanup', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $days = $this->scopeConfig->getValue('epicor_comm_returns/returns/unsubmitted_days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($enabled && !empty($days)) {

            $datefilter = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));

            $collection = $this->commResourceCustomerReturnModelCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Customer_Return_Collection */
            $collection->addFieldToFilter('erp_returns_number', array('null' => true));
            $collection->addFieldToFilter('submitted', 0);

            $collection->addFieldToFilter('rma_date', array('to' => $datefilter));

            $returns = $collection->getItems();

            if (!empty($returns)) {
                foreach ($returns as $return) {
                    /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
                    $return->delete();
                }
            }
        }
    }

    /*
     * Syncs category images from Erp to Magento
     */

    public function scheduleCategoryImage()
    {
        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(\Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        //M1 > M2 Translation End
        if (!$this->registry->registry('isSecureArea')) {
            $this->registry->register('isSecureArea', true);
        }

        if ($this->scopeConfig->isSetFlag('Epicor_Comm/image_cron/category_schedule', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $maxCategories = $this->scopeConfig->getValue('Epicor_Comm/image_cron/categories_per_run', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $collection = $this->catalogResourceModelCategoryCollectionFactory->create();
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection->addAttributeToSelect('ecc_erp_images', 'left');
            $collection->addAttributeToSelect('ecc_erp_images_processed', 'left');
            $collection->addAttributeToSelect('ecc_erp_images_last_processed');
            $collection->addAttributeToFilter(array(
                array('attribute' => 'ecc_erp_images_processed', 'null' => 1),
                array('attribute' => 'ecc_erp_images_processed', 'eq' => 0)
            ));
            $collection->addAttributeToSort('ecc_erp_images_last_processed', 'ASC');
            $collection->setPage(1, $maxCategories);

            $categories = $collection->getItems();

            $helper = $this->commCatalogCategoryImageSyncHelper;
            /* @var $helper Epicor_Comm_Helper_Catalog_Category_image_sync */

            $mediaFolder = $helper->getMediaFolder();
            if (!$helper->validateOrCreateDirectory($mediaFolder)) {
                $helper->sendMagentoMessage(
                    "Directory $mediaFolder was not found and cannot be created due to permissions, must be created manually.", "Category Images Folder not created", \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
                );
            }

            $assetsFolder = $helper->getAssetsFolder();
            if ($helper->validateOrCreateDirectory($assetsFolder)) {
                foreach ($categories as $category) {
                    /* @var $category Mage_Catalog_Model_Category */
                    $helper->processErpImages($category);
                }
            } else {
                $helper->sendMagentoMessage(
                    "Directory $assetsFolder was not found and cannot be created due to permissions, must be created manually.", "Category Assets Folder not created", \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
                );
            }
        }

        $this->registry->unregister('isSecureArea');
    }

    /**
     * Check for any Caaps that have not been sent successfully
     */
    public function offlineArpaymentOrders()
    {

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $helper->setPhpTimeLimits();
        $helper->setPhpMemoryLimits();


        $msg = $helper->getHeartBeatMessage('Offline Deamon');

        if ($msg->isActive(null, true)) {
            $continue = $msg->sendMessage();
        } else {
            $continue = true;
        }

        if ($continue) {

            $statuses = array();
            foreach (explode(',', $this->scopeConfig->getValue('customerconnect_enabled_messages/CAAP_request/valid_order_statuses', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) as $status) {
                $statuses[] = array('eq' => $status);
            }
            $collection = $this->salesArOrderCollectionFactory->create();
            /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
            $collection->addFieldToFilter('ecc_caap_sent', 0);
            $collection->addFieldToFilter('status', $statuses);
            $collection->addFieldToFilter('created_at', array("lteq" => date(DATE_ATOM, strtotime('10 mins ago'))));

            $orders = $collection->getItems();

            $ordersSent = 0;
            $ordersFailed = 0;
            $ordersSuccessMsg = 'Successful: ';
            $ordersFailedMsg = 'Failed: ';
            foreach ($orders as $order) {
                if (!$this->registry->registry("offline_arpaymentorders_{$order->getId()}")) {
                    $this->registry->register("offline_arpaymentorders_{$order->getId()}", true);
                }
                $quotes = $this->quoteArFactory->create();
                $quote = $quotes->load($order->getQuoteId());
                //$quote = $this->quoteRepository->get($order->getQuoteId(), [$order->getStoreId()]);
                $this->eventManager->dispatch('ar_checkout_submit_all_after', array(
                    'quote' => $quote,
                    'order' => $order,
                ));

                $this->registry->unregister("offline_arpaymentorders_{$order->getId()}");
                $orderId = $order->getIncrementId();
                if ($order->getEccCaapSent() == "3") {
                    $ordersFailed++;
                    $ordersFailedMsg .= "</br>$orderId";
                    $log = $this->registry->registry('last_log');
                    if ($log) {
                        $logId = $log->getId();
                        $ordersFailedMsg .= "<a onClick='goToMessageUrl(\"$logId\");'> View Log</a>";
                    }
                    $ordersSent++;
                } else if ($order->getEccCaapSent() == "1") {
                    $ordersSuccessMsg .= "$orderId,";
                    $ordersSent++;
                }
            }
            if ($ordersSent > 0) {
                $this->commHelper->sendMagentoMessage(
                    "sent $ordersSent AR Payments Reference of which $ordersFailed failed <br/>$ordersSuccessMsg<br/>$ordersFailedMsg", "Offline AR payment Processing", \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE
                );
            }
        }
    }

    /**
     * Check for any GORs that have not been sent successfully
     */
    public function offlineDeleteArOrders()
    {

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $helper->setPhpTimeLimits();
        $helper->setPhpMemoryLimits();
        $continue = true;
        if ($continue) {
            $statuses = array();
            $collection = $this->salesResourceModelOrderCollectionFactory->create();
            /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
            $collection->addFieldToFilter('arpayments_quote', 1);
            $collection->setPageSize(10);
            $orders = $collection->getItems();
            foreach ($orders as $order) {
                if ($order->getArpaymentsQuote()) {
                    $this->arpaymentsHelper->deleteOrder($order);
                    $this->arpaymentsHelper->deleteRecord($order->getId());
                }
            }
        }
    }

}
