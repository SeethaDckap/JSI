<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Observer;

use Epicor\Comm\Model\Customer;

class SetAllowedResource extends \Epicor\AccessRight\Model\ApplyRoles implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * sets current price mode for customer type dealer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * Customer.
         *
         * @var Customer $customer
         */
        $customer = $observer->getEvent()->getCustomer();

        // Skip observer if customer class is not the ECC customer class.
        if (($customer instanceof Customer) === false) {
            return $this;
        }

        // Multi Erp Account.
        $getErpAcctCounts = $customer->getErpAcctCounts();
        $applyAccess = true;
        if (is_array($getErpAcctCounts) && count($getErpAcctCounts) > 1 &&
            !$this->customerSession->getMasqueradeAccountId()
        ) {
            $applyAccess = false;
        }
        if ($applyAccess) {
            $this->frontendApplyRole($customer);
        }
        return $this;
    }


}
