<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Gqrsubmittocustomer
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'Y', 'label' => 'Y'),
            array('value' => 'N', 'label' => 'N'),
        );
    }

}
