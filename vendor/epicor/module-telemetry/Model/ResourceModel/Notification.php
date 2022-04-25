<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\ResourceModel;

use Epicor\Telemetry\Api\Data\NotificationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Notification extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ecc_telemetry_notification_viewer_log', 'id');
    }

    /**
     * Insert on duplicate notification viewed logs.
     *
     * @param NotificationInterface $notification
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insert(NotificationInterface $notification)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connection->insertOnDuplicate(
            $this->getMainTable(),
            [
                'viewer_id' => $notification->getViewerId(),
            ]
        );

        return $this;
    }

    /**
     * Set notification flag to No for all users.
     *
     * @return void
     * @throws LocalizedException
     */
    public function flushNotifications()
    {
        $this->getConnection()->delete($this->getMainTable());
    }

}
