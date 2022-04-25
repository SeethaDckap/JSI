<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Erp\Mapping;


class Claimstatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function toOptionArray()
    {
        $arr = array('OPEN' => 'OPEN', 'CLOSED' => 'CLOSED');
        return $arr;
    }

    public function toGridArray()
    {
        $arr = array('OPEN' => 'OPEN', 'CLOSED' => 'CLOSED');
        return $arr;
    }

}
