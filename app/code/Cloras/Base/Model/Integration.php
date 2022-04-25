<?php

namespace Cloras\Base\Model;

use Magento\Framework\Model\AbstractModel;

class Integration extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Cloras\Base\Model\ResourceModel\Integration::class);
    }
}
