<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Sf extends \Epicor\Comm\Controller\Test
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Setup\CategorySetupFactory
     */
    protected $catalogCategorySetupFactory;
      /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    
      /**
     * @var \Epicor\Comm\Model\Cron
     */
     protected $cron;
     
    protected $commMessagingHelper;
     /**
     * @var \Epicor\Comm\Helper\Messaging
     */

    protected $catalogProductResourceModel;
      /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $catalogProductResourceModelFactory;
      /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogProductResourceModelCollectionFactory;
      /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $storeManager;
      /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $catalogProduct;
      /**
     * @var Magento\Catalog\Model\Product
     */
    /*
     * @var \Magento\Customer\Helper\Session\CurrentCustomer    
     */
    protected $currentCustomer;
    
       
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\CacheInterface $cacheManager,    
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Cron  $cron,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,   
        \Magento\Catalog\Model\ResourceModel\Product $catalogProductResourceModel,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogProductResourceModelFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $catalogProduct,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catlogProductCollection,
        \Magento\Customer\Helper\Session\CurrentCustomer   $currentCustomer 
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->eventManager = $context->getEventManager();
        $this->customerSession = $customerSession; 
        $this->commMessagingHelper = $commMessagingHelper;
        $this->cron = $cron;
        $this->catalogProduct = $catalogProduct;       
        $this->catalogProductResourceModel = $catalogProductResourceModel;       
        $this->catalogProductResourceModelCollectionFactory = $catlogProductCollection;       
        $this->storeManager = $storeManager; 
        $this->currentCustomer = $currentCustomer;
        parent::__construct(
            $context,
            $resourceConfig,
            $moduleReader,
            $cacheManager    
        );
    }

    public function execute()
    {
        $products = $this->commMessagingHelper->getNextScheduledMsqProducts();
        foreach ($products as $product) {
          var_dump($product->getSku());
        }
      exit;  
        $this->cron->scheduleMsq();    
        var_dump('--SF comm/test/sf completed --');   
      
    }
}