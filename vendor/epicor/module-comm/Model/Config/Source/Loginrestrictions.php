<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Loginrestrictions
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => 'Standard Login'),
            array('value' => 'store', 'label' => 'Restrict by Store Only'),
            array('value' => 'currency', 'label' => 'Restrict by Currency Only'),
            array('value' => 'full', 'label' => 'Restrict by Store and Currency'),
        );
    }

}
