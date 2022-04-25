<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Api\Data;

/**
 * Interface NotificationInterface.
 * @package Epicor\Telemetry\Api\Data
 * @api
 */
interface NotificationInterface
{
    /**
     * Get log ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Set log ID.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get ID of the user who viewed the notification.
     *
     * @return int
     */
    public function getViewerId();

    /**
     * Set ID of the user who viewed the notification.
     *
     * @param int $viewerId
     *
     * @return $this
     */
    public function setViewerId($viewerId);
}
