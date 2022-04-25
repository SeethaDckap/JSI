<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Block\Adminhtml\Form\Element;

/**
 * Description of CategoryListErpimages
 *
 * @author ashwani.arya
 */
class CategoryListErpimages extends \Magento\Backend\Block\Widget\Form\Generic {
     
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
      //  $this->_coreRegistry = $registry;
     //   $this->_formFactory = $formFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
     
    //\Magento\Framework\Registry $coreRegistry,
    
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        //$product = $this->_coreRegistry->registry('product');
        
        $category =  $this->_coreRegistry->registry('category'); 
        $categoryId = (int)$category->getId();
        
        $form->setDataObject($category);
      
        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('')));
        
        $fieldset->addType('erpimage_grid', 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpimages\Category');
 
        $fieldset->addField('ecc_erp_images','erpimage_grid',[
           'label' => __('Erp Images'),
            'name' => 'ecc_erp_images',
            'class' =>'action- scalable action-secondary',
            'data-form-part' => 'category_form'
        ]);
        
           $params = array('category'=>$categoryId);
           $ajax_url=  $this->getUrl('adminhtml/epicorcomm_message_ajax/synccategoryimages',$params);
        
        $fieldset->addField('syncftpimage', 'button', array(
            'value' => __('Sync Images'),
            'onclick' => "catynchimage.syncFtpImages('".$ajax_url."')"
        ));
        
          $values = $category->getData();
        // Set default attribute values for new product or on attribute set change
        $form->addValues($values);
       
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function isHidden()
    {
       return false;
    }
}

