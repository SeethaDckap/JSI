<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Wishlist;

use Magento\Framework\App\Action; 
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Wishlist\Controller\Index\Index
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * Index jsonResultFactory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $jsonResultFactory;

    /**
     * @var \Epicor\Comm\Helper\LazyLoader
     */
    protected $loaderHelper;

    /**
     * Index resultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param PageFactory $resultPageFactory
     * @param \Epicor\Comm\Helper\LazyLoader $loaderHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Epicor\Comm\Helper\LazyLoader $loaderHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->loaderHelper = $loaderHelper;
        $this->_logger = $logger;
        parent::__construct($context, $wishlistProvider);
    }

    /**
     * Display customer wishlist
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('isStockUpdate')) {
            $resultJson = $this->jsonResultFactory->create();
            $responseData = ["success" => 0];
            $wishListIds = $this->getRequest()->getParam('productIds');
            try {
                $wishList = $this->wishlistProvider->getWishlist();
                /** @var  \Magento\Wishlist\Model\Wishlist $wishList */
                $data = $wishList->getItemCollection()->addFieldToFilter("wishlist_item_id",
                    array("in" => $wishListIds));

                $items = $data->getItems();

                $columns = [];
                $restrictAjaxBlock = $this->loaderHelper->getWishListRestrictBlock();
                $page = $this->resultPageFactory->create();
                $layout = $page->getLayout();
                foreach ($layout->getChildBlocks("customer.wishlist.items") as $key => $child) {
                    if (in_array($key, $restrictAjaxBlock)) {
                        if ($child instanceof \Magento\Wishlist\Block\Customer\Wishlist\Item\Column && $child->isEnabled()) {
                            $columns[] = $child;
                        }
                    }
                }

                // Load Block
                foreach ($items as $item) {
                    $html = "";
                    $blocks = $columns;
                    foreach ($blocks as $column) {
                        $html .= $column->setItem($item)->toHtml();
                    }
                    $responseData["productList"][$item->getId()] = $html;
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
            return parent::execute();
        }
    }
}
