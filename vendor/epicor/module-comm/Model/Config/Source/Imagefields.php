<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Imagefields
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ""),
            array('value' => '_attributes/number', 'label' => "Number"),
            array('value' => '_attributes/type', 'label' => "Type"),
            array('value' => 'filename', 'label' => "Filename"),
            array('value' => 'description', 'label' => "Description"),
        );
    }

}
