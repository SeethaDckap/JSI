<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/**
 * @method getErpCode()
 * @method getMagentoCode() 
 */
class Yesnonull extends \Magento\Framework\Model\AbstractModel
{

    public function _construct()
    {
        parent::_construct();

    }

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ""),
            array('value' => 'N', 'label' => "No"),
            array('value' => 'Y', 'label' => "Yes"),
        );
    }

}
