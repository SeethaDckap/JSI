<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Locations;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author Paul.Ketelle
 */
class Form  extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_template = 'epicor_comm/catalog/product/edit/tab/locations.phtml';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Config\Source\ProducttaxFactory
     */
    protected $commConfigSourceProducttaxFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\Comm\Model\Config\Source\ProducttaxFactory $commConfigSourceProducttaxFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->commConfigSourceProducttaxFactory = $commConfigSourceProducttaxFactory;
       parent::__construct($context, $registry, $formFactory, $data);
    }


    protected function _prepareForm()
    {
        $product = $this->registry->registry('product');
        /* @var $product Epicor_Comm_Model_Product */

        $helper = $this->commLocationsHelper;
        /* @var $helper Epicor_Comm_Helper_Locations */

        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('locations_form', array('legend' => __('Location')));

        $fieldset->addType('manufacturerInput', '\Epicor\Comm\Block\Adminhtml\Form\Element\Manufacturers');

        $fieldset->addField('location_post_url', 'hidden', array(
            'name' => 'locationPostUrl',
            'value' => $this->getUrl('adminhtml/epicorcomm_catalog_product/locationpost', array('product_id' => $product->getId()))
        ));
        $fieldset->addField('delete_message', 'hidden', array(
            'name' => 'deleteMessage',
            'value' => __('Are you sure you want to delete this location data ?')
        ));
        $fieldset->addField('id', 'hidden', array(
            'name' => 'id',
        ));
        $fieldset->addField('available_locations', 'hidden', array(
            'value' => json_encode(array_keys($helper->getLocationDiff($product->getAdminLocations()))),
            'name' => 'available_locations'
        ));

        $fieldset->addField('location_code', 'select', array(
            'label' => __('Location Code'),
            'required' => false,
            'name' => 'location_code',
            'values' => $this->commResourceLocationCollectionFactory->create()->toOptionArray()#new Epicor_Comm_Model_Resource_Location_Collection()
        ));
        $fieldset->addField('stock_status', 'text', array(
            'label' => __('Stock Status'),
            'required' => false,
            'name' => 'stock_status',
        ));
        $fieldset->addField('free_stock', 'text', array(
            'label' => __('Free Stock'),
            'required' => false,
            'name' => 'free_stock',
        ));
        $fieldset->addField('minimum_order_qty', 'text', array(
            'label' => __('Minimum Order Qty'),
            'required' => false,
            'name' => 'minimum_order_qty',
        ));
        $fieldset->addField('maximum_order_qty', 'text', array(
            'label' => __('Maximum Order Qty'),
            'required' => false,
            'name' => 'maximum_order_qty',
        ));
        $fieldset->addField('lead_time_days', 'text', array(
            'label' => __('Lead Time Days'),
            'required' => false,
            'name' => 'lead_time_days',
        ));
        $fieldset->addField('lead_time_text', 'text', array(
            'label' => __('Lead Time Text'),
            'required' => false,
            'name' => 'lead_time_text',
        ));
        $fieldset->addField('supplier_brand', 'text', array(
            'label' => __('Supplier Brand'),
            'required' => false,
            'name' => 'supplier_brand',
        ));
        $fieldset->addField('tax_code', 'select', array(
            'label' => __('Tax Code'),
            'required' => false,
            'name' => 'tax_code',
            'values' => $this->commConfigSourceProducttaxFactory->create()->toOptionArray(true),
        ));
        $fieldset->addField('currency_code_display', 'text', array(
            'label' => __('Currency Code'),
            'required' => false,
            'name' => 'currency_code',
            'readonly' => true,
            'class' => 'disabled',
            'value' => $product->getStore()->getBaseCurrencyCode()
        ));
        $fieldset->addField('base_price', 'text', array(
            'label' => __('Base Price'),
            'required' => false,
            'name' => 'base_price',
        ));
        $fieldset->addField('cost_price', 'text', array(
            'label' => __('Cost Price'),
            'required' => false,
            'name' => 'cost_price',
        ));
        $fieldset->addField('manufacturers', 'manufacturerInput', array(
            'label' => __('Manufacturers'),
            'required' => false,
            'name' => 'manufacturers'
        ));
        $fieldset->addField('note', 'note', array(
            'text' => __('<span class="full-width-note">Leave fields blank to use product values instead of location values</span>'),
        ));
        $fieldset->addField('updateLocationSubmit', 'submit', array(
            'value' => __('Update'),
            'onclick' => "return productLocations.rowUpdate();",
            'name' => 'updateLocationSubmit',
            'class' => 'form-button',
        ));
        $fieldset->addField('addLocationSubmit', 'submit', array(
            'value' => __('Add'),
            'onclick' => "return productLocations.rowUpdate();",
            'name' => 'addLocationSubmit',
            'class' => 'form-button',
        ));
        $this->setForm($form);
    }
 /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Locations\Grid',
                'locations.grid'
            )
        );
        parent::_prepareLayout();
        return $this;
    }
}
