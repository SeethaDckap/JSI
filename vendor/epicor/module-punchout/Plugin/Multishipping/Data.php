<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Multishipping;

use Epicor\Punchout\Plugin\AbstractPlugin;

class Data extends AbstractPlugin
{
    /**
     * Get Punchout availability
     * @param \Magento\Multishipping\Helper\Data $subject
     * @param bool $result
     * @return bool $result
     */
    public function afterIsMultishippingCheckoutAvailable(
        \Magento\Multishipping\Helper\Data $subject,
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
