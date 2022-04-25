<?php
/**
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Plugin\Account;

use Magento\Customer\Model\Session;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Epicor\Customerconnect\Plugin\Account\AbstractCuau
{
    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function beforeExecute(\Magento\Customer\Controller\Adminhtml\Index\Save $subject)
    {
        $postdata= $this->_request->getPostValue();
        if($this->getCurrentCustomerId()) {
            $this->preparedata($this->getCurrentCustomerId(),$postdata['customer']);
        }

    }

    /**
     * Retrieve current customer ID
     *
     * @return int
     */
    private function getCurrentCustomerId()
    {
        $originalRequestData = $this->_request->getPostValue(\Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $customerId = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $customerId;
    }
}
