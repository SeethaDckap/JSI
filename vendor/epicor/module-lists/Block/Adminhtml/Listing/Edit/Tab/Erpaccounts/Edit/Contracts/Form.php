<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Erpaccounts\Edit\Contracts;


/**
 * List Contracts ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Form extends \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Contracts\Form
{

    protected function _prepareForm()
    {
        $this->_account = $this->registry->registry('customer_erp_account');
        $this->_type = 'erpaccount';
        return parent::_prepareForm();
    }

}
