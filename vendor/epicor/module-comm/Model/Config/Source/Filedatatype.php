<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Filedatatype
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'U', 'label' => "Url"),
            array('value' => 'D', 'label' => "Data"),
        );
    }

}
