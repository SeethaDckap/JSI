<?php


namespace Epicor\Customerconnect\Model\ResourceModel;

class PreqQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_preq_queue', 'entity_id');
    }
}
