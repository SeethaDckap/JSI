<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * @method getMessageParent()
 * @method getMessageType()
 * @method getMessageSubject()
 * @method getStartDateStamp()
 * @method getEndDateStamp()
 * @method getDuration()
 * @method getStatusCode()
 * @method getStatusDescription()
 * @method getXmlIn()
 * @method getXmlOut()
 * 
 * @method setMessageParent()
 * @method setMessageType()
 * @method setMessageSubject()
 * @method setStartDateStamp()
 * @method setEndDateStamp()
 * @method setDuration()
 * @method setStatusCode()
 * @method setStatusDescription()
 * @method setXmlIn()
 * @method setXmlOut()
 */

class Log extends \Epicor\Database\Model\Message\Log
{

    const MESSAGE_STATUS_INPROGRESS = 0;
    const MESSAGE_STATUS_WARNING = 1;
    const MESSAGE_STATUS_ERROR = 2;
    const MESSAGE_STATUS_SUCCESS = 3;
    const MESSAGE_STATUS_REPROCESSED = 4;
    const MESSAGE_STATUS_UNKNOWN = 5;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Comm\Model\ResourceModel\Message\Log');
    }

    public function getMessageStatuses()
    {
        return array(
            self::MESSAGE_STATUS_INPROGRESS => 'In Progress',
            self::MESSAGE_STATUS_WARNING => 'Warning',
            self::MESSAGE_STATUS_ERROR => 'Error',
            self::MESSAGE_STATUS_SUCCESS => 'Success',
            self::MESSAGE_STATUS_UNKNOWN => 'Unknown',
            self::MESSAGE_STATUS_REPROCESSED => 'Reprocessed',
        );
    }

    public function getMessageParents()
    {
        return array(
            'Request' => 'Request',
            'Upload' => 'Upload',
        );
    }

    public function startTiming()
    {
        $this->_timeStart = microtime(true);
        //M1 > M2 Translation Begin (Rule 25)
        //$this->setStartDatestamp(now());
        $this->setStartDatestamp(date('Y-m-d H:i:s'));
        //M1 > M2 Translation End
    }

    public function endTiming()
    {
        $end = microtime(true);
        $duration = ($end - $this->_timeStart) * 1000;
        $this->setDuration($duration);
        //M1 > M2 Translation Begin (Rule 25)
        //$this->setEndDatestamp(now());
        $this->setEndDatestamp(date('Y-m-d H:i:s'));
        //M1 > M2 Translation End
    }

    public function clean()
    {
        $this->getResource()->clean();
    }

}
