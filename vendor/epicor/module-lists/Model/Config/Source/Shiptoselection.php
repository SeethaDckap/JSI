<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


class Shiptoselection
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'all', 'label' => 'All'),
            array('value' => 'default', 'label' => 'Shoppers Default Ship To'),
            array('value' => 'specific', 'label' => 'Specific Ship To'),
        );
    }

}
