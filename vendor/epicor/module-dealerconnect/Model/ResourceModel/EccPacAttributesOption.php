<?php


namespace Epicor\Dealerconnect\Model\ResourceModel;

class EccPacAttributesOption extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_pac_attributes_option', 'entity_id');
    }
}
