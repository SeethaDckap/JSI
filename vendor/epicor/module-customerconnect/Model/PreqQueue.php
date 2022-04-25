<?php


namespace Epicor\Customerconnect\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;


class PreqQueue extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\PreqQueue');
    }




}
