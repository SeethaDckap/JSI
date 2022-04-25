<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin;

use Epicor\OrderApproval\Model\Status\Options as OrderStatusOptions;

class Order
{
    /**
     * @var OrderStatusOptions
     */
    private $orderStatusOptions;

    /**
     * Order constructor.
     *
     * @param OrderStatusOptions $orderStatusOptions
     */
    public function __construct(
        OrderStatusOptions $orderStatusOptions
    ) {
        $this->orderStatusOptions = $orderStatusOptions;
    }

    public function afterGetStatusLabel(
        \Magento\Sales\Model\Order $subject,
        $result
    ) {
        $eccOrderStatus = $subject->getEccGorSent();
        $statusOptionsValue = $this->orderStatusOptions->stateDescriptions();
        switch ($eccOrderStatus) {
            case \Epicor\OrderApproval\Model\Status\Options::ECC_ORDER_APPROVAL_PENDING_GOR_STATE:
                $result
                    = $statusOptionsValue[\Epicor\OrderApproval\Model\Status\Options::ECC_ORDER_APPROVAL_PENDING_GOR_STATE];
                break;
            case \Epicor\OrderApproval\Model\Status\Options::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE:
                $result
                    = $statusOptionsValue[\Epicor\OrderApproval\Model\Status\Options::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE];
                break;
        }

        return $result;
    }
}
