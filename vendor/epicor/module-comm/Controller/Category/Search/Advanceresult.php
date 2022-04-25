<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Category\Search;


class Advanceresult extends \Magento\CatalogSearch\Controller\Advanced\Result
{
    
    /**
     * Index resultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index jsonResultFactory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $jsonResultFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     *
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;
   
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $_logger;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    private $accessAuthorization;


    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\CatalogSearch\Model\Advanced $catalogSearchAdvanced
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Epicor\AccessRight\Helper\Data $authorization
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\CatalogSearch\Model\Advanced $catalogSearchAdvanced,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Psr\Log\LoggerInterface $logger,
        \Epicor\AccessRight\Helper\Data $authorization
    ) {
        $this->resultPageFactory        = $resultPageFactory;
        $this->jsonResultFactory        = $jsonResultFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry                 = $registry;
        $this->commProductHelper        = $commProductHelper;
        $this->_logger                  = $logger;
        $this->accessAuthorization      = $authorization->getAccessAuthorization();
        parent::__construct(
            $context,
            $catalogSearchAdvanced,
            $urlFactory
        );

    }//end __construct()


    public function execute() {
        if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('isStockUpdate')) {
            $resultJson = $this->jsonResultFactory->create();
            $responseData = ["success" => 0];
            $productIds = $this->getRequest()->getParam('productIds');            
            try {                                
                // get Product collection with MSQ            
                $collection = $this->commProductHelper->getProductCollectionByIds($productIds);
                $page = $this->resultPageFactory->create();
                $layout = $page->getLayout();
                $block = $layout->getBlock("price.loader.ajax")
                    ->setCollection($collection);
                foreach ($collection as $product) {

                    if ($this->registry->registry('current_product')) {
                        $this->registry->unregister('current_product');
                    }
                    $this->registry->register('current_product', $product);

                    $block->setProduct($product);

                    if (!$this->registry->registry('list_mode')) {
                        $block->setListMode($block->getMode());
                    }
                    $html = $block->toHtml();
                    //echo $html;exit;
                    $responseData["productList"][$product->getId()] = $html;
                }

                $responseData["success"] = 1;
                $resultJson->setData($responseData);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $responseData = ["success" => 0,"error"=>$e->getMessage()];                
                $resultJson->setData($responseData);               
            }
            
            return $resultJson;            
        } else {
            // Validate accessRights.
            if ($this->accessAuthorization->isAllowed('Epicor_Checkout::catalog_advance_search') === false) {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()->getUpdate()->addHandle('frontend_denied');
                $resultPage->getLayout()->unsetElement('content');
                $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
                return $resultPage;
            }

            return parent::execute();

        }//end if

    }//end execute()


}
