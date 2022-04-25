<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer;


/**
 * Description of File
 *
 * @author Paul.Ketelle
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Common_Model_File */

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $link = $return->getAttachmentLink($row->getId());
        /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */

        $checked = ($link->getToBeDeleted() == 'Y') ? ' checked="checked"' : '';

        $html = '<input type="checkbox" class="attachment_delete" name="attachments[existing][' . $row->getUniqueId() . '][delete]"' . $checked . ' />';

        $oldDetails = array(
            'return_id' => $return->getId(),
            'line_id' => '',
            'attachment_id' => $row->getId(),
            'web_file_id' => $row->getId(),
            'erp_file_id' => $row->getErpId(),
            'url' => $row->getUrl(),
        );

        $html .= '<input type="hidden" name="attachments[existing][' . $row->getUniqueId() . '][old_data]" value="' . base64_encode(serialize($oldDetails)) . '" /> ';

        return $html;
    }

}
