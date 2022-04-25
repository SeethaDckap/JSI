<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment;


/**
 * Customer Return Attachment collection model
 * 
 * @category   Epicor
 * @package    Epicor_License
 * @author     Epicor Websales Team
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Customer\ReturnModel\Attachment', 'Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment');
    }

}
