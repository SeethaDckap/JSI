<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Attachments\Renderer;


/**
 * RFQ line attachment delete tickbox
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $line = $this->registry->registry('current_rfq_row');

        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';

        $html = '<input type="checkbox" class="line_attachments_delete" name="lineattachments[' . $key . '][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][delete]" />';
        $oldDetails = array(
            'description' => $row->getDescription(),
            'filename' => $row->getFilename(),
            'erp_file_id' => $row->getErpFileId(),
            'web_file_id' => $row->getWebFileId(),
            'attachment_number' => $row->getAttachmentNumber(),
            'url' => $row->getUrl(),
            'attachment_status' => $row->getAttachmentStatus()
        );

        $html .= '<input type="hidden" name="lineattachments[' . $key . '][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][old_data]" value="' . base64_encode(serialize($oldDetails)) . '" /> ';
        $html .= '<input type="hidden" name="lineattachments[' . $key . '][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][is_duplicate]" value="1" /> ';

        return $html;
    }

}
