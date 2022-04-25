<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Helper\Wishlist;


/**
 * Wishlist Data Helper
 *
 * @category   Mage
 * @package    Mage_Wishlist
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Wishlist\Helper\Data
{

    /**
     * Check is allow wishlist module
     *
     * @return bool
     */
    public function isAllow()
    {
        $customer = $this->_customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer && $customer->isSalesRep()) {
            return false;
        } else {
            return parent::isAllow();
        }
    }

    /**
     * Check is allow wishlist action in shopping cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        $customer = $this->_customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer && $customer->isSalesRep()) {
            return false;
        } else {
            return parent::isAllowInCart();
        }
    }

}
