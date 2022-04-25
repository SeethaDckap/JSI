<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab;

class  Details  extends  \Magento\Backend\Block\Widget\Form\Generic implements  \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Data\Form\Element\SelectFactory
     */
    protected $formElementSelectFactory;
    /**
     * @var \Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $directoryConfigSourceCountryFactory;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\SelectFactory $formElementSelectFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Directory\Model\Config\Source\CountryFactory $directoryConfigSourceCountryFactory,
        array $data = [])
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->directoryConfigSourceCountryFactory=$directoryConfigSourceCountryFactory;
        $this->formElementSelectFactory=$formElementSelectFactory;
        $this->directoryData=$directoryData;
        $this->registry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Initialize form
     *
     */
    public function initForm()
    {
        $location = $this->registry->registry('location');
        $form = $this->_formFactory->create();

        $this->setForm($form);

        $fieldset = $form->addFieldset('details', array('legend' => __('Details')));
        $fieldset->addField('code', 'text', array(
            'label' => __('ERP Code'),
            'required' => true,
            'name' => 'code',
            'disabled' => $location->isObjectNew() ? false : true
        ));

        $fieldset->addField('name', 'text', array(
            'label' => __('Name'),
            'required' => true,
            'name' => 'name'
        ));

        $fieldset->addField('address1', 'text', array(
            'label' => __('Address Line 1'),
            'name' => 'address1'
        ));
        $fieldset->addField('address2', 'text', array(
            'label' => __('Address Line 2'),
            'name' => 'address2'
        ));
        $fieldset->addField('address3', 'text', array(
            'label' => __('Address Line 3'),
            'name' => 'address3'
        ));

        $fieldset->addField('city', 'text', array(
            'label' => __('City'),
            'name' => 'city'
        ));

        $county_id = $this->formElementSelectFactory->create(["data"=>[
            'label' => '',
            'name' => 'county_id',
            'no_display' => true,
            'required' => true,
            'style' => 'display:none'
        ]]);
        $county_id->setForm($form);
        $county_id->setId('county_id');

        $fieldset->addField('county_code', 'text', array(
            'label' => __('State/Province'),
            'name' => 'county_code',
            'after_element_html' => $county_id->getElementHtml()
        ));

        $elementJs= "<script type=\"text/javascript\">" .
            "require(['mage/adminhtml/form'], function(){" .
            "window.updater = new RegionUpdater('country'," .
            " 'county_code', 'county_id', " .
            $this->directoryData->getRegionJson() .
            ", 'hide');});</script>";

        $fieldset->addField('country', 'select', array(
            'label' => __('Country'),
            'name' => 'country',
            'values' => $this->directoryConfigSourceCountryFactory->create()->toOptionArray(),
            'class' => 'countries',
            'required' => true,
            'after_element_html' => $elementJs
        ));

        $fieldset->addField('postcode', 'text', array(
            'label' => __('Zip/Postal Code'),
            'name' => 'postcode'
        ));
        $fieldset->addField('email_address', 'text', array(
            'label' => __('Email'),
            'name' => 'email_address'
        ));

        $fieldset->addField('telephone_number', 'text', array(
            'label' => __('Telephone Number'),
            'name' => 'telephone_number'
        ));

        $fieldset->addField('mobile_number', 'text', array(
            'label' => __('Mobile Number'),
            'name' => 'mobile_number'
        ));

        $fieldset->addField('fax_number', 'text', array(
            'label' => __('Fax Number'),
            'name' => 'fax_number'
        ));
        $fieldset->addField('sort_order', 'text', array(
            'label' => __('Sort Order'),
            'name' => 'sort_order'
        ));
        $fieldset->addField('locationVisible', 'checkbox', array(
            'label'     => __('Location Visible'),
            'name'      => 'locationVisible',
            'onclick'   => "if(this.checked){ $('location_visible').value = 1; } else { $('location_visible').value = 0; }",
            'checked'   => $location->isObjectNew() ? true : $location->getLocationVisible()
        ));
        $fieldset->addField('location_visible', 'hidden', array(
            'name'      => 'location_visible'
        ));
        $fieldset->addField('includeInventory', 'checkbox', array(
            'label'     => __('Include Inventory'),
            'name'      => 'includeInventory',
            'onclick'   => "if(this.checked){ $('include_inventory').value = 1; } else { $('include_inventory').value = 0; }",
            'checked'   => $location->isObjectNew() ? true : $location->getIncludeInventory()
        ));
        $fieldset->addField('include_inventory', 'hidden', array(
            'name'      => 'include_inventory'
        ));
        $fieldset->addField('showInventory', 'checkbox', array(
            'label'     => __('Show Inventory'),
            'name'      => 'showInventory',
            'onclick'   => "if(this.checked){ $('show_inventory').value = 1; } else { $('show_inventory').value = 0; }",
            'checked'   => $location->isObjectNew() ? true : $location->getShowInventory()
        ));
        $fieldset->addField('show_inventory', 'hidden', array(
            'name'      => 'show_inventory'
        ));

        $site = $this->_coreRegistry->registry('location');
        if ($site->isObjectNew()) {
            $defaultCountry = $this->scopeConfig->getValue(\Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_COUNTRY_DEFAULT);
            $site->setData('country', $defaultCountry);
        }

        if ($location->isObjectNew()) {
            $site->setData('location_visible', 1);
            $site->setData('include_inventory', 1);
            $site->setData('show_inventory', 1);
        }
        $form->setValues($site->getData());

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Location Details');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}