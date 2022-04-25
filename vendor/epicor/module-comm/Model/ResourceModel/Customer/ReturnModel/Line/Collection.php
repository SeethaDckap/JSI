<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line;


/**
 * Customer Return Line collection model
 * 
 * @category   Epicor
 * @package    Epicor_License
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Location\Link\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Customer\ReturnModel\Line', 'Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line');
    }

}
