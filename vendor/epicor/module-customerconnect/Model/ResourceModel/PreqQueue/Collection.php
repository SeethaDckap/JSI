<?php


namespace Epicor\Customerconnect\Model\ResourceModel\PreqQueue;

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
            'Epicor\Customerconnect\Model\PreqQueue',
            'Epicor\Customerconnect\Model\ResourceModel\PreqQueue'
        );
    }
}
