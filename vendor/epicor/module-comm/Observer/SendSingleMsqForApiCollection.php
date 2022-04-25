<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

class SendSingleMsqForApiCollection  implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    private $commMessageRequestMsqFactory;

    /**
     * SendSingleMsqForApiCollection constructor.
     *
     * @param \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory
     */
    public function __construct(
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory
    ) {
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq Epicor_Comm_Model_Message_Request_Msq */

        $collection = $observer->getEvent()->getCollection();
        $msq->addProducts($collection->getItems());
        $msq->setTrigger('API Call');
        $msq->sendMessage();

    }
}
