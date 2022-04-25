<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Attachments\Renderer;


/**
 * RFQ attachments editable file field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Filename extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $rfq = $this->registry->registry('current_rfq_row');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */

        $helper = $this->commonFileHelper;

        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        $url = $helper->getFileUrl($row->getWebFileId(), $row->getErpFileId(), $row->getFilename(), $row->getUrl());

        $html = $value . ' <a href="' . $url . '" target="_blank" class="attachment_view">' . __('View') . '</a>';

        if ($this->registry->registry('rfqs_editable') || $this->registry->registry('rfqs_editable_partial')) {
            $html .= ' | ' . __('Update File') . ': <input type="file" name="lineattachments[' . $key . '][' . $rfq->getUniqueId() . '][' . $row->getUniqueId() . '][' . $index . ']" class="line_attachments_' . $index . '"/>';
        }

        return $html;
    }

}
