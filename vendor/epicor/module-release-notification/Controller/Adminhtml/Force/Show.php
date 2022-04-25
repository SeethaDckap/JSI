<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Controller\Adminhtml\Force;

use Magento\Backend\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

class Show implements HttpGetActionInterface
{

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * Show constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param Session $session
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Session $session
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->session->setForceShowEccReleaseNotification(true);
        $this->session->setViewedVersion('');

        /** @var Redirect $forward */
        $redirect = $this->redirectFactory->create();
        return $redirect->setPath('admin/dashboard');
    }
}
