<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Plugin;

use Epicor\ReleaseNotification\Api\Data\LogInterface;
use Epicor\ReleaseNotification\Api\Data\LogInterfaceFactory;
use Epicor\ReleaseNotification\Api\LogRepositoryInterface;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\ResultInterface;
use Magento\ReleaseNotification\Controller\Adminhtml\Notification\MarkUserNotified;

class LogViews
{

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * LogViews constructor.
     *
     * @param LogRepositoryInterface $logRepository
     * @param Session $session
     * @param DataObjectHelper $dataObjectHelper
     * @param LogInterfaceFactory $logFactory
     * @param Auth $auth
     */
    public function __construct(
        LogRepositoryInterface $logRepository,
        Session $session,
        DataObjectHelper $dataObjectHelper,
        LogInterfaceFactory $logFactory,
        Auth $auth
    ) {
        $this->logRepository = $logRepository;
        $this->session = $session;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logFactory = $logFactory;
        $this->auth = $auth;
    }

    /**
     * Logs ECC release notification views.
     *
     * @param MarkUserNotified $subject
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    public function afterExecute(MarkUserNotified $subject, ResultInterface $result)
    {
        try {
            /**
             * @var LogInterface $log
             */
            $log = $this->logFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $log,
                [
                    'viewer_id' => $this->auth->getUser()->getId(),
                    'last_view_version' => $this->session->getViewedVersion()
                ],
                LogInterface::class
            );
            $this->logRepository->save($log);

            $responseContent = [
                'success' => true,
                'error_message' => ''
            ];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }

        return $result->setData($responseContent);
    }
}
