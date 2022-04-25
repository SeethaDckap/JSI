<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer;

/**
 * Class Attachments
 * @package Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer
 */
class Attachments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @const prefix for order part number
     */
    const PREFIX = 'part';

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $index = $this->getColumn()->getIndex();
        $attachmentData = !empty($row->getData($index)) ? $row->getData($index) : '';
        if (!empty($attachmentData)) {
            $html .= $this->getLineAttachmentsHtml($row, $attachmentData);
        }

        return $html;
    }

    /**
     * @param $row
     * @param $attachmentData
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getLineAttachmentsHtml($row, $attachmentData)
    {
        $orderPartNumber = !empty($row->getData('product_code')) ? self::PREFIX . '_' . $row->getData('product_code') : '';
        return $this->getLayout()
            ->createBlock('\Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\LineAttachments')
            ->setData('attachment_data', $attachmentData)
            ->setData('order_part_number', $orderPartNumber)
            ->setTemplate('Epicor_Customerconnect::customerconnect/attachments/line_attachments.phtml')
            ->toHtml();
    }
}