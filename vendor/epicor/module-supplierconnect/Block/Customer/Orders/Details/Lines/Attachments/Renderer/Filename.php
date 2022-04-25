<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments\Renderer;


/**
 * Orders attachments editable file field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Filename extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Epicor\Common\Helper\File
     */
    private $commonFileHelper;

    /**
     * Filename constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Common\Helper\File $commonFileHelper
     * @param array $data
     */
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

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $line = $this->registry->registry('current_order_row');
        $key = 'existing';

        $helper = $this->commonFileHelper;

        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        $url = $helper->getFileUrl($row->getWebFileId(), $row->getErpFileId(), $row->getFilename(), $row->getUrl());

        $html = $value . ' <a href="' . $url . '" target="_blank" class="attachment_view">' . __('View') . '</a>';

        if ($row->getWebFileId()) {
            $html .= ' | ' . __('Update File') . ': <input type="file" name="lineattachments[' . $key . '][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][' . $index . ']" class="line_attachments_' . $index . '"/>';
        }

        return $html;
    }

}
