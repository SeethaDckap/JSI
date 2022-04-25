<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Payment;

use Epicor\Punchout\Plugin\AbstractPlugin;

class InstantPurchaseConfig extends AbstractPlugin
{

    /**
     * Disable InstantPurchase.
     *
     * @param Magento\InstantPurchase\Model\Config $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterIsModuleEnabled(\Magento\InstantPurchase\Model\Config $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = false;
        }
        return $return;
    }

}
