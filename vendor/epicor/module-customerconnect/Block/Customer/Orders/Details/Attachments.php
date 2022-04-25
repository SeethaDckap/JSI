<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details;

use \Magento\Framework\Registry;
use Epicor\Customerconnect\Helper\Data as CustomerconnectHelper;

/**
 * Class Attachments
 * @package Epicor\Customerconnect\Block\Customer\Orders\Details
 */
class Attachments extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * Attachments constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Registry $registry
     * @param CustomerconnectHelper $customerconnectHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Registry $registry,
        CustomerconnectHelper $customerconnectHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $data
        );

    }

    /**
     * set order details attachment
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/attachments.phtml');
        $this->setTitle(__('Attachments'));
    }

    /**
     * @return array
     */
    public function getOrderAttachmentData()
    {
        $attachment = [];
        $order = $this->registry->registry('customer_connect_order_details');
        if ($order) {
            $orderAttachments = $order->getAttachments();
            $attachmentData = ($orderAttachments) ? $orderAttachments->getasarrayAttachment() : array();
            if (!empty($attachmentData)) {
                foreach ($attachmentData as $k => $v) {
                    $attachmentNumber = !empty($v['attachment_number']) ? $v['attachment_number'] : '';
                    $attachmentDescription = (!empty($v['description'])) ? $v['description'] : '';
                    $fileName = (!empty($v['filename'])) ? $v['filename'] : '';
                    $erpFileId = (!empty($v['erp_file_id'])) ? $v['erp_file_id'] : '';

                    $attachment[$k]['attachment_number'] = $attachmentNumber;
                    $attachment[$k]['description'] = $attachmentDescription;
                    $attachment[$k]['erp_file_id'] = $erpFileId;
                    $attachment[$k]['filename'] = $fileName;


                }
                return $attachment;
            }

        }
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        $order = $this->registry->registry('customer_connect_order_details');
        if ($order) {
            return $orderNumber = !empty($order->getOrderNumber()) ? $order->getOrderNumber() : '';
        }
    }

    /**
     * @param $filename
     * @return string
     */
    public function getFileNameToDisplay($filename)
    {
        return $this->customerconnectHelper->getFileNameToDisplay($filename);
    }
}