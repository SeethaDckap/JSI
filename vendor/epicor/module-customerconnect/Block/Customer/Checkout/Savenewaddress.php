<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Checkout;


/**
 * Customer Orders list
 */
class Savenewaddress extends \Epicor\Customerconnect\Block\Customer\Info
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Epicor\Common\Helper\Data $commonHelper
    )
    {
        $this->commonHelper = $commonHelper;
        parent::__construct();
        if ($this->commonHelper->customerAddressPermissionCheck('create')) {
            $this->setTemplate('customerconnect/customer/checkout/savenewaddress.phtml');
            $values = $this->customerconnectHelper->getSaveBillingAddressErpValues();
            $this->setErpDropdownValues($values['erp_dropdown_values']);
            $this->setErpCurrentDropdownValue($values['erp_current_dropdown_value']);
            $this->setSaveBillingAddressValues($values['save_billing_address_values']);
            $this->setSaveBillingAddressCurrentValue($values['save_billing_address_current_value']);
        }
    }

}
