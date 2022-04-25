<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Certificates\Edit\Tab;


/**
 * Certificates details edit block
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Details  extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{



    /**
     * Initialize form
     *
     */
    public function initForm()
    {
        $form = $this->_formFactory->create();

        $this->setForm($form);

        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('Certificate Details')));
        $fieldset->addField('name', 'text', array(
            'label' => __('Name'),
            'tabindex' => 1,
            'required' => true,
            'name' => 'name'
        ));

        $fieldset->addField('request', 'textarea', array(
            'label' => __('Certificate Signing Request'),
            'required' => false,
            'name' => 'request'
        ));

        $fieldset->addField('private_key', 'textarea', array(
            'label' => __('Private Key'),
            'required' => false,
            'name' => 'private_key'
        ));

        $fieldset->addField('certificate', 'textarea', array(
            'label' => __('Certificate'),
            'required' => false,
            'name' => 'certificate'
        ));

        $fieldset->addField('c_a_certificate', 'textarea', array(
            'label' => __('CA Certificate'),
            'required' => false,
            'name' => 'c_a_certificate'
        ));

        $site = $this->_coreRegistry->registry('current_certificate');
        $form->setValues($site->getData());

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Certificate Details');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}
