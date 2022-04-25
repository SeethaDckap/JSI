<?php

namespace Cloras\Base\Model;

use Magento\Framework\Model\AbstractModel;

class Orders extends AbstractModel
{
    
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Cloras\Base\Model\ResourceModel\Orders::class);
    }
}
