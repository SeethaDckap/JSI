<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Erpwebboth
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'web', 'label' => "Web Only"),
            array('value' => 'erp', 'label' => "ERP Only"),
            array('value' => 'both', 'label' => "Both"),
        );
    }

}
