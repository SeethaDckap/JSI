<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml;


class Quickstart extends \Magento\Backend\Block\Widget\Form\Container
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
        $this->_objectId = 'id';
        $this->_blockGroup = 'epicor_common';
        $this->_controller = 'adminhtml_quickstart';
        $this->_mode = 'edit';
        $this->setUseAjax(true);
//          $this->setFormAction($this->getUrl('*/*/new'));     
        $this->buttonList->remove('add');
        $this->buttonList->remove('back');
        $this->buttonList->remove('delete');
        $this->buttonList->add('refresh', array(
            'label' => __('Reload Page'),
            'onclick' => 'setLocation(window.location.href)',
        ));
//        $this->_removeButton('save');        
//    
//     
    }

    public function getHeaderText()
    {
        return __('Quick Start');
    }

}
