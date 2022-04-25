<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Cart;

use Epicor\Punchout\Plugin\AbstractPlugin;

class Coupon extends AbstractPlugin
{

    /**
     * My Account Link.
     *
     * @param Magento\Checkout\Block\Cart\Coupon $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterToHtml(\Magento\Checkout\Block\Cart\Coupon $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = '';
        }
        return $return;
    }

}
