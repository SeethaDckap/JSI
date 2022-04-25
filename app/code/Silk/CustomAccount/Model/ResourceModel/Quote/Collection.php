<?php

namespace Silk\CustomAccount\Model\ResourceModel\Quote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Silk\CustomAccount\Model\Quote', 'Silk\CustomAccount\Model\ResourceModel\Quote');
    }

}
?>