<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;


class Rowdata extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData();
        $manufacturers = unserialize($row->getData('manufacturers'));
//      /  print_r($manufacturers);
        $data['manufacturers'] = json_encode($manufacturers);
        $jsonArray = json_encode($data);
        $html = '<input rel="' . $row->getId() . '" id="row-' . $row->getId() . '" '
                . 'name="rowData" type="hidden" value=\'' . $jsonArray . '\' class="rowdata" />';
        return $html;
    }

}
