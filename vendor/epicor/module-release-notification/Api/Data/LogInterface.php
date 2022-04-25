<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Api\Data;

/**
 * Interface LogInterface
 * @package Epicor\ReleaseNotification\Api\Data
 * @api
 */
interface LogInterface
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

    /**
     * Get last viewed version.
     *
     * @return string
     */
    public function getLastViewVersion();

    /**
     * Set last viewed version.
     *
     * @param string $lastViewedVersion
     *
     * @return $this
     */
    public function setLastViewVersion($lastViewedVersion);

    /**
     * Get hide date.
     *
     * @return string
     */
    public function getHideDate();

    /**
     * Set hide date.
     *
     * @param string $date
     *
     * @return $this
     */
    public function setHideDate($date);
}
