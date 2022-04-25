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
 * Description of ListErpImages
 *
 * @author 
 */

class ListErpImages extends \Magento\Backend\Block\Widget\Form\Generic {
     
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $product = $this->_coreRegistry->registry('product');
        $form->setDataObject($product);
      
        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('')));
       
        $params = array('product'=>$product->getId());
        $ajax_url=  $this->getUrl('adminhtml/epicorcomm_message_ajax/syncftpimages',$params);
        
        $fieldset->addField('syncftpimage', 'button', array(
            'value' => __('Sync Images'),
            'onclick' => "msynchimage.syncFtpImages('".$ajax_url."')"
        ));
        
        $fieldset->addType('erpimage_grid', 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpimages');
 
        $fieldset->addField('ecc_erp_images','erpimage_grid',[
           'label' => __('Erp Images'),
            'name' => 'product[ecc_erp_images]',
            'class' =>'action- scalable action-secondary',
            'data-form-part' => 'erp_images_form'
        ]);
        
         $fromDate = $fieldset->addField('ecc_erp_images_last_processed', 'date', array( 
                'label' => __('Last ERP Image process time for this product'),
                //'class' => 'validate-date',
               'required' => false,
               'time' =>false,
                'name' =>'product[ecc_erp_images_last_processed]',
                'data-form-part' => 'product_form',
                'date_format' => 'MM/dd/yyyy',
                )
            );
         
         $fieldset->addField('ecc_erp_images_processed','select',
                [
                    'label' => __('Images synced from ERP'),
                    'title' => __('Yes/No'),
                   'name' => 'product[ecc_erp_images_processed]',
                    'required' => false,
                    'class' => 'admin__actions-switch-text',
                    'data-form-part' => 'product_form',
                    'options' => ['1' => __('Yes'), '0' => __('No')]
                ]
            );
        
        $values = $product->getData();
        $form->addValues($values);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function isHidden()
    {
       return false;
    }
}
