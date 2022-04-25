<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Attributes;


/*
 * Allow collection access to epicor_comm/erp_mapping_attributes
 */

class Collection  extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Attributes\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Attributes', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Attributes');
    }

}
