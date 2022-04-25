<?php

namespace Silk\CustomForms\Model\ResourceModel\DealerRequest;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Silk\CustomForms\Model\DealerRequest', 'Silk\CustomForms\Model\ResourceModel\DealerRequest');
    }
}
?>