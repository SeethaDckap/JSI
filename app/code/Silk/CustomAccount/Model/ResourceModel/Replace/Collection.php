<?php

namespace Silk\CustomAccount\Model\ResourceModel\Replace;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Silk\CustomAccount\Model\Replace', 'Silk\CustomAccount\Model\ResourceModel\Replace');
    }

}
?>