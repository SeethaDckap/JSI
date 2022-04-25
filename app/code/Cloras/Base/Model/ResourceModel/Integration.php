<?php

namespace Cloras\Base\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Integration extends AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init($this->getTable('cloras_integration'), 'id');
    }//end _construct()
}//end class
