<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Mapping;


/*
 *   Grid Container for epicor_comm/erp_mapping_attributes   
 */

class Erpattributes extends \Magento\Backend\Block\Widget\Grid\Container
{
    /*
     * Construct for epicor_comm/erp_mapping_attributes
     */

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_mapping_erpattributes';
        $this->_blockGroup = 'epicor_comm';
        $this->_headerText = __('ERP Attribute Types');
        $this->_addButtonLabel = __('Add Attribute Mapping');
        parent::__construct(
            $context,
            $data
        );
        $this->_addButton('addbycsv', array(
            'label' => __('Add Attribute Mapping By CSV'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/addbycsv') . '\')',
            'class' => 'add',
        ));
    }

}
