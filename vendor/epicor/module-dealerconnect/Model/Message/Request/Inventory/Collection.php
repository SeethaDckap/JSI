<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request\Inventory;


/**
 * Collection class for message models, data is gathered from message / cache and added to the collection
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Collection extends \Magento\Framework\Data\Collection
{

    private $_messageBase;
    private $_messageType;
    private $_columns;
    private $_accountNumber;
    private $_languageCode;
    private $_dataFilters = array();
    private $_filterString = '';
    private $_data = array();
    private $_helper = false;
    private $_keepRowObjectType = false;
    private $_dataSubset;
    private $_idColumn;
    private $_gridId;
    private $_cacheKeyBase;
    private $_cacheEnabled;
    private $_cacheTime;
    private $_showAll = false;
    private $_additionalFilters;
    private $_forceLinqSearch;
    private $_maxResults;
    private $_erpSearchEnabled;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Common\Helper\GenericgridReader
     */
    protected $genericgridReader;

    /**
     * @var \Epicor\Common\Model\MessageRequestModelReader
     */
    protected $messageRequestModelReader;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $cacheState;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Common\Helper\GenericgridReader $genericgridReader,
        \Epicor\Common\Model\MessageRequestModelReader $messageRequestModelReader,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->generic = $generic;
        $this->scopeConfig = $scopeConfig;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->genericgridReader = $genericgridReader;
        $this->messageRequestModelReader = $messageRequestModelReader;
        $this->cache = $cache;
        $this->directoryList = $directoryList;
        $this->_localeResolver = $localeResolver;
        $this->cacheState = $cacheState;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        parent::__construct(
            $entityFactory
        );
    }


    /**
     * Sets the message base, used by the message / caching etc
     *
     * @param string $messageBase
     */
    public function setMessageBase($messageBase)
    {
        $this->_messageBase = $messageBase;
    }

    /**
     * Sets the message type, used by the message
     *
     * @param string $messageType
     */
    public function setMessageType($messageType)
    {
        $this->_messageType = $messageType;
    }

    /**
     * Sets the message type, used by the message
     *
     * @param string $gridId
     */
    public function setGridId($gridId)
    {
        $this->_gridId = $gridId;
    }

    /**
     * Sets the data subset string, used when loading data from message
     *
     * @param string $dataSubset
     */
    public function setDataSubset($dataSubset)
    {
        $this->_dataSubset = $dataSubset;
    }

    /**
     * Sets the account number, used by the message
     *
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->_accountNumber = $accountNumber;
    }

    /**
     * Sets the lanuage code, used by the message
     *
     * @param string $languageCode
     */
    public function setLanguageCode($languageCode)
    {
        $this->_languageCode = $languageCode;
    }

    /**
     * Sets the id column for the data
     *
     * @param string $idColumn
     */
    public function setIdColumn($idColumn)
    {
        $this->_idColumn = $idColumn;
    }

    /**
     * Sets the data array
     *
     * @param array 4data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * Sets the columns array
     *
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->_columns = $columns;
    }

    /**
     * Sets the cache enabled flag
     *
     * @param boolean
     */
    public function setCacheEnabled($enabled)
    {
        $this->_cacheEnabled = $enabled;
    }

    /**
     * Returns any cache time for this collection
     *
     * @return integer
     */
    public function getCacheTime()
    {
        return $this->_cacheTime;
    }

    /**
     * Sets the show all flag
     *
     * @param boolean
     */
    public function setShowAll($showAll)
    {
        $this->_showAll = $showAll;
    }

    /**
     * Sets any additonal filters to add ot the ones provided by the grid
     *
     * @param array
     */
    public function setAdditionalFilters($filters)
    {
        $this->_additionalFilters = $filters;
    }

    /**
     * Sets the max number of results for the message
     *
     * @param integer
     */
    public function setMaxResults($max)
    {
        $this->_maxResults = $max;
    }

    /**
     * Sets whether to keep the row objects as is, or allow them to be flattened
     *
     * @param boolean $keep
     */
    public function setKeepRowObjectType($keep)
    {
        $this->_keepRowObjectType = $keep;
    }

    /**
     * Loads the collection from the relevant message type
     *
     * @param type $printQuery
     * @param type $logQuery
     *
     * Params needed due to inheritance, but not used
     *
     * @return \Epicor\Common\Model\Message\Collection
     * @throws \Exception
     */
    public function load($printQuery = false, $logQuery = false)
    {

        if ($this->isLoaded()) {
            return $this;
        }

        //M1 > M2 Translation Begin (Rule 46)
        //$this->_helper = Mage::helper($this->_messageBase . '/genericgrid');
        $this->_helper = $this->genericgridReader->getHelper($this->_messageBase);
        //M1 > M2 Translation End

        $this->_cacheEnabled = ($this->_cacheEnabled !== false) ? $this->cacheState->isEnabled($this->_messageBase) : false;

        $customerSession = $this->customerSession;
        $customerId = $customerSession->getCustomer()->getId();

        $identifier = $this->_messageType ?: $this->_gridId;

        $this->_cacheKeyBase = 'CUSTOMER_' . $customerId . '_' . strtoupper($this->_messageBase) . '_' . strtoupper($identifier);

        $this->_processFilters();
        $this->_loadData();
        $this->_setItems();
        $this->_setIsLoaded();
    }

    /**
     * Processes the filters into the correct format to send in a message
     *
     * @return array
     */
    private function _processFilters()
    {

        if (!empty($this->_filters)) {
            foreach ($this->_filters as $filter) {
                $filters = $this->_processFilter($filter);
                $this->_dataFilters = array_merge($this->_dataFilters, $filters);
            }
        }

        if (!empty($this->_additionalFilters)) {
            foreach ($this->_additionalFilters as $filter) {
                $filters = $this->_processFilter($filter, true);
                $this->_dataFilters = array_merge($this->_dataFilters, $filters);
            }
        }

        if (!empty($this->_dataFilters)) {
            foreach ($this->_dataFilters as &$filter) {
                //M1 > M2 Translation Begin (Rule 34)
                //$this->_filterString .= implode('|', $filter);
                if (is_object($filter['value'])) {
                    $filter['value'] = (array)$filter['value'];
                    $filter['value'] = $filter['value']['date'];
                }
                $this->_filterString .= implode('|', (array)$filter);
                //M1 > M2 Translation End
            }
        }
    }

    /**
     * Processes a filter into the correct format to send in a message
     *
     * May return multiple filters depending on the type provided
     *
     * @param \Magento\Framework\DataObject $filter
     *
     * @return array
     */
    private function _processFilter($filter, $force = false)
    {

        $filters = array();

        $field = $filter->getField();
        $value = $filter->getValue();

        $columnInfo = isset($this->_columns[$field]) ? $this->_columns[$field] : array();

        if (isset($value['like'])) {
            $like = $value['like'];
            /* @var $like Zend_Db_Expr */

            $condition = isset($columnInfo['condition']) ? $columnInfo['condition'] : 'EQ';

            $filters[] = array(
                'field' => $field,
                'type' => $condition,
                'value' => str_replace('%', '', trim($like->__toString(), '\'')),
            );
        } else if (isset($value['from']) || isset($value['to'])) {

            if (array_key_exists('condition', $columnInfo)) {
                $conditions = explode('/', $columnInfo['condition']);

                if (count($conditions) == 2) {
                    $condFrom = $conditions[1];
                    $condTo = $conditions[0];
                } else {
                    $condFrom = 'GTE';
                    $condTo = 'LTE';
                }
            } else {
                $condFrom = 'GTE';
                $condTo = 'LTE';
            }
            if (isset($value['from'])) {
                $from = $value['from'];
                $orig = isset($value['orig_from']) ? $value['orig_from'] : null;

                if ($from instanceof \Zend_Date) {
                    /* @var $from Zend_Date */
                    //M1 > M2 Translation Begin (Rule 32)
                    //$from = $this->_helper->getLocalDate($from->getTimestamp(), \Epicor\Comm\Model\Message::DATE_FORMAT);
                    //$orig = $this->_helper->getFormattedInputDate($orig, \Epicor\Comm\Model\Message::DATE_FORMAT);
                    $from = $this->_helper->getLocalDate($from->getTimestamp(), \IntlDateFormatter::LONG);
                    $orig = $this->_helper->getFormattedInputDate($orig, \IntlDateFormatter::LONG);
                    //M1 > M2 Translation End
                }

                $filters[] = array(
                    'field' => $field,
                    'type' => $condFrom,
                    'value' => $from,
                    'orig_data' => $orig
                );
            }

            if (isset($value['to'])) {
                $to = $value['to'];
                $orig = isset($value['orig_to']) ? $value['orig_to'] : null;

                if ($to instanceof \Zend_Date) {
                    /* @var $to Zend_Date */
                    //M1 > M2 Translation Begin (Rule 32)
                    //$to = $this->_helper->getLocalDate($to->getTimestamp(), \Epicor\Comm\Model\Message::DATE_FORMAT);
                    //$orig = $this->_helper->getFormattedInputDate($orig, \Epicor\Comm\Model\Message::DATE_FORMAT);
                    $to = $this->_helper->getLocalDate($to->getTimestamp(), \IntlDateFormatter::LONG);
                    $orig = $this->_helper->getFormattedInputDate($orig, \IntlDateFormatter::LONG);
                    //M1 > M2 Translation End
                }

                $filters[] = array(
                    'field' => $field,
                    'type' => $condTo,
                    'value' => $to,
                    'orig_data' => $orig
                );
            }
        } else if (isset($value['eq'])) {
            $eq = $value['eq'];
            /* @var $eq string */

            $filters[] = array(
                'field' => $field,
                'type' => 'EQ',
                'value' => $eq,
            );
        } else if (isset($value['neq'])) {
            $neq = $value['neq'];
            /* @var $neq string */

            $filters[] = array(
                'field' => $field,
                'type' => 'NEQ',
                'value' => $neq,
            );
        }

        if ($force) {
            foreach ($filters as $x => $filter) {
                $filters[$x]['force'] = 1;
            }
        }

        return $filters;
    }

    /**
     * Loads the data
     *
     * Priority order:
     *
     * 1. Data loaded from session cache
     * 2. Data provided manually
     * 3. Data loaded from cache
     * 4. Data loaded from message
     */
    private function _loadData()
    {

        $session = false;
        $cache = false;
        $provided = false;
        $results = array();
        try {
            $results = $this->_loadSessionData();
            if (empty($results)) {
                if (is_null($this->_data)) {
                    $results = $this->_loadCachedData();
                    if (is_null($results)) {
                        $results = $this->_loadMessageData();
                    } else {
                        $cache = true;
                    }
                } else {
                    $results = $this->_data;
                    $provided = true;
                }
            } else {
                $session = true;
            }
            if($this->_messageType == "debm" && ($this->_gridId == "dealerconnect_bom_built" || $this->_gridId == "dealerconnect_bom_add") && empty($results)){
                $this->_data = $results;
                return;
            }
            if (!$cache && !$session) {
                foreach ($results as $x => $row) {
                    if (!is_null($row) && !empty($row)) {
                        if (!$this->_keepRowObjectType) {
                            $row = $this->dataObjectFactory->create(['data' => $this->_flattenData($row->getData())]);
                        }

                        if (method_exists($this->_helper, 'process' . ucfirst($this->_messageType) . 'Row')) {
                            $row = call_user_func(array($this->_helper, 'process' . ucfirst($this->_messageType) . 'Row'), $row, $this->_dataSubset);
                        }

                        if (!$row->getId() && $this->_idColumn) {
                            $row->setId($row->getData($this->_idColumn));
                        }

                        $results[$x] = $row;
                    } else {
                        unset($results[$x]);
                    }
                }
                if ($provided || $this->_forceLinqSearch) {
                    $results = $this->_searchData($results);
                }
            }

            if (!$session) {
                if (!$cache && !$provided) {
                    $this->_storeData($results, 'cache');
                }
                $this->_storeData($results, 'session');
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->messageManager->addErrorMessage(__('Error occurred while retrieving data'));
        }
        $this->_data = $results;
    }

    /**
     * Flattens a data row into a single array (as data may be nested)
     *
     * @param \Epicor\Common\Model\Xmlvarien $row
     * @param string $keyBase
     * @param string $join
     *
     * @return array
     */
    private function _flattenData($row, $keyBase = '', $join = '')
    {

        $processedRow = array();
        foreach ($row as $column => $data) {

            $columnInfo = isset($this->_columns[$column]) ? $this->_columns[$column] : array();

            if ($data instanceof \Epicor\Common\Model\Xmlvarien && (empty($columnInfo) || !isset($columnInfo['keep_data_format']))) {
                $processedRow = array_merge($processedRow, $this->_flattenData($data->getData(), $keyBase . $join . $column, '_'));
                if (strpos($column, 'address')) {
                    $processedRow[$keyBase . $join . $column] = $this->_flattenAddress($data);
                    $processedRow[$keyBase . $join . $column . '_street'] = $this->_flattenStreet($data);
                } else {
                    $processedRow[$keyBase . $join . $column] = $data;
                }
            } else {
                $processedRow[$keyBase . $join . $column] = $data;
            }
        }

        return $processedRow;
    }

    /**
     * Flattens an address into a single string
     *
     * @param \Epicor\Common\Model\Xmlvarien $address
     *
     * @return string
     */
    private function _flattenAddress($address)
    {
        //M1 > M2 Translation Begin (Rule 9)
        /*return $address->getAddress1() . ', '
            . $address->getAddress2() . ', '
            . $address->getAddress3() . ', '*/
        return $address->getData('address1') . ', '
            . $address->getData('address2') . ', '
            . $address->getData('address3') . ', '
        //M1 > M2 Translation End
            . $address->getCity() . ', '
            . $address->getCounty() . ', '
            . $address->getCountry() . ', '
            . $address->getPostcode();
    }

    /**
     * Flattens an address into a single string
     *
     * @param \Epicor\Common\Model\Xmlvarien $address
     *
     * @return string
     */
    private function _flattenStreet($address)
    {
        //M1 > M2 Translation Begin (Rule 9)
        /*return $address->getAddress1() . ', '
            . $address->getAddress2() . ', '
            . $address->getAddress3();*/
        return $address->getData('address1') . ', '
            . $address->getData('address2') . ', '
            . $address->getData('address3');
        //M1 > M2 Translation End
    }

    /**
     * Using the filters provided, loads any cached data
     *
     * @return array
     */
    private function _loadSessionData()
    {

        $data = array();

        if ($this->_cacheEnabled) {
            //M1 > M2 Translation Begin (Rule p2-6.7)
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            //M1 > M2 Translation End
            if ($cache->load($this->_cacheKeyBase . '_filter') == $this->_filterString) {
                $this->_cacheTime = $cache->load($this->_cacheKeyBase . '_time');
                $data = unserialize($cache->load($this->_cacheKeyBase . '_data'));
            }
        }

        return $data;
    }

    /**
     * Using the filters provided, loads any cached data
     *
     * @return array
     */
    private function _loadCachedData()
    {

        $data = null;

        if ($this->_cacheEnabled) {
            $data = null;
        }

        return $data;
    }

    /**
     * Cache the data from the searchinto the cache / session
     *
     * @param array $data
     * @param string $type
     */
    private function _storeData($data, $type)
    {
        if ($this->_cacheEnabled) {
            //M1 > M2 Translation Begin (Rule p2-6.7)
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            //M1 > M2 Translation End
            /* @var $cache Mage_Core_Model_Cache */

            if ($type == 'session') {
                $customerSession = $this->customerSession;
                $customerId = $customerSession->getCustomer()->getId();

                $tags = array($this->_cacheKeyBase, 'CUSTOMER_' . $customerId . '_' . strtoupper($this->_messageBase) . '_SEARCH', $this->_messageBase);

                $lifeTime = $this->scopeConfig->getValue('Epicor_Comm/caching/customer_lifetime', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $cache->save($this->_filterString, $this->_cacheKeyBase . '_filter', $tags, $lifeTime);
                $cache->save(time(), $this->_cacheKeyBase . '_time', $tags, $lifeTime);
                $cache->save(serialize($data), $this->_cacheKeyBase . '_data', $tags, $lifeTime);
            } else if ($type == 'cache') {

            }
        }
    }

    /**
     * Using the filters provided, loads any data by sending the message request
     *
     * @return array
     */
    private function _loadMessageData()
    {

        //M1 > M2 Translation Begin (Rule 46)
        //$message = Mage::getModel($this->_messageBase . '/message_request_' . $this->_messageType);
        $message = $this->messageRequestModelReader->getModel($this->_messageBase, $this->_messageType);
        /* @var $message \Epicor\Comm\Model\Message\Request */
        //M1 > M2 Translation End
        if (!$message) {
            throw new \Exception('Message type "' . $this->_messageType . '" could not be loaded');
        }

        $helper = $this->commMessagingHelper;

        $message->setMaxResults($this->_maxResults);

        foreach ($this->_dataFilters as $filter) {

            $columnInfo = isset($this->_columns[$filter['field']]) ? $this->_columns[$filter['field']] : array();
            if ($columnInfo['filter_by'] == 'erp' || isset($filter['force'])) {
                $field = $helper->convertStringToCamelCase($filter['field']);
                if(($columnInfo['type']=='date' || $columnInfo['type']=='datetime') && isset($columnInfo['format'])){
                    $filter['value'] = date($columnInfo['format'], strtotime($filter['value']));
                }
                $message->addSearchOption($field, $filter['type'], $filter['value'],$columnInfo);
            } else if ($columnInfo['filter_by'] == 'linq') {
                $this->_forceLinqSearch = true;
            }
        }

        $type = ($this->_messageBase == 'supplierconnect') ? 'supplier' : 'customer';
        $erpCustomer = $helper->getErpAccountInfo(null, $type);
        /* @var $erpCustomer \Epicor\Comm\Model\Customer\Erpaccount */

        $message->setAccountNumber($erpCustomer->getErpCode());
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$message->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));
        $message->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
        //M1 > M2 Translation End
//        foreach (array_keys($erpCustomer->getAllCurrencyData()) as $currency_code) {
//            $message->addCurrencyOption('currencyCode', $currency_code);
//        }
        if ($message->isActive()) {
            //Added to send only one DEBM request.
            if ($this->_messageType == "debm" && $this->_gridId == "dealerconnect_bom_add") {
                $results = $this->registry->registry('debm_trans_details');
                if (!is_array($results)) {
                            $results = array($results);
                        }
            } elseif ($this->_messageType == "debm" && $this->_gridId == "dealerconnect_bom_built") {
                $results = $this->registry->registry('debm_details');
                if (!is_array($results)) {
                            $results = array($results);
                        }
            } else {
                if ($message->sendMessage()) {
                    if ($this->_dataSubset) {
                            $results = $message->getResults()->getVarienDataFromPath($this->_dataSubset);

                        if (!is_array($results)) {
                            $results = array($results);
                        }
                    } else {
                            $results = $message->getResults();
                    }
                } else {
                    throw new \Exception($this->_messageType . ' message failed while sending');
                }
            }
        } else {
            $results = array();
            throw new \Exception($this->_messageType . ' message is not active');
        }

        return $results;
    }

    /**
     * Searches the array based on filters
     */
    private function _searchData($results)
    {
        $inc = get_include_path();
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //set_include_path($inc . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Linq' . DS);
        set_include_path($inc . PATH_SEPARATOR . BP . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Linq' . DIRECTORY_SEPARATOR);
        //M1 > M2 Translation End
        require_once('PHPLinq' . DIRECTORY_SEPARATOR . 'LinqToObjects.php');

        try {

            $result = from('$rowData')->in($results);

            $where = '';
            $join = '';

            foreach ($this->_dataFilters as $filter) {

                $columnInfo = isset($this->_columns[$filter['field']]) ? $this->_columns[$filter['field']] : array();

                if (empty($columnInfo) || (isset($columnInfo) && isset($columnInfo['filter_by']) && $columnInfo['filter_by'] != 'linq')) {
                    continue;
                }

                $type = 'text';

                if (isset($columnInfo['type'])) {
                    $type = $columnInfo['type'];
                }

                switch ($filter['type']) {
                    case 'EQ':
                        $where .= $join . '($rowData["' . $filter['field'] . '"] == "' . $filter['value'] . '")';
                        break;
                    case 'LIKE':
                        $where .= $join . 'stripos($rowData["' . $filter['field'] . '"],"' . $filter['value'] . '") !== false';
                        break;
                    case 'NEQ':
                        $where .= $join . '$rowData["' . $filter['field'] . '"] != "' . $filter['value'] . '"';
                        break;
                    case 'LT':
                        if (strpos($type, 'date') !== false) {
                            $value = date('Y-m-d 23:59:59', strtotime($filter['orig_data']));
                            $where .= $join . 'strtotime($rowData["' . $filter['field'] . '"]) < strtotime("' . $value . '")';
                        } else {
                            $where .= $join . '$rowData["' . $filter['field'] . '"] < "' . $filter['value'] . '"';
                        }
                        break;
                    case 'LTE':
                        if (strpos($type, 'date') !== false) {
                            $value = date('Y-m-d 23:59:59', strtotime($filter['orig_data']));
                            $where .= $join . 'strtotime($rowData["' . $filter['field'] . '"]) <= strtotime("' . $value . '")';
                        } else {
                            $where .= $join . '$rowData["' . $filter['field'] . '"] <= "' . $filter['value'] . '"';
                        }
                        break;
                    case 'GT':
                        if (strpos($type, 'date') !== false) {
                            $value = date('Y-m-d 00:00:00', strtotime($filter['orig_data']));
                            $where .= $join . 'strtotime($rowData["' . $filter['field'] . '"]) > strtotime("' . $value . '")';
                        } else {
                            $where .= $join . '$rowData["' . $filter['field'] . '"] > "' . $filter['value'] . '"';
                        }
                        break;
                    case 'GTE':
                        $value = date('Y-m-d 00:00:00', strtotime($filter['orig_data']));
                        if (strpos($type, 'date') !== false) {
                            $where .= $join . 'strtotime($rowData["' . $filter['field'] . '"]) >= strtotime("' . $value . '")';
                        } else {
                            $where .= $join . '$rowData["' . $filter['field'] . '"] >= "' . $filter['value'] . '"';
                        }
                        break;
                }
                $join = '&&';
            }

            if (!empty($where)) {
                $results = $result->where($where)->select('$rowData');
            }
        } catch (\Exception $e) {

        }
        return $results;
    }

    /**
     * Sorts an array of data by the sorters stored then sets it to _items
     */
    private function _setItems()
    {
        $inc = get_include_path();
        if (strpos($inc, 'Linq') === false) {
            //M1 > M2 Translation Begin (Rule p2-5.5)
            //set_include_path($inc . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Linq' . DS);
            set_include_path($inc . PATH_SEPARATOR . BP . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Linq' . DIRECTORY_SEPARATOR);
            //M1 > M2 Translation End
        }
        require_once('PHPLinq' . DIRECTORY_SEPARATOR . 'LinqToObjects.php');

        try {

            $result = from('$rowData')->in($this->_data);

            $then = false;

            foreach ($this->_orders as $key => $order) {

                $columnInfo = isset($this->_columns[$key]) ? $this->_columns[$key] : array();

                $comparer = null;

                if (isset($columnInfo['type'])) {

                    $type = (isset($columnInfo['sort_type'])) ? $columnInfo['sort_type'] : $columnInfo['type'];

                    switch ($type) {
                        case 'date':
                        case 'datetime':
                            $comparer = '\Epicor\Common\Helper\Genericgrid::datecmp';
                            break;
                        case 'number':
                            $comparer = '\Epicor\Common\Helper\Genericgrid::intcmp';
                            break;
                    }
                }

                $function = ($then) ? (($order == 'DESC') ? 'thenByDescending' : 'thenBy') : (($order == 'DESC') ? 'orderByDescending' : 'orderBy');

                $result->$function('$rowData["' . $key . '"]', $comparer);
            }

            if ($this->_showAll) {
                $size = count($this->_data);
                $page = 1;
            } else {

                $this->_maxResults = count($this->_data);
                $size = $this->_pageSize;

                if (!empty($this->_maxResults) && $this->_maxResults < $size) {
                    $size = $this->_maxResults;
                }

                $page = ($this->_curPage * $size > $this->_maxResults) ? ceil($this->_maxResults / $size) : $this->_curPage;
            }

            $this->_totalRecords = count($this->_data);

            $start = ($page - 1) * $size;

            $this->_items = $result->skip($start)->take($size)->select('$rowData');
        } catch (\Exception $e) {
            $this->_items = $this->_data;
        }
    }

    /**
     * Adds a new filter
     *
     * (added so that the grid using this collection doesnt error)
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $this->addFilter($field, $condition);
    }

    /**
     * change messagebase to namespace format. Capitalized the first letter of the words, if the first word is not 'Epicor', add it.
     * @param $messagebase
     * @return array|string
     */
    protected function toNamespace($messagebase)
    {
        $strs = [];
        foreach (explode('_', $messagebase) as $item) {
            $strs[] = ucfirst($item);
        }
        if ($strs[0] != 'Epicor') {
            array_unshift($strs, 'Epicor');
        }
        $strs = implode("\\", $strs);

        return $strs;
    }
}
