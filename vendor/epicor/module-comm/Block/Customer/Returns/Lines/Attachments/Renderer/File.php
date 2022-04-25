<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer;


/**
 * Return attachments editable file field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class File extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\File $commonFileHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonFileHelper = $commonFileHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Common_Model_File */

        $line = $this->registry->registry('current_return_line');
        /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $index = $this->getColumn()->getIndex();

        $url = $helper->getFileUrl($row->getId(), $row->getErpId(), $row->getFilename(), $row->getUrl());
        $html = $row->getFilename() . ' <a href="' . $url . '" target="_blank" class="attachment_view">View</a>';

        if (!$this->registry->registry('review_display') && $line->isActionAllowed('Attachments')) {
            $link = $line->getAttachmentLink($row->getId());
            /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */

            $disabled = ($link->getToBeDeleted() == 'Y' || $line->getToBeDeleted() == 'Y' ) ? ' disabled="disabled"' : '';
            $html .= ' | ' . __('Update File') . ': <input type="file" name="lineattachments[existing][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][' . $index . ']" class="line_attachments_' . $index . '"' . $disabled . '/>';
        }

        return $html;
    }

}
