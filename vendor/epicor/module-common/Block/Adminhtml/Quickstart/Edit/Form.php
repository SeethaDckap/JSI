<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Quickstart\Edit;


class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_completed = true;

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            array(
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'autocomplete' => 'off',
                ]
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
