<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer;


/**
 * Quantity display, converts a row value to qty display
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $index = $this->getColumn()->getIndex();
        if ($row->getData($index)) {
            $value = $row->getData($index) * 1;
        } else {
            $value = $row->getData($index);
        }
        return $value;
    }

}
