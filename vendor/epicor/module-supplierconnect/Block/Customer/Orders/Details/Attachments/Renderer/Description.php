<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Attachments\Renderer;

use Magento\Framework\DataObject;

/**
 * Attachment description field renderer class.
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Render function.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $key   = 'existing';
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);
        if ($row->getWebFileId()) {
            $html = '<input type="text" name="attachments['.$key.']['.$row->getUniqueId().']['.$index.']" value="'.$value.'" class="attachments_'.$index.'"/>';
        } else {
            $html = $value;
        }
        return $html;
    }//end render()

}//end class
