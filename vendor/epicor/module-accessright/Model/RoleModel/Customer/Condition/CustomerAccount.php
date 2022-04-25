<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\RoleModel\Customer\Condition;

/**
 * Address rule condition data model.
 */
class CustomerAccount extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * Base name for hidden elements.
     *
     * @var string
     */
    protected $elementName = 'customer_rule';

    /**
     * CustomerAccount constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sourceYesno = $sourceYesno;
    }
    
    /**
     * Load attribute options
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'ecc_master_shopper' => __('Master Shopper'),
            'ecc_hide_price' => __('Hide Price'),
            'ecc_function' => __('Function'),
            'ecc_is_branch_pickup_allowed' => __('Branch Pickup Allowed'),
            'ecc_login_mode_type' => __('Login Mode Type'),
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
            case 'ecc_function':
                return 'string';

            case 'ecc_master_shopper':
            case 'ecc_hide_price':
            case 'ecc_is_branch_pickup_allowed':
            case 'ecc_login_mode_type':
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
            case 'ecc_function':
                return 'text';

            case 'ecc_master_shopper':
            case 'ecc_hide_price':
            case 'ecc_is_branch_pickup_allowed':
            case 'ecc_login_mode_type':
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
                case 'ecc_master_shopper':
                case 'ecc_hide_price':
                case 'ecc_is_branch_pickup_allowed':
                    $options = $this->sourceYesno->toOptionArray();
                    break;
                case 'ecc_login_mode_type':
                    $options = $this->getErpLoginModeType();
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function getErpLoginModeType(){

        return [['value' => "dealer", 'label' => __('Dealer')], ['value' => "shopper", 'label' => __('End Customer')]];
    }
}
