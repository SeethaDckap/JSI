<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Api;

/**
 * Interface DownloadAttachmentManagementInterface
 * @package Epicor\Customerconnect\Api
 */
interface DownloadAttachmentManagementInterface
{

    /**
     * @return Epicor\Customerconnect\Api\Data\DownloadAttachmentResponseInterface
     */
    public function downloadAttachment();
}