<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Erpattributes\Csvupload;


/*
 * Form to insert/edit epicor_comm/erp_mapping_attributes table
 */

class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{

    const XML_FILE_UPLOAD = 1;
    const XML_TEXT_UPLOAD = 2;

    /*
     * Setup Form for updating epicor_comm/erp_mapping_attributes by csv 
     */

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $attributeTypes = $this->commHelper->_getEccattributeTypes();
        $typeIndex = 'Type Index =';
        array_shift($attributeTypes);
        $types = implode(", ", array_keys($attributeTypes));
        $count = 1;
        $types = str_replace('multiselect', 'multi_selec', $types); // don't want multiselect to change
        $types = str_replace('select', 'select (for Dropdown) ', $types);
        $types = str_replace('multi_selec', 'multiselect', $types);
        $types = str_replace('weee', 'weee (for Fixed Product Tax) ', $types);
        $typeIndex = "Valid Attribute Types : " . $types;

 
        $form = $this->formFactory->create(['data' => [
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/csvupload'),
            'method' => 'post',
            'enctype' => 'multipart/form-data']
        ]);
   
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'layout_block_form', array(
            'legend' => __('CSV Upload')
            )
        );

        $fieldset->addField('attributeimportcsv', 'button', array(
            'value' => __('Download Example CSV File'),
            'onclick' => "return window.location = '" . $this->getUrl('adminhtml/epicorcommon_mapping_erpattributes/createNewErpattributesCsv') . "';",
            'name' => 'attributeimportcsv',
            'class' => 'form-button'
        ));

        $fieldset->addField(
            'csv_file', 'file', array(
            'label' => __('CSV File'),
            'required' => true,
            'name' => 'csv_file'
            )
        );

        $fieldset->addField('typeindex', 'note', array(
            'text' => __('<br>' . $typeIndex . '<br>'),
        ));

        return parent::_prepareForm();
    }

}
