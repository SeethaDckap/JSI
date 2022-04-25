<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy\Renderer;


class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $type = $row->getData($this->getColumn()->getIndex());

        $types = \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy::$linkTypes;

        return isset($types[$type]) ? $types[$type] : $type;
    }

}
