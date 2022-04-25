<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Product;

/**
 * Controller for use with product AJAX view page
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class View extends \Magento\Catalog\Controller\Product\View
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $viewHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

     /**
      * Index jsonResultFactory
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    public $jsonResultFactory;
    
    /**
     *
     * @var \Epicor\Comm\Helper\Product 
     */
    protected $_commProductHelper;
    
   /**
    * 
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Catalog\Helper\Product\View $viewHelper
    * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
    * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    * @param \Epicor\Comm\Helper\Product $commProductHelper
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Epicor\Comm\Helper\Product $commProductHelper
    ) {
        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_commProductHelper = $commProductHelper;
        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
    }

    /**
     * Product view AJAX action for lazy load
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {        
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');
        $isStockUpdate = $this->getRequest()->getParam('isStockUpdate');

        if($isStockUpdate && $this->getRequest()->isAjax()) {
            $resultJson = $this->jsonResultFactory->create();
            $responseData = ["success" => 0];
            if (!$this->getRequest()->getParam('isStockUpdate')) {
                return parent::execute();
            }

            // Prepare helper and params
            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId($categoryId);
            $params->setSpecifyOptions($specifyOptions);

            try {
                $html = "";
                $page = $this->resultPageFactory->create();
                $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
                $layout = $page->getLayout();
                $elementtLists = $this->_commProductHelper->getElemetRemovelist();
                $currentProduct = $this->_commProductHelper->getRegistry()->registry("current_product");
                /* @var $currentProduct \Magento\Catalog\Model\Product  */                                
                
                foreach ($elementtLists as $view) {
                    if($currentProduct && $currentProduct->getTypeId() == 'bundle' && ($view == "product.info" || $view == "bundle.options.container")) {
                        continue; // skip render element for bundle product
                    }
                    $html .= $layout->hasElement($view) ? $layout->renderElement($view) : '';
                }
                if($currentProduct && $currentProduct->getTypeId() == 'bundle') {
                    $responseData['bundleOptionContainer'] = $layout->hasElement("bundle.options.container") ? $layout->renderElement("bundle.options.container") : ''; 
                }
                
                $responseData['page'] = $html;
                $responseData["success"] = 1;
                $resultJson->setData($responseData);

            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $responseData = ["success" => 0, "error" => $e->getMessage()];
                $resultJson->setData($responseData);
            };
            
            return $resultJson;
        } else {
            return parent::execute();
        }
    }
}
