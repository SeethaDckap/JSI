<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Certificates\Edit;


/**
 * Certificates edit form block
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{


    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post'
            ]
        ]);

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
