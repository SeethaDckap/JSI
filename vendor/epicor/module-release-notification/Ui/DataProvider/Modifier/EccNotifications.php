<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\ReleaseNotification\Ui\DataProvider\Modifier;

use Epicor\ReleaseNotification\Api\NotificationManagementInterface;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Session;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class EccNotifications implements ModifierInterface
{

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var NotificationManagementInterface
     */
    private $notificationManagement;

    /**
     * EccNotifications constructor.
     *
     * @param Session $session
     * @param Auth $auth
     * @param NotificationManagementInterface $notificationManagement
     */
    public function __construct(
        Session $session,
        Auth $auth,
        NotificationManagementInterface $notificationManagement
    ) {
        $this->session = $session;
        $this->auth = $auth;
        $this->notificationManagement = $notificationManagement;
    }

    /**
     * Modify data.
     *
     * @param array $data
     *
     * @return array|void
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Modify meta.
     *
     * @param array $meta
     *
     * @return array|void
     */
    public function modifyMeta(array $meta)
    {
        $content = $this->notificationManagement->getContent();

        if (empty($content)) {
            return $meta;
        }

        $this->session->setReleaseNotificationVersion($content['version']);
        if (
            $this->notificationManagement->canShow($content['version']) !== 1
            && !$this->session->getForceShowEccReleaseNotification()
        ) {
            return $meta;
        }
        $this->session->setForceShowEccReleaseNotification(false);

        // If no notification content, don't show.
        if (empty($content)) {
            return $meta;
        }

        if (!empty($meta['pages'])) {
            $meta['pages'] = array_merge($meta['pages'], $content['pages']);
        } else {
            $meta['pages'] = $content['pages'];
        }

        $this->session->setViewedVersion($content['version']);
        return $meta;
    }
}
