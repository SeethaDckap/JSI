<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Api;

/**
 * Interface NotificationManagementInterface.
 *
 * @package Epicor\ReleaseNotification\Api
 * @api
 */
interface NotificationManagementInterface
{
    /**
     * Get notification content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Checks whether notification can be shown to user.
     *
     * @param string $version
     *
     * @return bool
     */
    public function canShow($version);
}
