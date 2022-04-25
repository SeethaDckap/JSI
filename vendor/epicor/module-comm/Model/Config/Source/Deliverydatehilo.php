<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Deliverydatehilo
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'highest', 'label' => __('Highest')),
            array('value' => 'lowest', 'label' => __('Lowest')),
        );
    }

}
