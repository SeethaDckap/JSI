<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount;


/**
 * New Customer ErpAccount
 *
 * @author Gareth.James
 */
class Create extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );

        $this->_controller = 'adminhtml_customer_erpaccount';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_mode = 'create';

        $this->updateButton('save', 'label', __('Create ERP Account'));
    }

    public function getHeaderText()
    {
        return __('New ERP Account');
    }

}
