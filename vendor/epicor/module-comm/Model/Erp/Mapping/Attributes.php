<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/**
 * allow access to epicor_comm/erp_mapping_attributes
 * */
class Attributes extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Attributes');
    }

}
