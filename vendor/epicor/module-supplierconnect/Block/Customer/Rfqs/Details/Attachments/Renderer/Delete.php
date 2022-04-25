<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;

/**
 * Attachment Delete field renderer class.
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{


    /**
     * Constructor function
     *
     * @param Context $context Context class.
     * @param array   $data    Data.
     */
    public function __construct(
        Context $context,
        array $data=[]
    ) {
        parent::__construct(
            $context,
            $data
        );

    }//end __construct()


    /**
     * Render function.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $key  = 'existing';
        $html = '';
        if (!$row->getWebFileId()) {
            return $html;
        }

        $html       = '<input type="checkbox" class="attachments_delete"
            name="attachments[existing]['.$row->getUniqueId().']['.$row->getQuantity().'][delete]" value="1"/>';
        $oldDetails = array(
            'description'       => $row->getDescription(),
            'filename'          => $row->getFilename(),
            'erp_file_id'       => $row->getErpFileId(),
            'web_file_id'       => $row->getWebFileId(),
            'attachment_number' => $row->getAttachmentNumber(),
            'url'               => $row->getUrl(),
            'attachment_status' => $row->getAttachmentStatus(),
        );

        $html .= '<input type="hidden" name="attachments['.$key.']['.$row->getUniqueId().'][old_data]" value="'.base64_encode(serialize($oldDetails)).'" /> ';

        return $html;

    }//end render()


}//end class
