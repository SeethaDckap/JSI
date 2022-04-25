<?php


namespace Epicor\Dealerconnect\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;


class EccPacAttributes extends \Magento\Framework\Model\AbstractModel
{
    


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes');
    }



}
