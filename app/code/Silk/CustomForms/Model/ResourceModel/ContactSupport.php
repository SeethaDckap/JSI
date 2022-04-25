<?php
namespace Silk\CustomForms\Model\ResourceModel;

class ContactSupport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customforms_contact_support', 'entity_id');
    }
}
?>