<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\RoleModel\Erp\Condition;

/**
 * Address rule condition data model.
 */
class Erpaccount extends \Magento\Rule\Model\Condition\AbstractCondition
{

    /**
     * Base name for hidden elements.
     *
     * @var string
     */
    protected $elementName = 'erp_rule';

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $locationFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \Epicor\Comm\Model\LocationFactory $locationFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sourceYesno = $sourceYesno;
        $this->locationFactory = $locationFactory;
    }
    
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            //'name' => __('Name'),
            'allow_backorders' => __('Allow Backorders'),
            'default_location_code' => __('Default Location'),
            'is_branch_pickup_allowed' => __('Branch Pickup Allowed'),
            'is_arpayments_allowed' => __('AR Payment Allowed'),
            'login_mode_type' => __('Login Mode Type')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
//            case 'name':
//                return 'string';
            case 'allow_backorders':
            case 'is_branch_pickup_allowed':
            case 'is_arpayments_allowed':
            case 'default_location_code':
            case 'login_mode_type':
                return 'select';
        }
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
//            case 'name':
//                return 'text';

            case 'allow_backorders':
            case 'is_branch_pickup_allowed':
            case 'is_arpayments_allowed':
            case 'default_location_code':
            case 'login_mode_type':
                return 'select';
        }
        return 'text';
    }
    
    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'allow_backorders':
                case 'is_branch_pickup_allowed':
                case 'is_arpayments_allowed':
                    $options = $this->sourceYesno->toOptionArray();
                    break;
                case 'default_location_code':
                    $options = $this->getLocationData();
                    break;
                case 'login_mode_type':
                    $options = $this->getErpLoginModeType();
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * get Location Array
     * @return array
     */
    public function getLocationData()
    {
        $data = [];
        $locations = $this->locationFactory->create()->getCollection();
        $locations->getItems();
        foreach ($locations as $location) {
            $data[$location->getCode()] = $location->getCode();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getErpLoginModeType()
    {
        return [['value' => "dealer", 'label' => __('Dealer')], ['value' => "shopper", 'label' => __('End Customer')]];
    }
}
