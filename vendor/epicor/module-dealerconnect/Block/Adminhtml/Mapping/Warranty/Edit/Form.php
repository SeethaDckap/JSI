<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping\Warranty\Edit;


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
        if ($this->registry->registry('warraty_mapping_data')) {
            $data = $this->registry->registry('warraty_mapping_data');
        }
        
        $form = $this->formFactory->create(['data' => [
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data']
        ]);
        
        
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));
        
        $fieldset->addField('code', 'text', array(
                'label' => __('Warranty Code'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'code'
            ));
        
        $fieldset->addField('description', 'text', array(
                'label' => __('Description'),
                'name' => 'description',
                'class' => 'required-entry',
                'required' => true,            
            ));
        
        $fieldset->addField('status', 'select', array(
            'label' => __('Status'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'status',
            'values' => $this->getStatusName()
        ));
        
        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);
        
        return parent::_prepareForm();
    }
    
    public function getStatusName()
    {

        $statusName = array('yes' => 'Active', 'no' => 'Inactive');
        return $statusName;
    }    

}
