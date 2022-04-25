<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Observer;

class MessagingProcessCall extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Reports\Model\RawdataFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $model Epicor_Reports_Model_Rawdata */
        $model = $this->reportsRawdataFactory->create();
        $model->insertMessage($observer->getLog());
    }

}