<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Linecomment
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'cart', 'label' => 'My Cart Page'),
            array('value' => 'review', 'label' => 'Checkout Review'),
        );
    }

}
