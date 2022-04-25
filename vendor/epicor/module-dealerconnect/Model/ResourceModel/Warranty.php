<?php


namespace Epicor\Dealerconnect\Model\ResourceModel;

class Warranty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('ecc_erp_mapping_warranty_config', 'id');
    }
}
