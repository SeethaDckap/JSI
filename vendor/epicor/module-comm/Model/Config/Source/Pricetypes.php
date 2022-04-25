<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Pricetypes
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'Range', 'label' => 'Range'),
            array('value' => 'Default', 'label' => 'Default')
        );
    }

}
