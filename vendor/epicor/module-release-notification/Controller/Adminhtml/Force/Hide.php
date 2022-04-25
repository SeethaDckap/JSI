<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Controller\Adminhtml\Force;

use Epicor\ReleaseNotification\Model\System\Message\EccRelease;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

class Hide implements HttpGetActionInterface
{

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var EccRelease
     */
    private $systemMessage;

    /**
     * Show constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param EccRelease $systemMessage
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        EccRelease $systemMessage
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->systemMessage = $systemMessage;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->systemMessage->hide();

        /** @var Redirect $forward */
        $redirect = $this->redirectFactory->create();
        return $redirect->setRefererUrl();
    }
}
