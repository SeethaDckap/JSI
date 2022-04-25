<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\Data;

use Epicor\Customerconnect\Api\Data\DownloadAttachmentResponseInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Interface DownloadAttachmentResponse
 * @package Epicor\Customerconnect\Model\Data
 */
class DownloadAttachmentResponse extends AbstractExtensibleObject implements DownloadAttachmentResponseInterface
{
    /**
     * @const RESPONSE_DATA
     */
    const RESPONSE_DATA = 'data';

    /**
     * set Response data
     * @param $responseData
     * @return DownloadAttachmentResponse|mixed
     */
    public function setResponseData($responseData)
    {
        return $this->setData(self::RESPONSE_DATA, $responseData);
    }

    /**
     * get Response data
     * @return mixed|null
     */
    public function getResponseData()
    {
        return $this->_get(self::RESPONSE_DATA);
    }
}
