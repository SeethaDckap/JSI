<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Api
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Api\Data;

/**
 * Punchout Transaction logs interface.
 * @api
 */
interface TransactionlogsInterface
{

    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ENTITY_ID       = 'entity_id';
    const CONNECTION_ID   = 'connection_id';
    const TYPE            = 'type';
    const START_DATESTAMP = 'start_datestamp';
    const END_DATESTAMP   = 'end_datestamp';
    const DURATION        = 'duration';
    const MESSAGE_CODE    = 'message_code';
    const MESSAGE_STATUS  = 'message_status';
    const CXML_REQUEST    = 'cxml_request';
    const CXML_RESPONSE   = 'cxml_response';
    const SOURCE_URL      = 'source_url';
    const TARGET_URL      = 'target_url';


    /**
     * Get Transaction Log Id
     *
     * @return int|null
     */
    public function getEntityId();


    /**
     * Get Connection Id
     *
     * @return int
     */
    public function getConnectionId();


    /**
     * Get Type
     *
     * @return string
     */
    public function getType();


    /**
     * Get Start Date
     *
     * @return string
     */
    public function getStartDatestamp();


    /**
     * Get End Date
     *
     * @return string
     */
    public function getEndDatestamp();


    /**
     * Get Duration
     *
     * @return string
     */
    public function getDuration();


    /**
     * Get Message Code
     *
     * @return string
     */
    public function getMessageCode();


    /**
     * Get Message Status
     *
     * @return string
     */
    public function getMessageStatus();


    /**
     * Get Cxml Request
     *
     * @return string|null
     */
    public function getCxmlRequest();


    /**
     * Get Cxml response.
     *
     * @return string|null
     */
    public function getCxmlResponse();


    /**
     * Get Source Url.
     *
     * @return string|null
     */
    public function getSourceUrl();


    /**
     * Get Target Url.
     *
     * @return string|null
     */
    public function getTargetUrl();


    /**
     * Set Connection Id
     *
     * @param  in $connectionId Connection Id.
     *
     * @return TransactionlogsInterface
     */
    public function setConnectionId($connectionId);


    /**
     * Set Type
     *
     * @param  string $type Type.
     *
     * @return TransactionlogsInterface
     */
    public function setType($type);


    /**
     * Set Start Date
     *
     * @param  string $startDatestamp Start Date.
     *
     * @return TransactionlogsInterface
     */
    public function setStartDatestamp($startDatestamp);


    /**
     * Set End Date
     *
     * @param  string $endDatestamp End Date.
     *
     * @return TransactionlogsInterface
     */
    public function setEndDatestamp($endDatestamp);


    /**
     * Set Duration
     *
     * @param  string $duration Duration.
     *
     * @return TransactionlogsInterface
     */
    public function setDuration($duration);


    /**
     * Set Message Code
     *
     * @param  string $messageCode Message Code.
     *
     * @return TransactionlogsInterface
     */
    public function setMessageCode($messageCode);


    /**
     * Set Message Status
     *
     * @param  string $messageStatus Message Status.
     *
     * @return TransactionlogsInterface
     */
    public function setMessageStatus($messageStatus);


    /**
     * Set Cxml Request
     *
     * @param  string $cxmlRequest Cxml Request.
     *
     * @return TransactionlogsInterface
     */
    public function setCxmlRequest($cxmlRequest);


    /**
     * Set Cxml Response
     *
     * @param  string $cxmlResponse Cxml Response.
     *
     * @return TransactionlogsInterface
     */
    public function setCxmlResponse($cxmlResponse);


    /**
     * Set Source Url
     *
     * @param string $sourceUrl Source Url.
     *
     * @return TransactionlogsInterface
     */
    public function setSourceUrl($sourceUrl);


    /**
     * Set Target Url
     *
     * @param string $targetUrl Target Url.
     *
     * @return TransactionlogsInterface
     */
    public function setTargetUrl($targetUrl);

}
