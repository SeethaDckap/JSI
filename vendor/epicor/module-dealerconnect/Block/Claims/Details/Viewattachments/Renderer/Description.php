<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Viewattachments\Renderer;


/**
 * Line comment display
 *
 * @author     Epicor Websales Team
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $key = $this->registry->registry('claim_new') ? 'new' : 'existing';
        $helper = $this->commonFileHelper;

        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);
        $fileindex = 'filename';
        $url = $helper->getFileUrl($row->getWebFileId(), $row->getErpFileId(), $row->getFilename(), $row->getUrl());
        $html = '<span style="margin-right: 16px">'.$value.'</span>'.'<a href="' . $url . '" target="_blank" class="attachment_view">View</a>';

        if ($this->registry->registry('claims_editable') || $this->registry->registry('claims_editable_partial')) {
            $html .= ' | ' . __('Update File') . ': <input type="file" name="attachments[' . $key . '][' . $row->getUniqueId() . '][' . $fileindex . ']" value="' . $value . '" class="lines_' . $index . '"/>';
        }

        return $html;
    }

}
