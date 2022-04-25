<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Form\Element;


class Salesrepaccount extends \Epicor\Common\Block\Adminhtml\Form\Element\Erpaccounttype
{

    protected function _construct()
    {
        $this->_restrictToType = 'salesrep';
        parent::_construct();
    }

}
