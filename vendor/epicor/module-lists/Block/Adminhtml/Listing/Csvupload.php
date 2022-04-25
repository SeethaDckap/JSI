<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing;


class Csvupload extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_listing';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Lists CSV Upload');
        $this->_mode = 'csvupload';

        parent::__construct(
            $context,
            $data
        );

        //$this->_removeButton('back');
        $this->updateButton('save', 'label', __('Upload'));
    }

}
