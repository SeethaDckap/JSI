<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping;


/*
 * Allow  access to table epicor_comm/erp_mapping_attributes
 */

class Attributes extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Attributes
{

    
    public function getFields()
    {
        $fields = $this->getConnection()->describeTable($this->getMainTable());
        return $fields;
    }
    
}
