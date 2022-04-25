<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping;


class Reasoncodetypes extends \Magento\Framework\Model\AbstractModel
{

    var $types = array(
        array('value' => '', 'label' => " "),
        array('value' => 'B', 'label' => "B2B sites only"),
        array('value' => 'C', 'label' => "B2C sites only")
    );

//    public function _construct()
//    {
//        parent::_construct();
//        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Reasoncodetypes');
//    }

    public function toOptionArray()
    {
        return $this->types;
    }

    public function toArray(array $attributes = array())
    {
        $array = array();
        foreach ($this->types as $type) {
            $array[$type['value']] = $type['label'];
        }
        return $array;
    }

}
