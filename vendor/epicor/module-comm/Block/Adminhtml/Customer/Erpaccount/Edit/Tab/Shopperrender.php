<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


class Shopperrender extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        if ($value == 1) {
            $val = "<input name=shoppers[]" . " type='checkbox' value=" . $row->getId() . " checked=checked>";
        } else {
            $val = "<input name=shoppers[]" . " type='checkbox' value=" . $row->getId() . ">";
        }
        return $val;
    }

}
