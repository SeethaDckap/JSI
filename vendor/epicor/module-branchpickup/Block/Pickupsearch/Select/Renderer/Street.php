<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer;


class Street extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();
    public function render(\Magento\Framework\DataObject $row)
    {
        //M1 > M2 Translation Begin (Rule 9)
       /* $address1 = $row->getAddress1();
        $address2 = $row->getAddress2();
        $address3 = $row->getAddress3();*/
        $address1 = $row->getData('address1');
        $address2 = $row->getData('address2');
        $address3 = $row->getData('address3');
        //M1 > M2 Translation End
        $detailsArray = array($address1, $address2, $address3);
        $string = implode(',', array_filter($detailsArray, 'strlen'));
        return $string;
    }

}
