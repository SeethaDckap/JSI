<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\ResourceModel\Customer\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Customer\Model\ResourceModel\Grid\Collection
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;
    
    protected function _initSelect()
    {
        parent::_initSelect();
//$collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=ecc_erpaccount_id', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code'), null, 'left');
        $this->getSelect()->joinLeft(
                ['secondTable' => $this->getTable('ecc_erp_account')],
                'main_table.ecc_erpaccount_id = secondTable.entity_id',
                ['company as customer_company','short_code']
            );
    }
      
     /**
     * Modify for website_id code same in location type and location list
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'customer_company') {
            $field = 'secondTable.company';
        }

        return parent::addFieldToFilter($field, $condition);
    }
    
}
