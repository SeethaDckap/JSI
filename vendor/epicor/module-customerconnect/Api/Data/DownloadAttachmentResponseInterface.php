<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Api\Data;

/**
 * Interface DownloadAttachmentResponseInterface
 * @package Epicor\Customerconnect\Api\Data
 */
interface DownloadAttachmentResponseInterface
{
    /**
     * Set Response data
     * @param $responseData
     * @return mixed
     */
    public function setResponseData($responseData);

    /**
     * get Response data
     * @return mixed
     */
    public function getResponseData();
}
