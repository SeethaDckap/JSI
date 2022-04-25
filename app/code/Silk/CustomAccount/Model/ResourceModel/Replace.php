<?php
namespace Silk\CustomAccount\Model\ResourceModel;

class Replace extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('custom_replace', 'id');
    }
}
?>