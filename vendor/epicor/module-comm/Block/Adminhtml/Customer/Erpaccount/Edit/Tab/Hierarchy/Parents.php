<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy;


class Parents extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_customer_erpaccount_edit_tab_hierarchy_parents';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Parents');
        parent::__construct(
            $context,
            $data
        );

        $this->removeButton('add');
    }

}
