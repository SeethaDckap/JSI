<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message;


/*
 * @method getMessageId()
 * @method getCreatedAt()
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

class Queue extends \Epicor\Database\Model\Message\Queue
{
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
        $this->_init('Epicor\Comm\Model\ResourceModel\Message\Queue');
    }

    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->isObjectNew()) {
            $this->setCreatedAt(microtime(true));
        }
    }

    public function clean()
    {
        $this->getResource()->clean();
    }

}
