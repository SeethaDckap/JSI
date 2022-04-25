<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Customer\Edit\Contracts;


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
        $this->_account = $this->registry->registry('current_customer');
        //     $this->_account->setData('contract_shipto_default', $this->_account->getData('ecc_contract_shipto_default'));
        $this->_type = 'customer';
        return parent::_prepareForm();
    }

}
