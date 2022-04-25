<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout;


class LayoutProcessor
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $shippingdates;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commonContext;
    /**
     * @var \Epicor\Comm\Helper\Context
     */
    protected $commHelper;

    protected $_session;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory,
        \Epicor\Comm\Model\Checkout\Dates $shippingdates,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Magento\Customer\Model\Session $session,
        \Epicor\Common\Helper\Context $commonContext
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->shippingdates = $shippingdates;
        $this->_session = $session;
        $this->checkoutSession = $commonContext->getCheckoutSession();
        $this->commHelper = $commonContext->getCommHelper();
        $this->timezone = $commonContext->getTimezone();
        $this->commonContext = $commonContext;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->messageManager = $commonContext->getMessageManager();
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $cusOrderRefValidation = $this->commHelper->cusOrderRefValidation();
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['before-form']['children']['ecc_customer_order_ref'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAdditional',
                'template' => 'ui/form/field',
                'value'    => $this->checkoutSession->getQuoteOnly()->getEccCustomerOrderRef(),
                'elementTmpl' => 'Epicor_Comm/ui/form/element/input/inputlength',
                'options' => [],
                'id' => 'po-ref',
                'maxlength' => 50
            ],
            'dataScope' => 'shippingAdditional.ecc_customer_order_ref',
            'label' => new \Magento\Framework\Phrase(__('Customer Order Reference / Purchase Order Number')),
            'provider' => 'checkoutProvider',
            'additionalClasses'=>'ecc_customer_order_ref',
            'visible' => true,
            'sortOrder' => 250,
            'validation' => $cusOrderRefValidation,
            'id' => 'ecc_customer_order_ref',

        ];
        if($maxPoLength = $this->scopeConfig->getValue('checkout/options/max_po_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['before-form']['children']['ecc_customer_order_ref']['config']['maxlength'] = $maxPoLength;
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['before-form']['children']['ecc_customer_order_ref']['config']['notice'] = __('(Max %1 chars)', $maxPoLength);
        }

        // Ship Status
        if ($this->commHelper->isShipStatus()) {
            $SSCollection = $this->commHelper->getShipStatusCollection();
            $SSOptions = array();
            $SSData = [];
            $SSSelected = '';
            $SSDefaultHelp = "";
            $ssCount = 0;
            if (count($SSCollection) > 0) {
                foreach ($SSCollection as $shipStatus) {

                    $SSOptions[] = [
                        'value' => $shipStatus->getShippingStatusCode(),
                        'label' => $shipStatus->getDescription(),
                        'data-help' => "test"
                    ];
                    //Default Help
                    if($ssCount == 0) {
                        $SSDefaultHelp = $shipStatus->getStatusHelp();
                        $ssCount++;
                    }
                    // prepare SS help for Js
                    $SSData[$shipStatus->getShippingStatusCode()] = $shipStatus->getStatusHelp();

                    // Selected Ship Status Data
                    if($this->checkoutSession->getQuoteOnly()->getEccShipStatusErpcode() == $shipStatus->getShippingStatusCode()) {
                        $SSSelected = $this->checkoutSession->getQuoteOnly()->getEccShipStatusErpcode();
                        $SSDefaultHelp = $shipStatus->getStatusHelp();
                    }
                }

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children']['ecc_ship_status_erpcode'] = [
                    'component' => 'Epicor_Comm/epicor/comm/js/form/element/ship-status-help',
                    'config' => [
                        'customScope' => 'shippingAdditional',
                        'template' => 'ui/form/field',
                        'value' => $SSSelected,
                        'elementTmpl' => 'ui/form/element/select',
                        'options' => $SSOptions,
                        'optionHelp' => json_encode($SSData),
                        'id' => 'ecc_ship_status_erpcode'
                    ],
                    'dataScope' => 'shippingAdditional.ecc_ship_status_erpcode',
                    'label' => new \Magento\Framework\Phrase(__('Ship Status')),
                    'provider' => 'checkoutProvider',
                    'additionalClasses' => 'ecc_ship_status_erpcode ecc_shipping_berore_field',
                    'visible' => true,
                    'sortOrder' => 256,
                    'tooltip' => ['description' => $SSDefaultHelp],
                    'id' => 'ecc_ship_status_erpcode'
                ];
            }
        }

        //Require delivery date
        if ($this->commHelper->isRequiredDate()) {
            $requireDate = null;
            if ($this->checkoutSession->getQuoteOnly()->getEccRequiredDate() && $this->checkoutSession->getQuoteOnly()->getEccRequiredDate() != "0000-00-00") {
                $requireDate = date('F j, Y', strtotime($this->checkoutSession->getQuoteOnly()->getEccRequiredDate()));
                if(strtotime($this->timezone->formatDate()) > strtotime($this->checkoutSession->getQuoteOnly()->getEccRequiredDate())){
                    //$this->messageManager->addWarningMessage(__("Required Date should be greater than or equal to today."));
                    $requireDate = null;
                }
            }

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['before-form']['children']['ecc_required_date'] = [
                'component' => 'Epicor_Comm/epicor/comm/js/form/element/require-date',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'value' => $requireDate ?: "",
                    'elementTmpl' => 'ui/form/element/date',
                    'options' => [
                        "dateFormat" => "MMMM dd, yyyy",
                        "minDate" => "new date()",
                        //"buttonImageOnly" => true,
                        'buttonText' => '',
                        "showOn" => 'both'
                    ],
                    'id' => 'ecc-required-date',
                    'maxlength' => 50,
                    'isCart' => false
                ],
                'dataScope' => 'shippingAddress.ecc_required_date',
                'label' => new \Magento\Framework\Phrase(__('Require Date')),
                'provider' => 'checkoutProvider',
                'additionalClasses' => 'date ecc_required_date ecc_shipping_berore_field',
                'visible' => true,
                'sortOrder' => 257,
                'validationParams' => ["dateFormat" => "MMMM dd, yyyy"],
                'validation' => ["validate-date" => true, "validate-before-today-date" => true],
                'id' => 'ecc_required_date',
            ];
        }

        if ($this->shippingdates->isShow()) {
            $availabledates = $this->shippingdates->getAvailableDates($this->checkoutSession->getQuoteOnly());
            $datesoptions = [];
            if (count($availabledates) === 0) {
                $defaultdate = $this->shippingdates->getDefaultAvailableDate();
                $datesoptions[] = ['value' => $defaultdate, 'label' => date('F j, Y', strtotime($defaultdate))];
            } else {
                foreach ($availabledates as $key => $date) {
                    $datesoptions[] = ['value' => $date, 'label' => date('F j, Y', strtotime($date))];
                }
            }
            if ($this->shippingdates->showAsList()) {
                $defaultValue = '';
                if (isset($datesoptions[0]['value'])) {
                    $defaultValue = $datesoptions[0]['value'];
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shippingAdditional']['children']['dda-block']
                    = [
                    'component' => 'Epicor_Comm/epicor/comm/js/view/checkout/shipping/dda',
                    'children' => [
                        'dda-form' => [
                            'component' => 'uiComponent',
                            'displayArea' => 'dda-form',
                            'children' => [
                                'shipping_dates' => [
                                    'component' => 'Magento_Ui/js/form/element/checkbox-set',
                                    'type' => 'radio',
                                    'config' => [
                                        'customScope' => 'shippingAdditional',
                                        'template' => 'ui/form/field',
                                        'value' => $defaultValue,
                                        'template' => 'Epicor_Comm/ui/form/element/shippingdates/checkbox-set',
                                        'options' => $datesoptions,
                                        'id' => 'ecc_required_date',
                                        'sectionblock' => 'default_shippingdates',
                                    ],
                                    'dataScope' => 'shippingAdditional.ecc_required_date',
                                    'label' => '',
                                    'provider' => 'checkoutProvider',
                                    'visible' => true,
                                    'sortOrder' => 250,
                                    'id' => 'ecc_required_date',
                                ]
                            ]
                        ]
                    ]
                ];
            } else {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shippingAdditional']['children']['dda-block']
                    = [
                    'component' => 'Epicor_Comm/epicor/comm/js/view/checkout/shipping/dda',
                    'children' => [
                        'dda-form' => [
                            'component' => 'uiComponent',
                            'displayArea' => 'dda-form',
                            'children' => [
                                'shipping_dates' => [
                                    'component' => 'Magento_Ui/js/form/element/select',
                                    'config' => [
                                        'customScope' => 'shippingAdditional',
                                        'template' => 'Epicor_Comm/checkout/shipping/field',
                                        'value' => $this->checkoutSession->getQuoteOnly()->getEccRequiredDate(),
                                        'elementTmpl' => 'ui/form/element/select',
                                        'options' => $datesoptions,
                                        'sectionblock' => 'default_shippingdates',
                                        'id' => 'ecc_required_date'
                                    ],
                                    'dataScope' => 'shippingAdditional.ecc_required_date',
                                    'label' => 'Available Delivery Dates',
                                    'provider' => 'checkoutProvider',
                                    'visible' => true,
                                    'sortOrder' => 250,
                                    'id' => 'ecc_required_date'
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        //Tax exempt reference
        if ($this->commHelper->isTaxExemptionAllowed()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['before-form']['children']['ecc_tax_exempt_reference'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAdditional',
                    'template' => 'ui/form/field',
                    'value' => $this->checkoutSession->getQuoteOnly()->getEccTaxExemptReference(),
                    'elementTmpl' => 'Epicor_Comm/ui/form/element/input/inputlength',
                    'options' => [],
                    'id' => 'po-ref',
                    'maxlength' => 255
                ],
                'dataScope' => 'shippingAdditional.ecc_tax_exempt_reference',
                'label' => new \Magento\Framework\Phrase(__('Tax Exempt Reference')),
                'provider' => 'checkoutProvider',
                'additionalClasses' => 'ecc_tax_exempt_reference',
                'visible' => true,
                'sortOrder' => 280,
                'validation' => [],
                'id' => 'ecc_tax_exempt_reference',

            ];
            if ($maxTaxRefLength = $this->scopeConfig->getValue('checkout/options/max_tax_exempt_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children']['ecc_tax_exempt_reference']['config']['maxlength'] = $maxTaxRefLength;
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children']['ecc_tax_exempt_reference']['config']['notice'] = __('(Max %1 chars)', $maxTaxRefLength);
            }
        }

        //Payment - additional reference
        if ($this->commHelper->isEccAdditionalReference()) {
            $isMandatoryEccAdditionalReference = $this->commHelper->isMandatoryEccAdditionalReference();
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']['before-place-order']['children']['ecc_additional_reference'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'billingAddress',
                    'template' => 'ui/form/field',
                    'value' => $this->checkoutSession->getQuoteOnly()->getEccAdditionalReference(),
                    'elementTmpl' => 'Epicor_Comm/ui/form/element/input/inputlength',
                    'options' => [],
                    'id' => 'po-ref',
                    'maxlength' => 255
                ],
                'dataScope' => 'additional.reference',
                'label' => new \Magento\Framework\Phrase(__('Additional Reference')),
                'provider' => 'checkoutProvider',
                'additionalClasses' => 'ecc_additional_reference',
                'visible' => true,
                'sortOrder' => 249,
                'validation' => $isMandatoryEccAdditionalReference ? ['required-entry' => true] : [],
                'id' => 'ecc_additional_reference',
            ];
            if($isMandatoryEccAdditionalReference){
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']["children"]["additional-payment-validators"]["children"]["additional-reference-validator"]
                ["component"] = "Epicor_Comm/epicor/comm/js/view/additional-reference-validation";
            }


            $maxlength = $this->commHelper->getAReferenceMaxLength();
            if ($maxlength) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['before-place-order']['children']
                ['ecc_additional_reference']['config']['maxlength'] = $maxlength;

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['before-place-order']['children']
                ['ecc_additional_reference']['config']['notice'] = __('(Max %1 chars)', $maxlength);
            }
        }

        //payment - Comment Box
        if ($this->isCommentAllowed()) {
            $notice = $this->getMaxCommentSize() ? __('%1 Chars Remaining', $this->getMaxCommentSize()) : '';
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']['before-place-order']['children']['comment'] = [
                'component' => 'Magento_Ui/js/form/element/textarea',
                'config' => [
                    'customScope' => 'billingAddress',
                    'template' => 'ui/form/field',
                    'options' => [],
                    'id' => 'comment'
                ],
                'dataScope' => 'ordercomment.comment',
                'label' => new \Magento\Framework\Phrase(__('Order Comment')),
                'value' => $this->getAddressInstructions(),
                'notice' => __('%1 Chars Remaining', $this->getMaxCommentSize()),
                'notice' => $notice,
                'provider' => 'checkoutProvider',
                'visible' => true,
                'sortOrder' => 250,
                'id' => 'comment'
            ];
        }

        if ($this->_session->isLoggedIn()) {

            $deliveryCount = $this->commHelper->getAddressesCollectionForTypeCount('delivery');
            if($deliveryCount) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['ecc_shipping_search'] = [
                    'component' => 'Magento_Ui/js/form/components/button',
                    'config' => [
                        'title' => __('Search Address'),
                        'formElement' => 'ecc_shipping_search',
                        'componentType' => 'ecc_shipping_search',
                        'component' => 'Epicor_Comm/js/epicor/comm/component/shippingbutton',
                        'sortOrder' => 249
                    ],
                    'visible' => true,
                    'sortOrder' => 250,
                    'id' => 'ecc_shipping_search'
                ];

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['ecc_limited_address_notice'] = [
                    'component' => 'Epicor_Comm/epicor/comm/js/view/checkout/shipping/limit-address',
                    'visible' => true,
                    'sortOrder' => 250,
                    'id' => 'limit-address'
                ];
            }

            $invoiceCount = $this->commHelper->getAddressesCollectionForTypeCount('invoice');


            if($invoiceCount ) {
                //don't allow address search if forceaddresstype set and customer is not explicitly associated with erp
                $forceAddressType = $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customerSession = $this->commonContext->getCustomerSessionFactory()->create();
                $customer = $customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */
                $erpAccount = $customer->getEccErpaccountId();
                if(!$forceAddressType || ($forceAddressType && !$erpAccount && !$customer->isSalesRep())){
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['children']['payments-list']['children']['before-place-order']['children']['ecc_billing_search'] = [
                        'component' => 'Magento_Ui/js/form/components/button',
                        'config' => [
                            'title' => __('Search Address'),
                            'formElement' => 'ecc_billing_search',
                            'componentType' => 'ecc_billing_search',
                            'component' => 'Epicor_Comm/js/epicor/comm/component/billingbutton',
                            'sortOrder' => 210
                        ],
                        'visible' => true,
                        'sortOrder' => 210,
                        'id' => 'ecc_billing_search'
                    ];
                }
            }
        }

        /**
         * Added this to remove ecc_instructions field from Checkout Billing and Shipping Address Form
         */
        foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']
                as $key => $payment) {
            if (isset($payment['children']['form-fields']['children']['ecc_instructions'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']['ecc_instructions']['visible'] = false;
            }
        }
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['ecc_instructions'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['ecc_instructions']['visible'] = false;
        }
        /**
         * Added this to exclude customer address fields
         */
        $customerAddrOpt = $this->commHelper->getCustomerAddressOptions();
        foreach ($customerAddrOpt as $component => $optionArr) {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component])) {
                if (!$this->scopeConfig->getValue('customer/address/' . $optionArr['name'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component]);
                }elseif (isset($optionArr['req']) && $optionArr['req']) {
                        $fieldShipping = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component];
                        $fieldShipping['validation'] = ['required-entry' => true];
                        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component] = $fieldShipping;
                }
            }

            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']
                     as $key => $payment) {
                if (isset($payment['children']['form-fields']['children'][$component])) {
                    if (!$this->scopeConfig->getValue('customer/address/' . $optionArr['name'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component]);
                    }elseif(isset($optionArr['req']) && $optionArr['req']){
                        $fieldBilling = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component];
                        $fieldBilling['validation'] = ['required-entry' => true];
                        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component] = $fieldBilling;
                    }
                }
            }
        }
        /**
         * Added this to include character length limits across billing/shipping form fields
         */
        $limitArr = $this->commHelper->getLimitOptions(false);
        $fieldArr = $this->commHelper->getLimitOptions(true);

        foreach ($limitArr as $limitVar) {
            if ($this->scopeConfig->isSetFlag('customer/address/limits_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) &&
                    $checkLength = $this->scopeConfig->getValue('customer/address/limit_' . $limitVar . '_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ) {
                $filteredComponent = array_filter($fieldArr, function ($var) use($limitVar) {
                    return ($var === $limitVar);
                });
                foreach ($filteredComponent as $component => $val) {
                    if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component])) {
                        if ($component === "street") {
                            $fieldShipping = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component]['children'];
                            foreach ($fieldShipping as $key => $field) {
                                $field['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                                $field['config']['maxlength'] = $checkLength;
                                $field['notice'] = __('max %1 chars', $checkLength);

                                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component]['children'][$key] = $field;
                            }
                        } else {
                            $fieldShipping = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component];
                            $fieldShipping['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                            $fieldShipping['config']['maxlength'] = $checkLength;
                            $fieldShipping['notice'] = __('max %1 chars', $checkLength);

                            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$component] = $fieldShipping;
                        }
                    }

                    foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']
                    as $key => $payment) {
                        if (isset($payment['children']['form-fields']['children'][$component])) {
                            if ($component === "street") {
                                $fieldBilling = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component]['children'];
                                foreach ($fieldBilling as $index => $field) {
                                    $field['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                                    $field['config']['maxlength'] = $checkLength;
                                    $field['notice'] = __('max %1 chars', $checkLength);

                                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component]['children'][$index] = $field;
                                }
                            } else {
                                $fieldBilling = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component];
                                $fieldBilling['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                                $fieldBilling['config']['maxlength'] = $checkLength;
                                $fieldBilling['notice'] = __('max %1 chars', $checkLength);

                                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children'][$component] = $fieldBilling;
                            }
                        }
                    }
                }
            }
        }

        if($this->commHelper->getEccHidePrice() || $this->commHelper->isPriceDisplayDisabled()){
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['subtotal']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['grand-total']);
        }
        if (!$this->_session->isLoggedIn()) {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['config']['maxlength'] = '';
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['config']['notice'] = '';
                if ($this->commHelper->isAddressLimitEnabled()
                    && $emailLength = $this->commHelper->getAddressCharacterLimit('email')
                ) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['config']['maxlength'] = $emailLength;
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['config']['notice'] = __('max %1 chars', $emailLength);
                }
            }
        }
        return $jsLayout;
    }

    public function getMaxCommentSize()
    {
        if ($this->limitTextArea()) {
            return $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return '';
    }

    public function limitTextArea()
    {
        $result = false;
        if ($this->isCommentAllowed() &&
            $this->scopeConfig->isSetFlag('checkout/options/limit_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $value = $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (is_numeric($value)) {
                $result = true;
            }
        }
        return $result;
    }

    public function getAddressInstructions()
    {
        $session = $this->checkoutSession;
        /* @var $session Mage_Checkout_Model_Session */
        $addressId = $session->getQuoteOnly()->getShippingAddress()->getCustomerAddressId();
        $customerAddress = $this->customerAddressFactory->create()->load($addressId);
        return $customerAddress->getEccInstructions();
    }

    public function getRemainingCommentSize()
    {
        $max = $this->getMaxCommentSize();
        $current = $this->getAddressInstructions();
        return $max - strlen($current);
    }

    public function isCommentAllowed()
    {
        return $this->scopeConfig->getValue('checkout/options/show_comments', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
