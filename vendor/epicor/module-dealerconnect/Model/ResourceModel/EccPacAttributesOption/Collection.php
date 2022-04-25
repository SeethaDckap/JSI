<?php


namespace Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Epicor\Dealerconnect\Model\EccPacAttributesOption',
            'Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption'
        );
    }
}
