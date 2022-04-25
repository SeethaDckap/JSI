<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock;

class Switcher extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Config\Model\Config\Source\LocaleFactory
     */
    protected $configConfigSourceMappingsFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Epicor\Common\Model\Config\Source\MappingsFactory $configConfigSourceMappingsFactory, 
        \Magento\Framework\Data\FormFactory $formFactory, 
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->configConfigSourceMappingsFactory = $configConfigSourceMappingsFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareForm()
    {
        $controllerName = $this->getRequest()->getControllerName();
        $selectedType = explode("_",$controllerName);
        $data = array('mapping_type'=>$selectedType[2]);

        $form = $this->formFactory->create(['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data']
        ]);
        

        $form->setUseContainer(false);

        $this->setForm($form);
        $fieldset = $form->addFieldset('some_form', array());

       $manageMappingType= $fieldset->addField('mapping_type', 'select', array(
            'label' => __('Change Mapping Type'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'language_codes',
            'values' => $this->configConfigSourceMappingsFactory->create()->toOptionArray(),
        ));
//        $manageMappingType->setAfterElementHtml("<script>
//             require(['jquery'], function($){
//                $('#mapping_type').on('change',function(){
//                    if($(this).val() == 1){
//                        alert('hey');
//                    }else{
//                    alert('hey');
//                         alert('hey');
//                    }
//                });
//           });
//        </script>");
        
        //Start
                    /*
             * Add Ajax to the Country select box html output
             */
            $manageMappingType->setAfterElementHtml("<script>
             require(['jquery'], function($){
                $('#mapping_type').on('change',function(){
                var ajaxurl = '" . $this->getUrl('adminhtml/epicorcommon_mapping/change') ."';
                var val =  $('#mapping_type').val();
                $.ajax({
                    url:ajaxurl,
                    type:'POST',
                    showLoader: true,
                    dataType:'json',
                    data: {value:val},                                      
                    success:function(response){
                        if(response.success){
                            //do nothing
                        }
                    }
                });
            });
            });
            //]]>
        </script>");
        //End
        $form->setValues($data);

        return parent::_prepareForm();
    }

}
