<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Model;


/**
 * Created by PhpStorm.
 * User: lguerra
 * Date: 9/3/14
 * Time: 11:06 AM
 */
class Rawdata extends \Epicor\Database\Model\Reports\Raw\Data
{

    const REPORT_TYPE_SPEED = 'speed';
    const REPORT_TYPE_MIN_MAX_AVERAGE = 'minmaxavg';
    const REPORT_TYPE_PERFORMANCE = 'performance';

    var $table_name;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory
     */
    protected $commResourceMessageLogCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;
        $this->messageManager = $messageManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    function _construct()
    {
        $this->_init('Epicor\Reports\Model\ResourceModel\Rawdata');
    }

    /**
     * Grab the records currently stored on ecc_message_log that have no link in the raw data
     */
    function reprocessMessageLogData()
    {
        /* @var $messagesNotLogged Epicor_Comm_Model_Resource_Message_Log_Collection */
        $messagesNotLogged = $this->commResourceMessageLogCollectionFactory->create();

        $messagesNotLogged->addFieldToFilter('raw.messaging_log_id', array('null' => true));
        $messagesNotLogged->getSelect()->joinLeft(array('raw' => 'ecc_reports_raw_data'), 'main_table.id = raw.messaging_log_id', 'entity_id');
        $messagesNotLogged->addFieldToSelect('id')->addFieldToSelect('store')->addFieldToSelect('message_type')->addFieldToSelect('message_status')->
            addFieldToSelect('duration')->addFieldToSelect('start_datestamp')->addFieldToSelect('cached');

        $items = $messagesNotLogged->getItems();

        if (count($items) == 0) {
            //M1 > M2 Translation Begin (Rule p2-5.1)
            //Mage::getSingleton('reports/session')->addWarning(__('No records to process'));
            $this->messageManager->addWarningMessage(__('No records to process'));
            //M1 > M2 Translation End
            return;
        }
        foreach ($items as $messageNotLogged) {
            $this->insertMessage($messageNotLogged);
        }

        //M1 > M2 Translation Begin (Rule p2-5.1)
        //Mage::getSingleton('reports/session')->addSuccess(sprintf(__('%s record(s) processed successfully'), count($items)));
        $this->messageManager->addSuccessMessage(sprintf(__('%s record(s) processed successfully'), count($items)));
        //M1 > M2 Translation End
    }

    /**
     * Insert new row from a message
     * @param \Epicor\Comm\Model\Message\Log $log
     */
    function insertMessage(\Epicor\Comm\Model\Message\Log $log)
    {
        $model = $this;
        $model->setStore($log->getStore());
        $model->setMessageType($log->getMessageType());
        $status = $log->getMessageStatus() == $log::MESSAGE_STATUS_SUCCESS ? 'successful' : 'unsuccessful';
        $model->setMessageStatus($status);
        $model->setDuration($log->getDuration());
        $model->setTime($log->getStartDatestamp());
        $model->setCached($log->getCached());
        $model->setMessagingLogId($log->getId());
        $model->save();
    }

}
