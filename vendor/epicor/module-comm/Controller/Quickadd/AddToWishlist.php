<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Quickadd;

class AddToWishlist extends \Epicor\Comm\Controller\Quickadd
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory)
    {
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $commMessageRequestMsqFactory,
            $wishlistHelper,
            // $generic,
            $commHelper,
            $catalogProductFactory,
            $registry,
            $customerSession,
            $wishlistWishlistFactory,
            $dataObjectFactory);
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('productId');
        $qty = $this->getRequest()->getParam('qty');
        $product = $this->catalogProductFactory->create()->load($productId);
        $this->_addToWishlist($product, $qty);
        $message = $product->getName() . ' has been added to your wishlist';
        $this->getResponse()->setBody(json_encode(array('status' =>'success')));
    }

}


