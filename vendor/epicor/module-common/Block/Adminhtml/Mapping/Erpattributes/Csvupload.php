<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Erpattributes;


/*
 * Setup CSVupload form container for table epicor_comm/erp_mapping_attributes
 */

class Csvupload extends \Magento\Backend\Block\Widget\Form\Container
{
    /*
     * Set up save button and grid for csvupload form 
     */

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_mapping_erpattributes';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Attributes CSV Upload');
        $this->_mode = 'csvupload';

        parent::__construct(
            $context,
            $data
        );

       // $this->removeButton('back');
        $this->updateButton('save', 'label', __('Upload'));
    }

}
