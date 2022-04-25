<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model\ResourceModel\Transaction;
/**
 * Elements token collection model
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */
class Collection  extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    public function _construct()
    {

        $this->_init('Epicor\Elements\Model\Transaction','Epicor\Elements\Model\ResourceModel\Transaction');
    }

}
