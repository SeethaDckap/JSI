<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Customer\Erpaccount;


class Address extends \Epicor\Common\Block\Adminhtml\Form\AbstractBlock
{

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerAddressHelper;

    /**
     * @var \Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $directoryConfigSourceCountryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\SelectFactory
     */
    protected $formElementSelectFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Directory\Model\Config\Source\CountryFactory $directoryConfigSourceCountryFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\SelectFactory $formElementSelectFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->formElementSelectFactory = $formElementSelectFactory;
        $this->customerAddressHelper = $customerAddressHelper;
        $this->directoryConfigSourceCountryFactory = $directoryConfigSourceCountryFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->directoryHelper = $directoryHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getAddressHtml($type, $data)
    {
        if (!$this->getForm()) {
            $this->setForm($this->formFactory->create());
        }

        return $this->_addFormAddress($type, $data, true);
    }

    protected function _addFormAddress($type, $data, $toHtml = false, $addSameAs = array(), $showPhoneFax = true)
    {

        $form = $this->getForm();
        //$form->setHtmlIdPrefix($type . '_');

        $fieldset = $form->addFieldset($type . '_address', array('legend' => __(ucwords($type) . ' Address')));

        if (!empty($addSameAs)) {
            foreach ($addSameAs as $sameType) {
                $fieldset->addField($sameType . '_' . $type, 'checkbox', array(
                    'label' => __('Same as ' . ucfirst($sameType)),
                    'required' => false,
                    'name' => 'same_as',
                    'class' => 'same_as'
                ));
            }
        }

        $fieldset->addField($type . '_name', 'text', array(
            'label' => __('Name'),
            'required' => true,
            'name' => $type . '[name]'
        ));

        $fieldset->addField($type . '_address1', 'text', array(
            'label' => __('Address Line 1'),
            'required' => true,
            'name' => $type . '[address1]'
        ));
        for ($_i = 2, $_n = $this->customerAddressHelper->getStreetLines(); $_i <= $_n; $_i++) {
            $fieldset->addField($type . "_address{$_i}", 'text', array(
                'label' => __("Address Line {$_i}"),
                'name' => $type . "[address{$_i}]"
            ));
        }
//        $fieldset->addField($type . '_address3', 'text', array(
//            'label' => Mage::helper('epicor_common')->__('Address Line 3'),
//            'name' => $type . '[address3]'
//        ));

        $fieldset->addField($type . '_city', 'text', array(
            'label' => __('City'),
            'required' => true,
            'name' => $type . '[city]'
        ));

        $county_id = $this->formElementSelectFactory->create(
            [
                'data' => array(
                    'label' => '',
                    'required' => true,
                    'name' => $type . '[county_id]',
                    'no_display' => true,
                )
            ]
        );
        $county_id->setForm($form);
        $county_id->setId($type . '_county_id');

        $fieldset->addField($type . '_county', 'text', array(
            'label' => __('County'),
            'required' => true,
            'name' => $type . '[county]',
            'after_element_html' => $county_id->getElementHtml()
        ));

        $fieldset->addField($type . '_country', 'select', array(
            'label' => __('Country'),
            'name' => $type . '[country]',
            'required' => true,
            'values' => $this->directoryConfigSourceCountryFactory->create()->toOptionArray(),
            'class' => 'countries'
        ));

        $fieldset->addField($type . '_postcode', 'text', array(
            'label' => __('Postcode'),
            'required' => true,
            'name' => $type . '[postcode]'
        ));
        $fieldset->addField($type . '_email', 'text', array(
            'label' => __('Email'),
            'name' => $type . '[email]',
            'required' => false
        ));

        if ($showPhoneFax) {
            $phoneRequired = $this->scopeConfig->getValue('checkout/options/telephone_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? true : false;
            $mobileRequired = ($this->scopeConfig->isSetFlag('customer/address/display_mobile_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ) ? true : false;
            $fieldset->addField($type . '_phone', 'text', array(
                'label' => __('Telephone Number'),
                'name' => $type . '[phone]',
                'required' => $phoneRequired
            ));
            $fieldset->addField($type . '_mobile_number', 'text', array(
                'label' => __('Mobile Phone Number'),
                'name' => $type . '[mobile_number]',
                'required' => $mobileRequired
            ));

            $field = $fieldset->addField($type . '_fax_number', 'text', array(
                'label' => __('Fax Number'),
                'name' => $type . '[fax_number]'
            ));
        }

        if ((strpos($type, 'delivery') !== false)) {
            $fieldset->addField($type . '_instructions', 'text', array(
                'label' => __('Instructions'),
                'name' => $type . '[instructions]',
            ));
        }

        $form->setData($data);

        if ($toHtml) {
            return $fieldset->toHtml();
        }
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * @return \Magento\Customer\Helper\Address
     */
    public function getCustomerAddressHelper()
    {
        return $this->customerAddressHelper;
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getDirectoryHelper()
    {
        return $this->directoryHelper;
    }
    //M1 > M2 Translation End

}
