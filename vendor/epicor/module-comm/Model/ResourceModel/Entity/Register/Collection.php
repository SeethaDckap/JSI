<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Entity\Register;


/**
 * Entity Register collection model
 * 
 * @category   Epicor
 * @package    Epicor_License
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Entity\Register\Collection
{
    protected $_idFieldName = 'row_id';
     
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Entity\Register', 'Epicor\Comm\Model\ResourceModel\Entity\Register');
    }

}
