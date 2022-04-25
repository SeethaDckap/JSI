<?php

namespace Silk\CustomForms\Model\ResourceModel\ContactSupport;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Silk\CustomForms\Model\ContactSupport', 'Silk\CustomForms\Model\ResourceModel\ContactSupport');
    }
}
?>