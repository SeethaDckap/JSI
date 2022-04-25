<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Skuoptions
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ""),
            array('value' => 'cussku', 'label' => "Customer Sku"),
            array('value' => 'altsku', 'label' => "Alternative Sku"),
            array('value' => 'prodsku', 'label' => "Product Sku"),
        );
    }

}
