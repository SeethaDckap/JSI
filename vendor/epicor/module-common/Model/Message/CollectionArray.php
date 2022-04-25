<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model\Message;


/**
 * Collection class for message models, data is gathered from message / cache and added to the collection
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class CollectionArray extends \Epicor\Common\Model\Message\Collection
{

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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

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
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request
    )
    {
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
        $this->request = $request;
        parent::__construct(
            $entityFactory,
            $customerSession,
            $logger,
            $generic,
            $scopeConfig,
            $commMessagingHelper,
            $dataObjectFactory,
            $genericgridReader,
            $messageRequestModelReader,
            $directoryList,
            $cache,
            $localeResolver,
            $cacheState,
            $messageManager,
            $request
        );
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
    protected function _loadData()
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

            if (!$cache && !$session) {
                if (!isset($results[0])) {
                    $results2 = $results;
                    unset($results);
                    $results[0] = $results2;
                }
                foreach ($results as $x => $row) {
                    if (!is_null($row) && !empty($row)) {
                        if (!$this->_keepRowObjectType) {
                            $row = $this->dataObjectFactory->create(['data' => $this->_flattenData($row)]);
                        }

                        if (method_exists($this->_helper, 'process' . ucfirst($this->_messageType) . 'Row')) {
                            $row = call_user_func(array($this->_helper, 'process' . ucfirst($this->_messageType) . 'Row'), $row, $this->_dataSubset);
                        }

                        if (!$row->getId() && $this->_idColumn) {
                            $row->setId($row[$this->_idColumn]);
                        }

                        $results[$x] = $row;
                    } else {
                        unset($results[$x]);
                    }
                    //For CRQS/CUOS filtering for dealerconnect
                    if (($this->_messageType === "crqs" || $this->_messageType === "cuos") && $this->request->getModuleName() === "dealerconnect" && is_null($row['dealer'])) {
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
    protected function _flattenData($row, $keyBase = '', $join = '')
    {

        $processedRow = array();
        foreach ($row as $column => $data) {
            $column = preg_replace('/([A-Z])/', '_$1', $column);
            $column = strtolower($column);
            $columnInfo = isset($this->_columns[$column]) ? $this->_columns[$column] : array();
            if (is_array($data) && (empty($columnInfo) || !isset($columnInfo['keep_data_format']))) {
                $processedRow = array_merge($processedRow, $this->_flattenData($data, $keyBase . $join . $column, '_'));
                if (strpos($column, 'address')) {
                    $processedRow[$keyBase . $join . $column] = $this->_flattenAddress($data);
                    $processedRow[$keyBase . $join . $column . '_street'] = $this->_flattenStreet($data);
                } else if (empty($data)) {
                    $processedRow[$keyBase . $join . $column] = '';
                } else {
                    if (empty($data)) {
                        $processedRow[$keyBase . $join . $column] = '';
                    } else {
                        $processedRow[$keyBase . $join . $column] = $data;
                    }
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
    protected function _flattenAddress($address)
    {
        $address1 = $address['address1'] ? $address['address1'] : '';
        $address2 = $address['address2'] ? $address['address2'] : '';
        $address3 = $address['address3'] ? $address['address3'] : '';
        $city = $address['city'] ? $address['city'] : '';
        $county = $address['county'] ? $address['county'] : '';
        $country = $address['country'] ? $address['country'] : '';
        $postcode = $address['postcode'] ? $address['postcode'] : '';

        return $address1 . ', '
            . $address2 . ', '
            . $address3 . ', '
            . $city . ', '
            . $county . ', '
            . $country . ', '
            . $postcode;
    }

    /**
     * Flattens an address into a single string
     *
     * @param \Epicor\Common\Model\Xmlvarien $address
     *
     * @return string
     */
    protected function _flattenStreet($address)
    {
        $address1 = $address['address1'] ? $address['address1'] : '';
        $address2 = $address['address2'] ? $address['address2'] : '';
        $address3 = $address['address3'] ? $address['address3'] : '';
        return $address1 . ', '
            . $address2 . ', '
            . $address3;
    }


    /**
     * Using the filters provided, loads any data by sending the message request
     *
     * @return array
     */
    protected function _loadMessageData()
    {

        $message = $this->messageRequestModelReader->getModel($this->_messageBase, $this->_messageType);
        if (!$message) {
            throw new \Exception('Message type "' . $this->_messageType . '" could not be loaded');
        }

        $helper = $this->commMessagingHelper;

        $message->setMaxResults($this->_maxResults);

        foreach ($this->_dataFilters as $filter) {

            $columnInfo = isset($this->_columns[$filter['field']]) ? $this->_columns[$filter['field']] : array();
            if (
                (isset($columnInfo['filter_by']) && $columnInfo['filter_by'] == 'erp' || isset($filter['force']))
            ) {
                $field = $helper->convertStringToCamelCase($filter['field']);
                if (
                    isset($columnInfo['filter_by']) &&
                    ($columnInfo['type'] == 'date' || $columnInfo['type'] == 'datetime') &&
                    isset($columnInfo['format'])) {
                    $filter['value'] = date($columnInfo['format'], strtotime($filter['value']));
                }
                $message->addSearchOption($field, $filter['type'], $filter['value']);
            } else if ($columnInfo['filter_by'] == 'linq') {
                $this->_forceLinqSearch = true;
            }
        }

        $type = ($this->_messageBase == 'supplierconnect') ? 'supplier' : 'customer';
        $erpCustomer = $helper->getErpAccountInfo(null, $type);

        $message->setAccountNumber($erpCustomer->getErpCode());
        $message->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
        if ($message->isActive()) {
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
        } else {
            $results = array();
            throw new \Exception($this->_messageType . ' message is not active');
        }

        return $results;
    }


}
