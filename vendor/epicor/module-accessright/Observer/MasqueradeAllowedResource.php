<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Observer;

class MasqueradeAllowedResource extends \Epicor\AccessRight\Model\ApplyRoles implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * sets current price mode for customer type dealer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->frontendApplyRole();
        return $this;
    }


}
