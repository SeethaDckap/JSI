<?php


namespace Epicor\Dealerconnect\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;


class EccPac extends \Magento\Framework\Model\AbstractModel
{
    
    
    const ECC_PAC_ENTITY_ID = 'entity_id';
 

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\EccPac');
    }




}
