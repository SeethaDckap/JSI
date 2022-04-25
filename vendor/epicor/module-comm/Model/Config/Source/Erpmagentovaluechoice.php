<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Erpmagentovaluechoice
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'magento', 'label' => "Magento"),
            array('value' => 'erp', 'label' => "Erp"),
            array('value' => 'higher', 'label' => "Higher Value"),
            array('value' => 'lower', 'label' => "Lower Value"),
        );
    }

}
