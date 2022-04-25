<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Address\Renderer;


class Street extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();
    public function render(\Magento\Framework\DataObject $row)
    {
        $detailsArray = $row->getStreet();
        if (is_array($detailsArray)) {
            $string = implode(',', array_filter($detailsArray, 'strlen'));
        } else {
            $string = $detailsArray;
        }
        return $string;
    }

}
