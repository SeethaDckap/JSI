<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Certificates\Edit\Tab;


/**
 * Certificates generate csr details block
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Csr extends \Magento\Backend\Block\Widget\Form\Generic implements
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

        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('Generate CSR')));

        $fieldset->addField('country', 'text', array(
            'label' => __('Country'),
            'required' => false,
            'name' => 'country',
            'note' => __('Please enter the 2 character ISO country code<br>eg. GB'),
            'class' => 'validate-length maximum-length-2 minimum-length-2'
        ));

        $fieldset->addField('state', 'text', array(
            'label' => __('State'),
            'required' => false,
            'name' => 'state',
            'note' => __('eg. Yorkshire'),
        ));

        $fieldset->addField('city', 'text', array(
            'label' => __('City'),
            'required' => false,
            'name' => 'city',
            'note' => __('eg. York'),
        ));

        $fieldset->addField('organisation', 'text', array(
            'label' => __('Organisation'),
            'required' => false,
            'name' => 'organisation',
            'note' => __('eg. Epicor'),
        ));

        $fieldset->addField('department', 'text', array(
            'label' => __('Department'),
            'required' => false,
            'name' => 'department',
            'note' => __('eg. eCommerce'),
        ));

        $fieldset->addField('domain_name', 'text', array(
            'label' => __('Domain Name'),
            'required' => false,
            'name' => 'domain_name',
            'note' => __('eg. www.domain.com'),
        ));

        $fieldset->addField('email', 'text', array(
            'label' => __('Email'),
            'required' => false,
            'class' => 'validate-email',
            'name' => 'email',
            'note' => __('eg. webmaster@example.com'),
        ));

        $site = $this->_coreRegistry->registry('current_certificate');
        $form->setValues($site->getData());

        return parent::_prepareForm();
    }

    public function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);

        $html .= '<p>' . __('Generating a new CSR will create / overwrite the Private Key and Certificate Signing Request.') . '</p>';

        return $html;
    }

    public function getTabLabel()
    {
        return __('Generate CSR');
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
