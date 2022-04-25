<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/**
 * @method getErpCode()
 * @method getMagentoCode() 
 */
class Payment extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment');
    }

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ""),
//                        array('value' => 'C - Collected', 'label' => "Collected"),
//                        array('value' => 'A - Authorised', 'label' => "Authorised"),
//                        array('value' => 'D - Authorised/Capture on Ship', 'label' => "Authorised and web will capture on Ship"),
//                        array('value' => 'N - Token only', 'label' => "Token only"),
            array('value' => 'C', 'label' => "Collected"),
            array('value' => 'A', 'label' => "Authorised"),
            array('value' => 'D', 'label' => "Authorised and web will capture on Ship"),
            array('value' => 'N', 'label' => "Token only"),
        );
    }

}
