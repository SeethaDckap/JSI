<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Ewa;


class Display
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'base_description', 'label' => 'Base Product Description'),
            array('value' => 'ewa_title', 'label' => 'Configured Title'),
            array('value' => 'ewa_sku', 'label' => 'Configured SKU'),
            array('value' => 'ewa_short_description', 'label' => 'Configured Short Description'),
            array('value' => 'ewa_description', 'label' => 'Configured Description'),
        );
    }

}
