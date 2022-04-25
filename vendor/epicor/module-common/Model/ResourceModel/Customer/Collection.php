<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\ResourceModel\Customer;


/**
 * Model Resource Class for Contracts
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    public function _getItemId(\Magento\Framework\DataObject $item)
    {
        if($this->getFlag('allow_duplicate') && isset($this->_items[$item->getEntityId()])){
            return $item->getEntityId().uniqid();
        }
    }
}
