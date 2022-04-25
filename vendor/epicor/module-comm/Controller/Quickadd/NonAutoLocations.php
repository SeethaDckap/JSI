<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Quickadd;

class NonAutoLocations extends \Epicor\Comm\Controller\Quickadd
{


    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
    \Magento\Wishlist\Helper\Data $wishlistHelper,
    \Epicor\Comm\Helper\Data $commHelper,
    \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
    \Magento\Framework\Registry $registry,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
    \Epicor\Comm\Helper\Locations $commLocationsHelper,
    \Magento\Framework\DataObjectFactory $dataObjectFactory)
{
    $this->commLocationsHelper = $commLocationsHelper;
    parent::__construct(
        $context,
        $commMessageRequestMsqFactory,
        $wishlistHelper,
        $commHelper,
        $catalogProductFactory,
        $registry,
        $customerSession,
        $wishlistWishlistFactory,
        $dataObjectFactory);
}


    public function execute()
    {
        //get all locations for customer/product
        $existingSku = $this->registry->registry('sku_in_autocomplete');
        $sku = $this->getRequest()->getParam('sku');
        $locations = array();
        $productId = '';
        $product = $this->catalogProductFactory->create()->loadByAttribute('sku', $sku);
        /* @var $product Epicor_Comm_Catalog_Model_Product */
        if ($product) {
            $productId = $product->getId();
            $locations = $this->commLocationsHelper->getLocationsArray($product);
            $message = 'success';
            if (empty($locations)) {
                $message = 'nolocations';
            } else {
                $message = 'success';
            }
        } else {
            $message = 'noproduct';
        }

        echo json_encode(array('message' => $message, 'locations' => $locations, 'productid' => $productId));
    }

}
