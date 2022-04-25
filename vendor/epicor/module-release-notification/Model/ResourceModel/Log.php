<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Model\ResourceModel;

use Epicor\ReleaseNotification\Api\Data\LogInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ecc_release_notification_viewer_log', 'id');
    }

    /**
     * Insert on duplicate notification viewed logs.
     *
     * @param LogInterface $log
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insert(LogInterface $log)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connection->insertOnDuplicate(
            $this->getMainTable(),
            [
                'viewer_id' => $log->getViewerId(),
                'last_view_version' => $log->getLastViewVersion(),
                'hide_date' => $log->getHideDate()
            ],
            [
                'last_view_version',
                'hide_date'
            ]
        );

        return $this;
    }
}
