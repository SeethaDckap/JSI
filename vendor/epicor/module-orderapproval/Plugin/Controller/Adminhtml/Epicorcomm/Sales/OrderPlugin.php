<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Plugin\Controller\Adminhtml\Epicorcomm\Sales;

use Epicor\OrderApproval\Model\Status\Options as StatusOptions;
use Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order as ComSalesOrder;

class OrderPlugin
{
    /**
     * @param ComSalesOrder $subject
     * @param $result
     * @return mixed
     */
    public function afterGetStatusMessages(ComSalesOrder $subject, $result)
    {
        if (!$subject->changedStatus || !$this->isValidResult($result)) {
            return $result;
        }

        if ($subject->changedStatus === StatusOptions::ECC_ORDER_APPROVAL_PENDING_GOR_STATE) {
            $result['gor_message'] = 'Manually set to : Order Pending Approval';
        }
        if ($subject->changedStatus === StatusOptions::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE) {
            $result['gor_message'] = 'Manually set to : Order Rejected';
        }

        return $result;
    }

    /**
     * @param $result
     * @return bool
     */
    private function isValidResult($result)
    {
        return is_array($result) && isset($result['gor_message']) && isset($result['state']);
    }
}