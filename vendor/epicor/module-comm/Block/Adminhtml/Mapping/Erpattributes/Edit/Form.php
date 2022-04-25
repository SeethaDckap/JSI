<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Edit;


/*
 * Form to insert/edit epicor_comm/erp_mapping_attributes table
 */

class Form extends \Magento\Backend\Block\Widget\Form
{
    /*
     * check if data is passed to form, else setup empty form  
     */

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $configConfigSourceYesnoFactory;

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
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config\Source\YesnoFactory $configConfigSourceYesnoFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        $this->registry = $registry;
        $this->configConfigSourceYesnoFactory = $configConfigSourceYesnoFactory;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();
        if ($this->backendSession->getErpattributesMappingData()) {
            $data = $this->backendSession->getErpattributesMappingData();
            $this->backendSession->getErpattributesMappingData(null);
        } elseif ($this->registry->registry('erpattributes_mapping_data')) {
            $data = $this->registry->registry('erpattributes_mapping_data')->getData();
        } else {
            $data = array();
        }

        $yesno = $this->configConfigSourceYesnoFactory->create()->toOptionArray();
        $form = $this->formFactory->create(
            array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
            )
        );

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'mapping_form', array(
            'legend' => __('ERP Attribute Information')
            )
        );

        $fieldset->addField(
            'attribute_code', 'text', array(
            'label' => __('Attribute Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'attribute_code',
            )
        );
        $inputType = $fieldset->addField(
            'input_type', 'select', array(
            'label' => __('Input Type'),
            'required' => true,
            'name' => 'input_type',
            'values' => $this->commHelper->_getEccattributeTypes(),
            )
        );
        $separator = $fieldset->addField(
            'separator', 'text', array(
            'label' => __('Separator'),
            'required' => true,
            'name' => 'separator',
            )
        );
        $configurable = $fieldset->addField(
            'is_visible_in_advanced_search', 'select', array(
            'label' => __('Visible in Advanced Search'),
            'name' => 'is_visible_in_advanced_search',
            'values' => $yesno,
            )
        );

        $fieldset->addField(
            'position', 'text', array(
            'label' => __('Position'),
            'required' => true,
            'class' => 'validate-number validate-not-negative-number',
            'name' => 'position',
            'note' => __('Value Must be Greater than or Equal to 0'),
            'value' => 1,
            )
        );
        $fieldset->addField(
            'is_searchable', 'select', array(
                'label' => __('Use In Search'),
                'name' => 'is_searchable',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'is_comparable', 'select', array(
                'label' => __('Comparable on Storefront'),
                'name' => 'is_comparable',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'is_filterable', 'select', array(
                'label' => __('Use in Layered Navigation'),
                'name' => 'is_filterable',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'is_filterable_in_search', 'select', array(
                'label' => __('Use in Search Results Layered Navigation'),
                'name' => 'is_filterable_in_search',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'is_used_for_promo_rules', 'select', array(
                'label' => __('Use for Promo Rule Conditions'),
                'name' => 'is_used_for_promo_rules',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'is_html_allowed_on_front', 'select', array(
                'label' => __('Allow HTML Tags on Storefront'),
                'name' => 'is_html_allowed_on_front',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'is_visible_on_front', 'select', array(
                'label' => __('Visible on Catalog Pages on Storefront'),
                'name' => 'is_visible_on_front',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'used_in_product_listing', 'select', array(
                'label' => __('Used in Product Listing'),
                'name' => 'used_in_product_listing',
                'values' => $yesno,
            )
        );
        $fieldset->addField(
            'used_for_sort_by', 'select', array(
                'label' => __('Used for Sorting in Product Listing'),
                'name' => 'used_for_sort_by',
                'values' => $yesno,
            )
        );

        $form->setValues($data);

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($inputType->getHtmlId(), $inputType->getName())
                ->addFieldMap($separator->getHtmlId(), $separator->getName())
                ->addFieldMap($configurable->getHtmlId(), $configurable->getName())
                ->addFieldDependence(
                    $separator->getName(), $inputType->getName(), 'multiselect'
                )
                ->addFieldDependence(
                    $configurable->getName(), $inputType->getName(), 'select'
                )
        );
        return parent::_prepareForm();
    }

    /*
     * retrieve options for filterable attribute
     */

    protected function getFilterableAttributeOptions()
    {
        return array(
            array('value' => '0', 'label' => __('No')),
            array('value' => '1', 'label' => __('Filterable (with results)')),
            array('value' => '2', 'label' => __('Filterable (no results)')),
        );
    }

}
