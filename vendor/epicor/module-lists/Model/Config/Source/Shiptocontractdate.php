<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


class Shiptocontractdate
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'all', 'label' => 'All'),
            array('value' => 'newest', 'label' => 'Newest Activation Date'),
            array('value' => 'oldest', 'label' => 'Oldest Activation Date'),
        );
    }

}
