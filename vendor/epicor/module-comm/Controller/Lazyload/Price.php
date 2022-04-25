<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Lazyload;

use Magento\Framework\App\Action\Context;
use Epicor\Comm\Helper\Product;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class Price extends \Epicor\Comm\Controller\Lazyload
{

    /**
     * @var Product
     */
    protected $commProductHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Price constructor.
     * @param Context $context
     * @param Product $commProductHelper
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $jsonResultFactory
     * @param Registry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Product $commProductHelper,
        PageFactory $resultPageFactory,
        JsonFactory $jsonResultFactory,
        Registry $registry,
        LoggerInterface $logger
    ) {

        $this->commProductHelper = $commProductHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->registry = $registry;
        $this->_logger = $logger;
        parent::__construct(
            $context
        );
    }


    /**
     * Lazy load for price and stock availability
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('isStockUpdate')) {
            $resultJson = $this->jsonResultFactory->create();
            $responseData = ["success" => 0];
            $productIds = $this->getRequest()->getParam('productIds');
            $type = $this->getRequest()->getParam('type');

            try {
                // get Product collection with MSQ
                $collection = $this->commProductHelper->getProductCollectionByIds($productIds, $type);

                foreach ($collection as $product) {
                    $page = $this->resultPageFactory->create();
                    $layout = $page->getLayout();
                    if($product) { // Require to set product in to registry for version < Magento 2.3.0
                        $this->registry->register("product", $product);
                    }
                    $block = $layout->getBlock($type . ".price.loader.ajax")
                        ->setType($type)
                        ->setProduct($product);

                    $html = $block->toHtml();
                    $this->registry->unregister("product"); // Remove registry after render product
                    $responseData["productList"][$product->getId()] = $html;
                }

                $responseData["success"] = 1;
                $resultJson->setData($responseData);

            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $responseData = ["success" => 0, "error" => $e->getMessage()];
                $resultJson->setData($responseData);
            }

            return $resultJson;
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
