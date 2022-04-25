<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;


class Customer extends \Epicor\Common\Block\Adminhtml\Form\Element\Erpaccounttype
{

    protected function _construct()
    {
        parent::_construct();

        $this->_restrictToType = 'mage_customer';

        $this->_accountType = 'mage_customer';

        $this->_defaultLabel = 'No Customer Selected';

        $this->_types = array(
            'mage_customer' => array(
                'label' => 'Customer',
                'field' => 'id',
                'model' => 'customer/customer',
                'url' => 'adminhtml/epicorcomm_customer/listcustomers/',
                'priority' => 10
            )
        );
    }

}
