<?php
namespace Silk\CustomForms\Model;

class ContactSupport extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\CustomForms\Model\ResourceModel\ContactSupport');
    }
}
?>