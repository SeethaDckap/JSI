<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\App\Action\Context;

/**
 * Renders the Locations, for a given product.
 *
 * @package Epicor\Comm
 */
class Locations extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * Locations constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param RawFactory $rawFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        RawFactory $rawFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->rawFactory = $rawFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /**
         * @var Raw $resultRaw
         */
        $resultRaw = $this->rawFactory->create();
        $page = $this->pageFactory->create();
        $block = $page->getLayout()->getBlock('epicor_comm_product_locations')
            ->setProductId($this->getRequest()->getParam('productId'))
            ->setListMode('list')
            ->setReturnUrl($this->getRequest()->getParam('addToCartReturnUrl'));

        $resultRaw->setContents($block->toHtml());
        return $resultRaw;
    }
}
