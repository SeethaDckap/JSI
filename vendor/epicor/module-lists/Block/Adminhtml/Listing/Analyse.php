<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing;


class Analyse extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_listing';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Analyse Lists');
        $this->_mode = 'analyse';

        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('back');

        $this->updateButton('save', 'label', __('Analyse'));

        $this->updateButton('reset', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/index') . '\')');
    }

}
