<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Cart;

use Epicor\Punchout\Plugin\AbstractPlugin;

class PaymentMethods extends AbstractPlugin
{
    /**
     * My Account Link.
     *
     * @param Magento\Checkout\Block\Cart $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterGetMethods(\Magento\Checkout\Block\Cart $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = ['punchout.bottom'];
        }
        return $return;
    }

}
