<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Erpaccount
 *
 * @author David.Wylie
 */
class Allowedaccounts extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_customer_erpaccount_allowedaccounts';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Erp Accounts');

        $this->addButton(20, array('label' => 'Cancel', 'onclick' => "accountSelector.closepopup()"), 1);

        parent::_construct();
        $this->removeButton('add');
    }

    /**
     * @return $this
     * @fixme set child 'grid' block will be failed if specil the block name.
     */
    protected function _prepareLayout()
    {
        if (false === $this->getChildBlock('grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    str_replace(
                        '_',
                        '\\',
                        $this->_blockGroup
                    ) . '\\Block\\' . str_replace(
                        ' ',
                        '\\',
                        ucwords(str_replace('_', ' ', $this->_controller))
                    ) . '\\Grid'
                )->setSaveParametersInSession(
                    true
                )
            );
        }

        $this->toolbar->pushButtons($this, $this->buttonList);

        return $this;
    }
}
