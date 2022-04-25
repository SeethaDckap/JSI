<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model\Config\Source;


class Cvvresults
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'M', 'label' => "M - Matched"),
            array('value' => 'N', 'label' => "N - No Matched"),
            array('value' => 'P', 'label' => "P - Not Processed"),
            array('value' => 'S', 'label' => "S - Not Present"),
            array('value' => 'U', 'label' => "U - Unavailable"),
            array('value' => 'Y', 'label' => "Y - Match"),
        );
    }

}
