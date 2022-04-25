<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Block\Adminhtml\Customer\Erpaccount;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Grid
 *
 * @author David.Wylie
 */
class Grid extends \Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Grid
{

    protected function _prepareColumns()
    {

        parent::_prepareColumns();

        $this->addColumnAfter('pre_reg_password', array(
            'header' => __('Pre reg Password'),
            'index' => 'pre_reg_password',
            'width' => '200px',
            'filter' => false,
            'sortable' => false,
            ), 'onstop');

        return $this;
    }

}
