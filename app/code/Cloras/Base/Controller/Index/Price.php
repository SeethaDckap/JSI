<?php
namespace Cloras\Base\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Cloras\Base\Helper\Data as BaseHelperData;
use Magento\Framework\App\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

class Price extends Action
{

    /**
     * @var BaseHelperData
     */
    private $resultJsonFactory;

    /**
     * @var HelperData
     */
    private $baseHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param JsonHelperData $jsonHelper
     * @param LoggerInterface $logger
     * @param DirectoryList $dir
     * @param BaseHelperData $baseHelper
     */
    public function __construct(
        Context $context,
        JsonHelperData $jsonHelper,
        LoggerInterface $logger,
        DirectoryList $dir,
        BaseHelperData $baseHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->baseHelper = $baseHelper;
        $this->dir = $dir;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->logger->info('ajax call started');
        
        $products = $this->getProductPrice();

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $response->setHeader('Content-type', 'text/plain');

        $response->setContents(
            $this->jsonHelper->jsonEncode(
                $products
            )
        );
        
        $this->logger->info('ajax call end');

        return $response;
    }//end execute()

    private function getProductPrice()
    {

        $products = [];

        $this->logger->info('Fetch Dynamic Pricing');

        $categoryId = $this->getRequest()->getParam('categoryId');

        $productId = $this->getRequest()->getParam('productId');

        $limit = ($this->getRequest()->getParam('limit') ? $this->getRequest()->getParam('limit') : 24);

        $pageValue = ($this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1);

        $productIds = [];
        if (!empty($this->getRequest()->getParam('productIds'))) {
            $productIds = explode(",", $this->getRequest()->getParam('productIds'));
        }
       
        
        /*fetch price based on product ids*/
        $products = $this->baseHelper->fetchAPIData($productIds, 'fetch_price');
        $this->logger->info('Product Price', [$products]);
        
        return $products;
    }
}//end class
