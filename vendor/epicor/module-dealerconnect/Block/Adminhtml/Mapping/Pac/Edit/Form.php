<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping\Pac\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
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
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {   
        $data = array();
        $attribute_data = array();
        if ($this->registry->registry('pac_attribute_data')) {
            $data = $this->registry->registry('pac_attribute_data');
        }
        
        $form = $this->formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'get',
                'enctype' => 'multipart/form-data']
            ]
        );
       
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('pac_mapping_form', array(
            'legend' => __('Mapping Information')
        ));
        
        $fieldset->addField('class_attribute_id', 'text', array(
                'label' => __('Attribute Class Id'),
                'name' => 'class_attribute_id',
                'readonly' => true,
            ));
        
        $fieldset->addField('attribute_id', 'text', array(
                'label' => __('Attribute Id'),
                'name' => 'attribute_id',
                'readonly' => true,
            ));
        
        $fieldset->addField('description', 'text', array(
                'label' => __('Description'),
                'name' => 'description',
                'readonly' => true,
            ));
        
        $fieldset->addField('label', 'text', array(
                'label' => __('Label'),
                'name' => 'label',
                'readonly' => true,
            ));
        
        $fieldset->addField('datatype', 'text', array(
                'label' => __('Datatype'),
                'name' => 'datatype',
                'readonly' => true,
            ));
        
        if(isset($data['attribute_options']) && $data['attribute_options']!=null){
            $fieldset->addField('attribute_options', 'textarea', array(
                'label' => __('List Values'),
                'name' => 'attribute_options',
                'readonly' => true,
            ));
        }
        
        $form->setValues($data);
        
        return parent::_prepareForm();
    }

}
