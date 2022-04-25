<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Model\Config\Source;


class PreRegOptions
{

    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => "Yes"),
            array('value' => 0, 'label' => "No"),
            array('value' => 2, 'label' => "Yes (With Admin Email)"),
        );
    }

}
