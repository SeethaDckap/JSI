<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Invoiceordering
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'N', 'label' => "Invoice Number Ascending"),
            array('value' => 'n', 'label' => "Invoice Number Descending"),
            array('value' => 'R', 'label' => "Reference Ascending"),
            array('value' => 'r', 'label' => "Reference Descending"),
            array('value' => 'D', 'label' => "Due Date Ascending"),
            array('value' => 'd', 'label' => "Due Date Descending"),
            array('value' => 'I', 'label' => "Invoice Date Ascending"),
            array('value' => 'i', 'label' => "Invoice Date Descending")
        );
    }

}
