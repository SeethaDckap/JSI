<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\System\Message;

use Epicor\Telemetry\Api\Data\NotificationInterface;
use Epicor\Telemetry\Api\Data\NotificationInterfaceFactory;
use Epicor\Telemetry\Model\ResourceModel\Notification;
use Epicor\Telemetry\Service\Configuration;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

class TelemetryEnabled implements MessageInterface
{
    /**
     * Identity.
     */
    const IDENTITY = 'telemetry_enabled';

    /**
     * @var NotificationInterfaceFactory
     */
    private $notificationFactory;

    /**
     * @var Notification
     */
    private $notificationResource;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Epicor\Telemetry\Model\ResourceModel\Collection\Notification
     */
    private $notificationCollection;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * TelemetryEnabled constructor.
     *
     * @param NotificationInterfaceFactory $notificationFactory
     * @param Notification $notificationResource
     * @param Session $session
     * @param UrlInterface $urlBuilder
     * @param \Epicor\Telemetry\Model\ResourceModel\Collection\Notification $notificationCollection
     * @param Configuration $config
     */
    public function __construct(
        NotificationInterfaceFactory $notificationFactory,
        Notification $notificationResource,
        Session $session,
        UrlInterface $urlBuilder,
        \Epicor\Telemetry\Model\ResourceModel\Collection\Notification $notificationCollection,
        Configuration $config
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->notificationResource = $notificationResource;
        $this->session = $session;
        $this->urlBuilder = $urlBuilder;
        $this->notificationCollection = $notificationCollection;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getIdentity()
    {
        return self::IDENTITY;
    }

    /**
     * @inheritDoc
     */
    public function isDisplayed()
    {
        return !$this->hasDismissed() && $this->config->isEnabled();
    }

    /**
     * Has user dismissed notification?
     *
     * @return bool
     */
    public function hasDismissed()
    {
        return $this->notificationCollection->addFieldToFilter(
            'viewer_id',
            [
                'eq' => $this->session->getUser()->getId(),
            ]
        )->load()->count() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        $hideUrl = $this->urlBuilder->getUrl('ecc_telemetry_notification/force/hide');
        return __(
            "To help troubleshoot issues, analyze trends and improve our products and services, Epicor will, unless you affirmatively opt out, collect and process general license, usage and non-personal telemetric data. The data includes date and time of access, IP addresses, computer/browser types, active features and performance metrics. The collected information is non-personal, non-identifiable, and subject to Epicor Privacy Policy which is hereby incorporated by reference. <br><br>Epicor will not collect the telemetric data for companies located in Russia and China due to legal requirements in these countries. <br><br> We hope that you participate in this service. If you choose to decline participation in this data collection process, you many opt out at any time. To do so, go to Epicor > Configuration > Networking and General > Telemetry. <a title='Click to dismiss the notification' href='%1'>[x]</a>",
            $hideUrl
        );
    }

    /**
     * @inheritDoc
     */
    public function getSeverity()
    {
        return self::SEVERITY_NOTICE;
    }

    /**
     * Hide notification.
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hide()
    {
        /**
         * @var $notification NotificationInterface
         */
        $notification = $this->notificationFactory->create();
        $notification->setViewerId($this->session->getUser()->getId());

        $this->notificationResource->insert($notification);
    }
}
