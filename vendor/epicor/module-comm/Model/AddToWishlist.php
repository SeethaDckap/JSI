<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

/*
 * AddToWishlist interface
 * @param id
 * @param qty
 * @return type none
 *
 */

use Epicor\Comm\Api\AddToWishlistInterface as AddToWishlistInterface;

class AddToWishlist implements  AddToWishlistInterface
{


    private $catalogProductFactory;
    /**
     * @var \Magento\Framework\App\Action\Action
     */
    private $controllerAction;
    /**
     * @var \Epicor\Comm\Controller\Quickadd\AddToWishlist
     */
    private $quickAddController;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;


    public function __construct(
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Controller\Quickadd\AddToWishlist $quickAddController,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->catalogProductFactory = $catalogProductFactory;
        $this->quickAddController = $quickAddController;
        $this->messageManager = $messageManager;
    }


    /**
     * @param int $id
     * @param int $qty
     * @return mixed|void
     */
    public function addToWishlist($id, $qty)
    {
        //products can't be added to wishlist if they are configurable
        $product = $this->catalogProductFactory->create()->load($id);
        $this->messageManager->addErrorMessage(
            __("We can't add product {$product->getSku()} to the Wish List from the quick add section. ".
               " The product needs to be configured first")
        );
    }
}

