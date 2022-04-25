<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Cart;

use Epicor\Punchout\Plugin\AbstractPlugin;

class Data extends AbstractPlugin
{
    /**
     * Get onepage checkout availability
     * @param \Magento\Checkout\Helper\Data $subject
     * @param bool $result
     * @return bool $result
     */
    public function afterCanOnepageCheckout(
        \Magento\Checkout\Helper\Data $subject,
        $result
    )
    {
        $subject;
        if ($result && $this->customerSession->getIsPunchout()) {
            return false;
        }
        return $result;
    }

}
