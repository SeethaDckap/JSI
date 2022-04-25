<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Api;

use Epicor\ReleaseNotification\Api\Data\LogInterface;

/**
 * Interface LogRepositoryInterface
 * @package Epicor\ReleaseNotification\Api
 * @api
 */
interface LogRepositoryInterface
{
    /**
     * Save log.
     *
     * @param LogInterface $log
     *
     * @return $this
     */
    public function save(LogInterface $log);

    /**
     * Get notification log by user id.
     *
     * @param int $userId
     *
     * @return LogInterface
     */
    public function getById($userId);
}
