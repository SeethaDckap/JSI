<?php


namespace Epicor\Dealerconnect\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;


class Warranty extends \Magento\Framework\Model\AbstractModel
{
    
    
    const ECC_PAC_ENTITY_ID = 'id';
 

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\Warranty');
    }




}
