<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Customer\Supplieraccount\Attribute;


/**
 * 
 * ERP Account grid for erp account selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Attribute\Grid
{

    protected function addAccountTypeFilter(&$collection, $fieldName)
    {
        $collection->addFieldToFilter('account_type', 'Supplier');
    }

}
