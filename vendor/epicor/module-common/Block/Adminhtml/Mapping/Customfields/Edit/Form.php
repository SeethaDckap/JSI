<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Customfields\Edit;


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


    protected $globalConfig;



    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->globalConfig = $globalConfig;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        $data = array();
        $attribute_data = array();
        if ($this->registry->registry('customfields_mapping_data')) {
            $data = $this->registry->registry('customfields_mapping_data');
        }

        $formParam = array('id' => $this->getRequest()->getParam('id'));
        $preselected = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');
        if($preselected) {
            $formParam = array('id' => $this->getRequest()->getParam('id'),'preselectedFields'=>$preselected,'msec'=>$msec);
        }


        $form = $this->formFactory->create(['data' => [
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', $formParam),
            'method' => 'post',
            'enctype' => 'multipart/form-data']
        ]);


        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));

        $fieldset->addField('message', 'select', array(
            'label' => __('Ecc Message'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'message',
            'values' => $this->getRequestMessages(),
        ))->setAfterElementHtml("<script>
             require(['jquery'], function($){
                $(document).ready(function(){
                    if(window.location.href.indexOf(\"edit\") == -1) {
                        var ajaxurl = '" . $this->getUrl('adminhtml/epicorcommon_mapping/changemapping', ['form_key' => $this->getFormKey()]) ."';
                        var val =  $('#message').val();
                        var selectedOption = '$msec';
                        $.ajax({
                            url:ajaxurl,
                            type:'POST',
                            showLoader: true,
                            dataType:'json',
                            data: {value:val},                                      
                            success:function(response){
                                
                               $('#message_section').empty();
                               $.each(response, function(i, keyvals) {
                                    $('#message_section').append($('<option>').text(keyvals.value).attr('value', keyvals.key));
                                    if(keyvals.key ==selectedOption) {
                                        $('#message_section').val(selectedOption);
                                    }
                                    
                                });
                               
                               
                            }
                        });    
                    }
                });
                $('#message').on('change',function(){
                var ajaxurl = '" . $this->getUrl('adminhtml/epicorcommon_mapping/changemapping') ."';
                var val =  $('#message').val();
                $.ajax({
                    url:ajaxurl,
                    type:'POST',
                    showLoader: true,
                    dataType:'json',
                    data: {value:val},                                      
                    success:function(response){
                       $('#message_section').empty();
                       $.each(response, function(i, keyvals) {
                            $('#message_section').append($('<option>').text(keyvals.value).attr('value', keyvals.key));
                        });
                    }
                });
            });
            });
            //]]>
        </script>");

        $fieldset->addField('message_section', 'select', array(
            'label' => __('Message Section'),
            'name' => 'message_section',
            'class' => 'required-entry',
            'required' => true,
            'values' => $this->getMessageSection()
        ));

        $fieldset->addField('custom_fields', 'text', array(
            'label' => __('Custom Fields'),
            'name' => 'custom_fields',
            'class' => 'required-entry',
            'required' => true,
        ))->setAfterElementHtml('
        <div class="field-tooltip toggle">
            <span class="field-tooltip-action action-help" tabindex="0" hidden="hidden"></span>
            <div class="field-tooltip-content">
                 <span>- For a User defined field, Please Enter : userDefined>FieldName
                 <br>
                    - For Custom attribute, Please Enter : AttributeName
                    <br>
                    - For Payload, Please Enter: Payload>PayloadData
            </span>
            </div>
        </div>
    ');


        $data = $this->includeStoreIdElement($data);

        if ($preselected) {
            $data->setData('message', $preselected);
            $data->setData('message_section', $msec);
        }

        $form->setValues($data);

        return parent::_prepareForm();
    }

    public function getStatusName()
    {

        $statusName = array('yes' => 'Active', 'no' => 'Inactive');
        return $statusName;
    }

    public function getRequestMessages() {
        $request = (array) $this->globalConfig->get('mapping_xml_request/messages');
        //M1 > M2 Translation End
        $_upload = array();
        foreach ($request as $key => $request) {
            $_upload[strtoupper($key)] = $request['title'];
        }
        return $_upload;
    }

    public function getMessageSection()
    {
        $data = array();
        $attribute_data = array();
        $preselected = '';
        if ($this->registry->registry('customfields_mapping_data')) {
            $data = $this->registry->registry('customfields_mapping_data');
            $preselected = $data->getData('message');
        }
        $request = (array)$this->globalConfig->get('mapping_xml_request/messages');
        $_upload = array();
        if ($preselected) {
            foreach ($request as $key => $request) {
                unset($request['title']);
                if ($preselected == strtoupper($key)) {
                    $arrayKeys = array_keys($request);
                    if(in_array('grid_config',$arrayKeys)) {
                        $_upload['grid_config'] = 'Grid Config';
                    }
                    if(in_array('address_section',$arrayKeys)) {
                        $_upload['address_section'] = 'Address Section';
                    }
                    if(in_array('information_section',$arrayKeys)) {
                        $_upload['information_section'] = 'Information Section';
                    }
                    if(in_array('lineinformation_section',$arrayKeys)) {
                        $_upload['lineinformation_section'] = 'Line Information Section';
                    }
                    if(in_array('replacement_grid_config',$arrayKeys)) {
                        $_upload['replacement_grid_config'] = 'Replacements Grid Setup';
                    }
                    if(in_array('newpogrid_config',$arrayKeys)) {
                        $_upload['newpogrid_config'] = 'New PO Grid Setup';
                    }
                }
            }

        }

        return $_upload;
    }

}