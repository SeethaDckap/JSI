<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Plugin;

use Magento\Backend\Model\Session;
use Magento\ReleaseNotification\Model\Condition\CanViewNotification;

class ShowEccNotification
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Validate if notification popup can be shown and set the notification flag.
     *
     * @param CanViewNotification $subject Parent class.
     * @param bool                $result Result from parent function.
     *
     * @return bool
     */
    public function afterIsVisible(CanViewNotification $subject, bool $result)
    {
        if ($this->session->getForceShowEccReleaseNotification() === false) {
            return false;
        }

        return true;
    }
}
