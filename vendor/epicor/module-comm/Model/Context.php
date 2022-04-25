<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;


class Context extends \Magento\Framework\Model\Context
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Common\Helper\XmlFactory
     */
    protected $commonXmlHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Queue\CollectionFactory
     */
    protected $commResourceMessageQueueCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Message\QueueFactory
     */
    protected $commMessageQueueFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Session\GenericFactory
     */
    protected $genericFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $configConfig;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $indexerFactory;
    /**
     * @var \Epicor\Comm\Helper\ProductFacctory
     */
    protected $commProductHelper;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;
    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    
    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockItemRepository;
     /**
       * @var \Epicor\Comm\Model\Customer\Erpaccount
       */
    protected $epicorCommModelCustomerErpaccount;
    
    /**
    * @var  Magento\Sales\Model\Order\Email\Sender\ShipmentSender
    */
    protected $shipmentEmailSender;

    /**
     * \Epicor\Comm\Model\IndexerFactory
     */
    protected  $commIndexerFactory;

    /**
     * @var \Epicor\Common\Model\DataMapping
     */
    protected $dataMapping;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Helper\XmlFactory $commonXmlHelper,
        \Epicor\Comm\Helper\MessagingFactory $commMessagingHelper,
        \Epicor\Comm\Helper\LocationsFactory $commLocationsHelper,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Model\ResourceModel\Message\Queue\CollectionFactory $commResourceMessageQueueCollectionFactory,
        \Epicor\Comm\Model\Message\QueueFactory $commMessageQueueFactory,
        \Magento\Framework\Session\GenericFactory $genericFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Config\Model\Config $configConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerFactory,
        \Epicor\Comm\Helper\ProductFactory $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Common\Helper\File  $commonFileHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,  
        \Epicor\Comm\Model\Customer\Erpaccount $epicorCommModelCustomerErpaccount, 
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Epicor\Comm\Model\IndexerFactory $commIndexerFactory,
        \Epicor\Common\Model\DataMappingFactory $dataMapping,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory
    )
    {

        parent::__construct($logger, $eventDispatcher, $cacheManager, $appState, $actionValidator);
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->eventManager = $eventManager;
        $this->commMessageLogFactory = $commMessageLogFactory;
        $this->moduleReader  = $moduleReader;
        $this->commResourceMessageQueueCollectionFactory = $commResourceMessageQueueCollectionFactory;
        $this->commMessageQueueFactory = $commMessageQueueFactory;
        $this->genericFactory = $genericFactory;
        $this->customerSession = $customerSession;
        $this->configConfig = $configConfig;
        $this->indexerFactory=$indexerFactory;
        $this->commProductHelper=$commProductHelper;
        $this->catalogProductFactory=$catalogProductFactory;
        $this->encryptor = $encryptor;
        $this->commonFileHelper = $commonFileHelper;
        $this->stockItemRepository = $stockItemRepository;
        $this->epicorCommModelCustomerErpaccount = $epicorCommModelCustomerErpaccount;
        $this->shipmentEmailSender = $shipmentSender;
        $this->commIndexerFactory = $commIndexerFactory;
        $this->dataMapping = $dataMapping;
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Epicor\Common\Helper\XmlFactory
     */
    public function getCommonXmlHelper()
    {
        return $this->commonXmlHelper;
    }

    /**
     * @return \Epicor\Comm\Helper\Messaging
     */
    public function getCommMessagingHelper()
    {
        return $this->commMessagingHelper;
    }

    /**
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getCommLocationsHelper()
    {
        return $this->commLocationsHelper;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return Message\LogFactory
     */
    public function getCommMessageLogFactory()
    {
        return $this->commMessageLogFactory;
    }

    /**
     * @return \Magento\Framework\Module\Dir\Reader
     */
    public function getModuleReader()
    {
        return $this->moduleReader;
    }

    /**
     * @return ResourceModel\Message\Queue\CollectionFactory
     */
    public function getCommResourceMessageQueueCollectionFactory()
    {
        return $this->commResourceMessageQueueCollectionFactory;
    }

    /**
     * @return Message\QueueFactory
     */
    public function getCommMessageQueueFactory()
    {
        return $this->commMessageQueueFactory;
    }

    /**
     * @return \Epicor\Comm\Helper\Data
     */
    public function getCommHelper()
    {
        return $this->commHelper;
    }

    /**
     * @return \Magento\Framework\Session\GenericFactory
     */
    public function getGenericFactory()
    {
        return $this->genericFactory;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Magento\Config\Model\Config
     */
    public function getConfigConfig()
    {
        return $this->configConfig;
    }
    /**
     * @return \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    public function getIndexerFactory()
    {
        return $this->indexerFactory;
    }

    /**
     * @return \Epicor\Comm\Helper\ProductFactory
     */
    public function getCommProductHelper()
    {
        return $this->commProductHelper;
    }
    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getCatalogProductFactory()
    {
        return $this->catalogProductFactory;
    }
    /**
     * @return \Epicor\Common\Helper\File
     */
    public function getCommonFileHelper()
    {
        return $this->commonFileHelper;
    }

    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }
    
    /**
     * @return \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public function getCustomerRepository()
    {
        return $this->customerRepository;
    }
    
     /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock
     */
    public function getStockItemRepository()
    {
        return $this->stockItemRepository;
    }

    /**
    * @return  \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getEpicorCommModelCustomerErpaccount()
    {
        return $this->epicorCommModelCustomerErpaccount;
    }
    /**
    * @return  Magento\Sales\Model\Order\Email\Sender\ShipmentSender
    */
    public function getShipmentEmailSender()
    {
        return $this->shipmentEmailSender;
    }

    /**
     *  @return \Epicor\Comm\Model\IndexerFactory
     */
    public function getCommIndexerFactory()
    {
        return $this->commIndexerFactory;
    }

    /**
     * @return \Epicor\Common\Model\DataMapping|\Epicor\Common\Model\DataMappingFactory
     */
    public function getDataMappingFactory()
    {
        return $this->dataMapping;
    }

    /**
     * @return \Epicor\Common\Model\XmlvarienFactory
     */
    public function getCommonXmlvarienFactory()
    {
        return $this->commonXmlvarienFactory;
    }
}
