<?php
namespace Silk\CustomForms\Model\ResourceModel;

class DealerRequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customforms_dealer_request', 'entity_id');
    }
}
?>