<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Cardtype;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Cardtype\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Cardtype', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Cardtype');
    }

}
