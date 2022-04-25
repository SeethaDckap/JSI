<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Controller\Adminhtml\Force;

use Epicor\Telemetry\Model\System\Message\TelemetryEnabled;
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
     * @var TelemetryEnabled
     */
    private $systemMessage;

    /**
     * Show constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param TelemetryEnabled $systemMessage
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        TelemetryEnabled $systemMessage
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
