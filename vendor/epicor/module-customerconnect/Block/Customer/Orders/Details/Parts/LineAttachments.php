<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details\Parts;

use Epicor\Customerconnect\Helper\Data as CustomerconnectHelper;
use Magento\Framework\View\Element\Template;

/**
 * Class LineAttachments
 * @package Epicor\Customerconnect\Block\Customer\Orders\Details\Parts
 */
class LineAttachments extends Template
{
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * Attachments constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerconnectHelper $customerconnectHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerconnectHelper $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $data
        );

    }

    /**
     * @return mixed
     */
    public function getAttachmentData()
    {
        return $this->getData('attachment_data');
    }

    /**
     * @return mixed
     */
    public function getOrderPartNumber()
    {
        return $this->getData('order_part_number');
    }

    /**
     * @return array
     */
    public function getLineAttachmentsData()
    {
        $attachment = [];
        $attachmentData = $this->getAttachmentData();

        if (is_array($attachmentData) && count($attachmentData) > 0) {
            foreach ($attachmentData as $k => $value) {
                $attachmentNumber = !empty($value['attachment_number']) ? $value['attachment_number'] : '';
                $attachmentDescription = (!empty($value['description'])) ? $value['description'] : '';
                $fileName = (!empty($value['filename'])) ? $value['filename'] : '';
                $erpFileId = (!empty($value['erp_file_id'])) ? $value['erp_file_id'] : '';

                $attachment[$k]['attachment_number'] = $attachmentNumber;
                $attachment[$k]['description'] = $attachmentDescription;
                $attachment[$k]['erp_file_id'] = $erpFileId;
                $attachment[$k]['filename'] = $fileName;
            }
        }
        return $attachment;
    }

    /**
     * @param $filename
     * @return mixed
     */
    public function getFileNameToDisplay($filename)
    {
        return $this->customerconnectHelper->getFileNameToDisplay($filename);
    }

    /**
     * @param $fileName
     * @return string
     */
    public function displayFilename($fileName)
    {
        return $out = strlen($fileName) > 15 ? substr($fileName, 0, 15) . ".." : fileName;
    }
}