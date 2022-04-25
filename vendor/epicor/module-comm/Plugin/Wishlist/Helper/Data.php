<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Wishlist\Helper;

class Data 
{

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization
    ){

        $this->_accessauthorization = $authorization->getAccessAuthorization();

    }
    /**
     * Modifying the add to cart url in Wishlist
     *
     * @param \Magento\Wishlist\Helper\Data $subject
     * @param string $url
     * @return string
     */
    public function afterGetAddToCartUrl(\Magento\Wishlist\Helper\Data $subject, $url)
    {
        $url = str_replace('/wishlist/index/cart/', '/checkout/cart/add/', $url);
        return $url;
    }
    /**
     * Modifying the add to cart url in Wishlist
     *
     * @param \Magento\Wishlist\Helper\Data $subject
     * @param string $url
     * @return string
     */
    public function afterIsAllow(\Magento\Wishlist\Helper\Data $subject, $result)
    {
         if(!$this->_accessauthorization->isAllowed('Epicor_Customer::my_account_wishlist')) {
                return false;
        }
        return $result;
    }
}
