<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Model\System\Message;

use Epicor\ReleaseNotification\Api\Data\LogInterface;
use Epicor\ReleaseNotification\Api\LogRepositoryInterface;
use Epicor\ReleaseNotification\Service\Configuration;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

class EccRelease implements MessageInterface
{
    /**
     * Identity.
     */
    const IDENTITY = 'ecc_release';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Configuration
     */
    private $productMetadata;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * EccRelease constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param Session $session
     * @param Configuration $productMetadata
     * @param LogRepositoryInterface $logRepository
     * @param BackendSession $backendSession
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Session $session,
        Configuration $productMetadata,
        LogRepositoryInterface $logRepository,
        BackendSession $backendSession
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->session = $session;
        $this->productMetadata = $productMetadata;
        $this->logRepository = $logRepository;
        $this->backendSession = $backendSession;
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
        return ($this->validateVersion() && $this->validateDate());
    }

    /**
     * Validates version.
     *
     * @return bool|int
     */
    private function validateVersion()
    {
        $version = '0.0.0';
        if (
            !empty($this->productMetadata->getEccVersion())
            && $this->productMetadata->isReleased()
        ) {
            $version = $this->productMetadata->getEccVersion();
        }

        return version_compare(
            $version,
            $this->backendSession->getReleaseNotificationVersion(),
            '<'
        );
    }

    /**
     * Validate date.
     *
     * @return bool
     */
    private function validateDate()
    {
        $hideDate = $this->logRepository
            ->getById($this->session->getUser()->getId())
            ->getHideDate();

        if (empty($hideDate)) {
            return true;
        }

        $today = date_create(gmdate("Y-m-d h:i:s"));
        $hide = date_create($hideDate);
        $diff = date_diff($today, $hide);
        return ((int)$diff->format("%a") > 5);
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl('ecc_release_notification/force/show');
        $hideUrl = $this->urlBuilder->getUrl('ecc_release_notification/force/hide');
        return __(
            "It is time for an ECC upgrade. Check out <a href='%1'>what's new</a> in our latest release. <a title='Click to dismiss the notification' href='%2'>[x]</a>",
            $url,
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
     * Hides system notification for user.
     *
     * @return void
     */
    public function hide()
    {
        /**
         * @var LogInterface
         */
        $log = $this->logRepository->getById($this->session->getUser()->getId());
        $log->setHideDate(gmdate("Y-m-d h:i:s"));
        $this->logRepository->save($log);
    }
}
