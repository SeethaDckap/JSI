<?php


namespace Epicor\Dealerconnect\Model\ResourceModel\Warranty;

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
            'Epicor\Dealerconnect\Model\Warranty',
            'Epicor\Dealerconnect\Model\ResourceModel\Warranty'
        );
    }
}
