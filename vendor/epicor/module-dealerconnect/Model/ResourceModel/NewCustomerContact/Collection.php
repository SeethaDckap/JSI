<?php


namespace Epicor\Dealerconnect\Model\ResourceModel\NewCustomerContact;

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
            'Epicor\Dealerconnect\Model\NewCustomerContact',
            'Epicor\Dealerconnect\Model\ResourceModel\NewCustomerContact'
        );
    }
}
