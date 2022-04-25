<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Notification\Grid\Renderer;

/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Model\Adminnotification\InboxFactory
     */
    protected $commAdminnotificationInboxFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Model\Adminnotification\InboxFactory $commAdminnotificationInboxFactory,
        array $data = []
    ) {
        $this->commAdminnotificationInboxFactory = $commAdminnotificationInboxFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $full_url = $this->commAdminnotificationInboxFactory->create()->getUrl($row->getUrl());
        //M1 > M2 Translation Begin (Rule p2-7)
        //$readDetailsHtml = ($full_url) ? '<a target="_blank" href="' . $full_url . '">' .
         //   Mage::helper('adminnotification')->__('Read Details') . '</a> | ' : '';

        //$markAsReadHtml = (!$row->getIsRead()) ? '<a href="' . $this->getUrl('*/*/markAsRead/', array('_current' => true, 'id' => $row->getId())) . '">' .
        //    Mage::helper('adminnotification')->__('Mark as Read') . '</a> | ' : '';

        $readDetailsHtml = ($full_url) ? '<a target="_blank" href="' . $full_url . '">' .
            __('Read Details') . '</a> | ' : '';
        $markAsReadHtml = (!$row->getIsRead()) ? '<a href="' . $this->getUrl('*/*/markAsRead/', array('_current' => true, 'id' => $row->getId())) . '">' .
            __('Mark as Read') . '</a> | ' : '';
        //M1 > M2 Translation End

        //M1 > M2 Translation Begin (Rule p2-7)
//        return sprintf('%s%s<a href="%s" onClick="deleteConfirm(\'%s\', this.href); return false;">%s</a>', $readDetailsHtml, $markAsReadHtml, $this->getUrl('*/*/remove/', array(
//                '_current' => true,
//                'id' => $row->getId(),
//                \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl())
//        ), Mage::helper('adminnotification')->__('Are you sure?'), Mage::helper('adminnotification')->__('Remove')
//        );
        return sprintf('%s%s<a href="%s" onClick="deleteConfirm(\'%s\', this.href); return false;">%s</a>', $readDetailsHtml, $markAsReadHtml, $this->getUrl('*/*/remove/', array(
                '_current' => true,
                'id' => $row->getId(),
                \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl())
        ), __('Are you sure?'), __('Remove')
        );
        //M1 > M2 Translation End

    }

}
