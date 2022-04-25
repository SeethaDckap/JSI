<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Response;

/**
 * Interface ResponseInterface
 *
 * @package Epicor\Punchout\Model\SetupResponse
 */
interface ResponseInterface
{
    const MSG_CODES = [
        '200' => [
            'code'    => '200',
            'text'    => 'success',
            'message' => 'Success',
            ],
        '400' => [
            'code'    => '400',
            'text'    => 'Bad Request',
            'message' => 'No XML in POST BODY',
        ],
        '401' => [
            'code'    => '401',
            'text'    => 'Unauthorized',
            'message' => 'Sender identity or shared secret is invalid',
        ],
        '500' => [
            'code'    => '500',
            'text'    => 'Internal Server Error',
            'message' => 'An unhandled error occurred while processing this request',
        ],
    ];


    /**
     * Send response
     *
     * @return \SimpleXMLElement
     */
    public function sendResponse($data);

}//end interface

