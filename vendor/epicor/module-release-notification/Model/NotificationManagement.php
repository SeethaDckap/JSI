<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Model;

use Epicor\ReleaseNotification\Api\LogRepositoryInterface;
use Epicor\ReleaseNotification\Api\NotificationManagementInterface;
use Magento\Backend\Model\Auth;
use Magento\Framework\HTTP\Client\Curl;

class NotificationManagement implements NotificationManagementInterface
{
    /**
     * Notification URL.
     */
    const NOTI_URL = 'https://update.epicorcommerce.com/release-notification/updates.json';

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * NotificationManagement constructor.
     *
     * @param Auth $auth
     * @param LogRepositoryInterface $logRepository
     * @param Curl $curl
     */
    public function __construct(
        Auth $auth,
        LogRepositoryInterface $logRepository,
        Curl $curl
    ) {
        $this->auth = $auth;
        $this->logRepository = $logRepository;
        $this->curl = $curl;
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        try {
            $this->curl->get(self::NOTI_URL);
            return \GuzzleHttp\json_decode($this->curl->getBody(), true);
        } catch (\Exception $e) {
            return [];
        }

    }

    /**
     * @inheritDoc
     */
    public function canShow($version)
    {
        return version_compare(
            $version,
            $this->logRepository->getById(
                $this->auth->getUser()->getId()
            )->getLastViewVersion()
        );
    }
}
