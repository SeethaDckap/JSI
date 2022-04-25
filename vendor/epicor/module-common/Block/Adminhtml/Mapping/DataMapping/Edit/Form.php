<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\DataMapping\Edit;


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

    protected $eccMappingConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\Common\Model\EccMappingConfig $eccMappingConfig,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->eccMappingConfig = $eccMappingConfig;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        $data = array();
        if ($this->registry->registry('ecc_datamapping_data')) {
            $data = $this->registry->registry('ecc_datamapping_data');
        }

        $formParam = array('id' => $this->getRequest()->getParam('id'));
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
        ));
        $fieldset->addField('orignal_tag', 'text', array(
            'label' => __('Orignal Tag'),
            'name' => 'orignal_tag',
            'class' => 'required-entry no-whitespace',
            'required' => true
        ))->setAfterElementHtml('
        <div class="field-tooltip toggle">
            <span class="field-tooltip-action action-help" tabindex="0" hidden="hidden"></span>
            <div class="field-tooltip-content">
                 <span>- For an Original Tag, Please Enter(eg. STK) : product>parent>productCode
                 <br> - For an Original Tag Attribute, Please Enter(eg. STK) : product>parent>images>image>filename+type                       
            </span>
            </div>
        </div>
    ');

        $fieldset->addField('mapped_tag', 'text', array(
            'label' => __('Mapped Tag'),
            'name' => 'mapped_tag',
            'class' => 'required-entry',
            'required' => true,
        ))->setAfterElementHtml('
        <div class="field-tooltip toggle">
            <span class="field-tooltip-action action-help" tabindex="0" hidden="hidden"></span>
            <div class="field-tooltip-content">
                 <span>- For a User Defined Field, Please Enter(eg. STK) : product>userDefined>fieldname
                 <br> - For Constant Value, Please Enter(eg. STK) : "LTS"
            </span>
            </div>
        </div>
    ');

        $data = $this->includeStoreIdElement($data);
        $form->setValues($data);

        return parent::_prepareForm();
    }

    public function getRequestMessages() {

        $request = $this->eccMappingConfig->getAttributeNames(null);
        $_upload = array();
        foreach ($request as $message => $field) {
            $_upload[$message]= $field;
        }
        ksort($_upload);
        return $_upload;
    }

}