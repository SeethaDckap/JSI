<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer;


/**
 * Order line attachments column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $html .= '</td>'
            . '</tr>'
            . '<tr class="lines_row attachment" id="row-attachments-' . $row->getId() . '" style="display: none;">'
            . '<td colspan="11">';

        $block = $this->getLayout()->createBlock('Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments');

        $html .= $block->toHtml();

        return $html;
    }
}
