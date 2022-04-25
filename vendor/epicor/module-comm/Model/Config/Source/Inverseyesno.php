<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


// this will give options whereby yes = 0 and no = 1
// this enables negative actions to be set to yes, when the actual value is no
class Inverseyesno
{

    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => "Yes"),
            array('value' => '1', 'label' => "No"),
        );
    }

}
