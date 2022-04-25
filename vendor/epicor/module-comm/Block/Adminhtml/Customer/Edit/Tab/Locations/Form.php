<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Edit\Tab\Locations;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author Paul.Ketelle
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $customer = $this->registry->registry('current_customer');
        /* @var $customer Epicor_Comm_Model_Customer */

        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('locations_form', array('legend' => __('Location')));

        $linkType = $customer->getEccLocationLinkType();

        $fieldset->addField('id', 'hidden', array(
            'name' => 'id',
        ));

        $fieldset->addField('locations_source', 'select', array(
            'label' => __('Location Restrictions Source'),
            'required' => false,
            'name' => 'locations_source',
            'values' => $this->_getOptions(),
        ));

        $data = array(
            'locations_source' => (is_null($linkType)) ? 'erp' : 'customer'
        );

        $form->setValues($data);


        $this->setForm($form);
    }

    /**
     * Gets an array of options for the dropdown
     * 
     * @return array
     */
    private function _getOptions()
    {
        $options = array();

        $options[] = array(
            'label' => __('Use ERP Account Specific Locations'),
            'value' => 'erp'
        );

        $options[] = array(
            'label' => __('Use Customer Specific Locations'),
            'value' => 'customer'
        );


        return $options;
    }

}
