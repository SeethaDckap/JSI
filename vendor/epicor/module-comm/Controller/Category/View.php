<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Category;

use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;

class View extends \Magento\Catalog\Controller\Category\View 
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
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Catalog\Model\Design $catalogDesign, 
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,       
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;        
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry = $registry;
        $this->commProductHelper = $commProductHelper;
        $this->_logger = $logger;
        parent::__construct(
            $context, 
            $catalogDesign, 
            $catalogSession, 
            $coreRegistry, 
            $storeManager, 
            $categoryUrlPathGenerator, 
            $resultPageFactory, 
            $resultForwardFactory, 
            $layerResolver, 
            $categoryRepository
        );        
    }
    
    public function execute()
    {
        if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('isStockUpdate')) {
            $this->setCurrentCategory();
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
                $block->setMsqCollection($collection);
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
            return parent::execute();
        }
    }

    /**
     * Set Current Category For CIM message to send category code.
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setCurrentCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        if ($categoryId) {
            $category = $this->categoryRepository->get(
                $categoryId,
                $this->_storeManager->getStore()->getId()
            );
            if ($category) {
                $this->_coreRegistry->register(
                    'current_category',
                    $category
                );
            }
        }
    }

}