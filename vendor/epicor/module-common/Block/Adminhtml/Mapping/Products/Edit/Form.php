<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Products\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ProductsFactory
     */
    protected $commErpMappingProductsFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commErpMappingProductsFactory = $commErpMappingProductsFactory;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getProductsMappingData()) {
            $data = $this->_session->getProductsMappingData();
            $this->_session->getProductsMappingData(null);
        } elseif ($this->registry->registry('products_mapping_data')) {
            $data = $this->registry->registry('products_mapping_data')->getData();
        } else {
            $data = array();
        }
        $form = $this->formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data']
            ]
        );
        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));
        $fieldset->addField('product_sku', 'text', array(
            'label' => __('SKU'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'product_sku'
        ));
        $fieldset->addField('product_uom', 'text', array(
            'label' => __('UOM'),
            'name' => 'product_uom'
        ));
        $data = $this->includeStoreIdElement($data);
        $form->setValues($data);

        return parent::_prepareForm();
    }

}
