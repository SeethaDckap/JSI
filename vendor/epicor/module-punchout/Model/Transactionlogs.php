<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Punchout\Model;

use Magento\Framework\Model\AbstractModel;
use Epicor\Punchout\Api\Data\TransactionlogsInterface;

/**
 * Transactionlogs Model.
 */
class Transactionlogs extends AbstractModel implements TransactionlogsInterface
{


    /**
     *  Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Punchout\Model\ResourceModel\Transactionlogs');

    }//end _construct()


    /**
     * Get Transaction Log Id
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return parent::getData(self::ENTITY_ID);

    }


    /**
     * Get Connection Id
     *
     * @return int
     */
    public function getConnectionId()
    {
        return parent::getData(self::CONNECTION_ID);

    }


    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return parent::getData(self::TYPE);

    }


    /**
     * Get Start Date
     *
     * @return string
     */
    public function getStartDatestamp()
    {
        return parent::getData(self::START_DATESTAMP);

    }


    /**
     * Get End Date
     *
     * @return string
     */
    public function getEndDatestamp()
    {
        return parent::getData(self::END_DATESTAMP);

    }


    /**
     * Get Duration
     *
     * @return string
     */
    public function getDuration()
    {
        return parent::getData(self::DURATION);

    }


    /**
     * Get Message Code
     *
     * @return string
     */
    public function getMessageCode()
    {
        return parent::getData(self::MESSAGE_CODE);

    }


    /**
     * Get Message Status
     *
     * @return string
     */
    public function getMessageStatus()
    {
        return parent::getData(self::MESSAGE_STATUS);

    }


    /**
     * Get Cxml Request
     *
     * @return string|null
     */
    public function getCxmlRequest()
    {
        return parent::getData(self::CXML_REQUEST);

    }


    /**
     * Get Cxml response.
     *
     * @return string|null
     */
    public function getCxmlResponse()
    {
        return parent::getData(self::CXML_RESPONSE);

    }


    /**
     * Get Source Url.
     *
     * @return string|null
     */
    public function getSourceUrl()
    {
        return parent::getData(self::SOURCE_URL);

    }


    /**
     * Get Target Url.
     *
     * @return string|null
     */
    public function getTargetUrl()
    {
        return parent::getData(self::TARGET_URL);

    }


    /**
     * Set Name
     *
     * @param string $name Format type.
     *
     * @return TransactionlogsInterface
     */
    public function setFormat($name)
    {
        return $this->setData(self::FORMAT, $name);

    }//end setFormat()


    /**
     * Set Connection Id
     *
     * @param in $connectionId Connection Id.
     *
     * @return TransactionlogsInterface
     */
    public function setConnectionId($connectionId)
    {
        return $this->setData(self::CONNECTION_ID, $connectionId);

    }


    /**
     * Set Type
     *
     * @param string $type Type.
     *
     * @return TransactionlogsInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);

    }


    /**
     * Set Start Date
     *
     * @param string $startDatestamp Start Date.
     *
     * @return TransactionlogsInterface
     */
    public function setStartDatestamp($startDatestamp)
    {
        return $this->setData(self::START_DATESTAMP, $startDatestamp);

    }


    /**
     * Set End Date
     *
     * @param string $endDatestamp End Date.
     *
     * @return TransactionlogsInterface
     */
    public function setEndDatestamp($endDatestamp)
    {
        return $this->setData(self::END_DATESTAMP, $endDatestamp);

    }


    /**
     * Set Duration
     *
     * @param string $duration Duration.
     *
     * @return TransactionlogsInterface
     */
    public function setDuration($duration)
    {
        return $this->setData(self::DURATION, $duration);

    }


    /**
     * Set Message Code
     *
     * @param string $messageCode Message Code.
     *
     * @return TransactionlogsInterface
     */
    public function setMessageCode($messageCode)
    {
        return $this->setData(self::MESSAGE_CODE, $messageCode);

    }


    /**
     * Set Message Status
     *
     * @param string $messageStatus Message Status.
     *
     * @return TransactionlogsInterface
     */
    public function setMessageStatus($messageStatus)
    {
        return $this->setData(self::MESSAGE_STATUS, $messageStatus);

    }


    /**
     * Set Cxml Request
     *
     * @param string $cxmlRequest Cxml Request.
     *
     * @return TransactionlogsInterface
     */
    public function setCxmlRequest($cxmlRequest)
    {
        return $this->setData(self::CXML_REQUEST, $cxmlRequest);

    }


    /**
     * Set Cxml Response
     *
     * @param string $cxmlResponse Cxml Response.
     *
     * @return TransactionlogsInterface
     */
    public function setCxmlResponse($cxmlResponse)
    {
        return $this->setData(self::CXML_RESPONSE, $cxmlResponse);

    }


    /**
     * Set Source Url
     *
     * @param string $sourceUrl Source Url.
     *
     * @return TransactionlogsInterface
     */
    public function setSourceUrl($sourceUrl)
    {
        return $this->setData(self::SOURCE_URL, $sourceUrl);

    }


    /**
     * Set Target Url
     *
     * @param string $targetUrl Target Url.
     *
     * @return TransactionlogsInterface
     */
    public function setTargetUrl($targetUrl)
    {
        return $this->setData(self::TARGET_URL, $targetUrl);

    }

    /**
     * Set Start Time
     */
    public function startTiming()
    {
        $this->_timeStart = microtime(true);
        $this->setStartDatestamp(date('Y-m-d H:i:s'));
    }

    /**
     * Set End Time
     */
    public function endTiming()
    {
        $end = microtime(true);
        $duration = ($end - $this->_timeStart) * 1000;
        $this->setDuration($duration);
        $this->setEndDatestamp(date('Y-m-d H:i:s'));
    }

}//end class
