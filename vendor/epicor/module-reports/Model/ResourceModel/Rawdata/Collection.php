<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Model\ResourceModel\Rawdata;


/**
 * Created by PhpStorm.
 * User: lguerra
 * Date: 9/5/14
 * Time: 11:32 AM
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Reports\Raw\Data\Collection
{


    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('Epicor\Reports\Model\Rawdata','Epicor\Reports\Model\ResourceModel\Rawdata');
    }

}
