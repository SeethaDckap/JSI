<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Model\ResourceModel\OrderHistory\CollectionFactory as HistoryCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\OrderHistory\Collection as HistoryCollection;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 *
 */
class OrderHistoryManagement
{
    /**
     * @var HistoryCollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * OrderHistoryManagement constructor.
     *
     * @param HistoryCollectionFactory $historyCollectionFactory
     */
    public function __construct(
        HistoryCollectionFactory $historyCollectionFactory
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
    }


    /**
     * @param $orderId
     *
     * @return false
     */
    public function getPendingGroupIdByOrderId($orderId)
    {
        /** @var HistoryCollection $historyCollection */
        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->addFieldToFilter("order_id",
            array("eq" => $orderId));
        $historyCollection->addFieldToFilter("status",
            array("eq" => GroupManagement::STATUS_PENDING));

        $items = $historyCollection->getItems();
        foreach ($items as $item) {
            return $item->getGroupId();
        }

        return false;
    }
}
