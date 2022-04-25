<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Payment;


use Epicor\Punchout\Plugin\AbstractPlugin;

class PaypalConfig extends AbstractPlugin
{

    /**
     * Disable Paypal.
     *
     * @param Magento\Paypal\Model\Config $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterIsMethodAvailable(\Magento\Paypal\Model\Config $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = false;
        }
        return $return;
    }

}
