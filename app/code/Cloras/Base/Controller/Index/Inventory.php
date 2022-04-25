<?php
namespace Cloras\Base\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelperData;

class Inventory extends Action
{
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param HelperData $catalogSearchHelper
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        JsonHelperData $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Cloras\Base\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Category $categoryModel
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_jsonHelper        = $jsonHelper;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->dir = $dir;
        $this->customerSession = $customerSession;
        $this->categoryModel = $categoryModel;
        parent::__construct($context);
    }//end __construct()

    public function execute()
    {

        $this->logger->pushHandler(
            new \Monolog\Handler\StreamHandler(
                $this->dir->getRoot().'/var/log/cloras/dynamic-inventory.log'
            )
        );
        
        $this->logger->info('AJAX call Start');
        
        $categoryId = $this->getRequest()->getParam('categoryId');

        $productId = $this->getRequest()->getParam('productId');

        $limit = ($this->getRequest()->getParam('limit') ? $this->getRequest()->getParam('limit') : 24);

        $pageValue = ($this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1);

        $products = [];
        $sessionPrices = [];
        $items = [];
             
        $productsData = [];
    
        $productIds = [];
        if (!empty($this->getRequest()->getParam('productIds'))) {
            $productIds = explode(",", $this->getRequest()->getParam('productIds'));
        }

        $products = $this->helper->fetchAPIData($productIds, 'fetch_inventory');

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        
        $response->setHeader('Content-type', 'text/plain');
    
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                $products
            )
        );
        $this->logger->info('AJAX call End');
        
        return $response;
    }//end execute()
}//end class
