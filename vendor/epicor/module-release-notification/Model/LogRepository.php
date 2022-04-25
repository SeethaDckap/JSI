<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Model;

use Epicor\ReleaseNotification\Api\Data\LogInterface;
use Epicor\ReleaseNotification\Api\LogRepositoryInterface;
use Epicor\ReleaseNotification\Model\ResourceModel\Log;

class LogRepository implements LogRepositoryInterface
{

    /**
     * @var Log
     */
    private $logResource;

    /**
     * @var ResourceModel\Collection\Log
     */
    private $logCollection;

    /**
     * LogRepository constructor.
     *
     * @param Log $logResource
     * @param ResourceModel\Collection\Log $logCollection
     */
    public function __construct(
        Log $logResource,
        ResourceModel\Collection\Log $logCollection
    ) {
        $this->logResource = $logResource;
        $this->logCollection = $logCollection;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(LogInterface $log)
    {
        $this->logResource->insert($log);
    }

    /**
     * @inheritDoc
     */
    public function getById($userId)
    {
        return $this->logCollection->addFieldToFilter(
            'viewer_id',
            [
                'eq' => $userId
            ]
        )->load()->getFirstItem();
    }
}
