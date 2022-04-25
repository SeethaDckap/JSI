<?php
namespace Silk\CustomAccount\Model;

class Quote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\CustomAccount\Model\ResourceModel\Quote');
    }
}
?>