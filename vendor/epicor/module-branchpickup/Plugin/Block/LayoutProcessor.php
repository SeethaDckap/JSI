<?php
namespace Epicor\BranchPickup\Plugin\Block;


class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    
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
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;       
    
    
    protected $_session;


    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $shippingdates;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * BranchPickup LayoutProcessor constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\AddressFactory $customerAddressFactory
     * @param \Epicor\BranchPickup\Helper\Data $branchPickupHelper
     * @param \Epicor\Comm\Model\Checkout\Dates $shippingdates
     * @param \Magento\Customer\Model\Session $session
     * @param \Epicor\Common\Helper\Context $commonContext
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\Comm\Model\Checkout\Dates $shippingdates,
        \Magento\Customer\Model\Session $session,
        \Epicor\Common\Helper\Context $commonContext
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->checkoutSession = $checkoutSession;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->_session = $session;
        $this->shippingdates = $shippingdates;
        $this->commHelper = $commonContext->getCommHelper();
        $this->timezone = $commonContext->getTimezone();
        $this->messageManager = $commonContext->getMessageManager();
    }

    
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        
        $branchpickupEnabled = $this->branchPickupHelper->isBranchPickupAvailable(); 
        if(!$branchpickupEnabled) {
            unset($jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']);
            return $jsLayout;
        }
        
        
        if (!$this->_session->isLoggedIn()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['bfirstname'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'required' => true,
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                    'id' => 'bfirstname'
                ],
                'validation' => [
                   'required-entry' => true
                ],            
                'dataScope' => 'shippingAddress.firstname',
                'label' => 'First Name *',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'displayArea' => 'branchpickup-customer-email',
                'sortOrder' => 2,
            ];
        
            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['blastname'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'required' => true,
                'config' => [
                    'customScope' => 'checkoutProvider',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                    'id' => 'blastname'
                ],
                'validation' => [
                   'required-entry' => true
                ],            
                'dataScope' => 'shippingAddress.lastname',
                'label' => 'Last Name *',
                'provider' => 'checkoutProvider',
                'displayArea' => 'branchpickup-customer-email',
                'visible' => true,
                'sortOrder' => 3
            ];
            // branch-pickup-address maxLength placed here to avoid layout issues when address limit disabled. Value of 255 used for effectively unlimited
            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['customer-email']['config']['maxlength'] = 255;

            if ($this->commHelper->isAddressLimitEnabled()) {
                $emailLimit = $this->commHelper->getAddressCharacterLimit('email');
                $firstnameLimit = $this->commHelper->getAddressCharacterLimit('name');
                $lastnameLimit = $this->commHelper->getAddressCharacterLimit('lastname');
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['customer-email']['config']['maxlength'] = $emailLimit;
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['customer-email']['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['customer-email']['config']['notice'] = __('max %1 chars', $emailLimit);
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['bfirstname']['config']['maxlength'] = $firstnameLimit;
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['bfirstname']['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['bfirstname']['config']['notice'] = __('max %1 chars', $firstnameLimit);
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['blastname']['config']['maxlength'] = $lastnameLimit;
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['blastname']['config']['elementTmpl'] = 'Epicor_Comm/ui/form/element/input/inputlength';
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['branch-pickup-address']['children']['blastname']['config']['notice'] = __('max %1 chars', $lastnameLimit);
            }
        }
        $cusOrderRefValidation = $this->commHelper->cusOrderRefValidation();
        $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_customer_order_ref'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAdditional',
                'template' => 'ui/form/field',
                'value' => $this->checkoutSession->getQuote()->getEccCustomerOrderRef(),
                'elementTmpl' => 'Epicor_Comm/ui/form/element/input/inputlength',
                'options' => [],
                'id' => 'po-ref',
                'maxlength' => 50
            ],
            'dataScope' => 'shippingAdditional.ecc_customer_order_ref',
            'label' => 'Customer Order Reference / Purchase Order Number',
            'provider' => 'checkoutProvider',
            'additionalClasses' => 'becc_customer_order_ref',
            'visible' => true,
            'sortOrder' => 4,
            'validation' => $cusOrderRefValidation,
            'id' => 'becc_customer_order_ref',
        ];
        if ($maxPoLength = $this->scopeConfig->getValue('checkout/options/max_po_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_customer_order_ref']['config']['maxlength'] = $maxPoLength;
            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_customer_order_ref']['config']['notice'] = __('(Max %1 chars)', $maxPoLength);
        }

        //Tax Exempt Reference
        if ($this->commHelper->isTaxExemptionAllowed()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_tax_exempt_reference'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAdditional',
                    'template' => 'ui/form/field',
                    'value' => $this->checkoutSession->getQuote()->getEccTaxExemptReference(),
                    'elementTmpl' => 'Epicor_Comm/ui/form/element/input/inputlength',
                    'options' => [],
                    'id' => 'tax-ref',
                    'maxlength' => 255
                ],
                'dataScope' => 'shippingAdditional.ecc_tax_exempt_reference',
                'label' => 'Tax Exempt Reference',
                'provider' => 'checkoutProvider',
                'additionalClasses' => 'becc_tax_exempt_reference',
                'visible' => true,
                'sortOrder' => 5,
                'validation' => [],
                'id' => 'becc_tax_exempt_reference',
            ];
            if ($maxTaxLength = $this->scopeConfig->getValue('checkout/options/max_tax_exempt_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_tax_exempt_reference']['config']['maxlength'] = $maxTaxLength;
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_tax_exempt_reference']['config']['notice'] = __('(Max %1 chars)', $maxTaxLength);
            }
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
                    if($this->checkoutSession->getQuote()->getEccShipStatusErpcode() == $shipStatus->getShippingStatusCode()) {
                        $SSSelected = $this->checkoutSession->getQuote()->getEccShipStatusErpcode();
                        $SSDefaultHelp = $shipStatus->getStatusHelp();
                    }
                }

                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']['becc_ship_status_erpcode'] = [
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
                    'additionalClasses' => 'becc_ship_status_erpcode',
                    'visible' => true,
                    'sortOrder' => 256,
                    'tooltip' => ['description' => $SSDefaultHelp],
                    'id' => 'becc_ship_status_erpcode'
                ];
            }
        }

        //Require delivery date
        if ($this->commHelper->isRequiredDate()) {
            $requireDate = null;
            if ($this->checkoutSession->getQuote()->getEccRequiredDate() && $this->checkoutSession->getQuote()->getEccRequiredDate() != "0000-00-00") {
                $requireDate = date('F j, Y', strtotime($this->checkoutSession->getQuote()->getEccRequiredDate()));
                if(strtotime($this->timezone->formatDate()) > strtotime($this->checkoutSession->getQuote()->getEccRequiredDate())){
                    //$this->messageManager->addWarningMessage(__("Required Date should be greater than or equal to today."));
                    $requireDate = null;
                }
            }

            $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']
            ['after-branch-pickup-address']['children']['becc_required_date'] = [
                'component' => 'Epicor_Comm/epicor/comm/js/form/element/require-date',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'value' => $requireDate ?: "",
                    'elementTmpl' => 'ui/form/element/date',
                    'options' => [
                        "dateFormat" => "MMMM dd, yyyy",
                        "minDate" => "new date()",
                        "buttonImageOnly" => true,
                        'buttonText' => '',
                        "showOn" => 'button'
                    ],
                    'id' => 'ecc-required-date',
                    'isCart' => false,
                    'maxlength' => 50,
                ],
                'dataScope' => 'branchPickup.ecc_required_date',
                'label' => new \Magento\Framework\Phrase(__('Require Date')),
                'provider' => 'checkoutProvider',
                'additionalClasses' => 'date becc_required_date',
                'visible' => true,
                'sortOrder' => 257,
                'validationParams' => ["dateFormat" => "MMMM dd, yyyy"],
                'validation' => ["validate-date" => true, "validate-before-today-date" => true],
                'id' => 'ecc_required_date',
            ];
        }
            
            if($this->shippingdates->isShow()){
            
            $availabledates = $this->shippingdates->getAvailableDates($this->checkoutSession->getQuote());
            $datesoptions= [];
            if(count($availabledates) === 0){
                $datesoptions[] = ['value'=>$this->shippingdates->getDefaultAvailableDate(),'label'=>'Next Available Day'];
            }else{         
                foreach($availabledates as $key=>$date){
                    $datesoptions[] = ['value'=>$date,'label'=>date( 'F j, Y', strtotime($date))];
                }
            } 
            if($this->shippingdates->showAsList()) {
                $defaultValue = '';
                if(isset($datesoptions[0]['value'])){
                    $defaultValue = $datesoptions[0]['value'];
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['component']='uiComponent';
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['sortOrder']=1010;
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['displayArea']='shippingAddressAfter';
                   $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['children']['dda-block']
                       =[
                           'component' => 'Epicor_Comm/epicor/comm/js/view/checkout/shipping/dda',
                                   'displayArea'=>'shippingAddressAfter',
                           'sortOrder' => 1010,
                           'children' => [
                               'dda-form' =>[
                                   'component'=>'uiComponent',
                                   'displayArea'=>'dda-form',
                                   'children'=>[
                                       'shipping_dates' => [
                                          'component' => 'Magento_Ui/js/form/element/checkbox-set',
                                           'type'=>'radio',
                                          'config' => [
                                              'customScope' => 'shippingAddressAfter',
                                              'template' => 'ui/form/field',
                                              'value'    => $defaultValue,
                                              'template' => 'Epicor_Comm/ui/form/element/shippingdates/checkbox-set',
                                              'options' => $datesoptions,
                                              'id' => 'ecc_required_date',
                                              'sectionblock' => 'branch_shippingdates',
                                          ],
                                          'dataScope' => 'shippingAddressAfter.ecc_required_date',
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
            }else{
                
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['component']='uiComponent';
                $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['sortOrder']=1010;
                
                   $jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']['children']['after-branch-pickup-address']['children']
                   ['shippingAddressAfter']['children']['dda-block']
                       =[
                           'component' => 'Epicor_Comm/epicor/comm/js/view/checkout/shipping/dda',
                           'displayArea'=>'shippingAddressAfter',
                           'sortOrder' => 1010,
                           'children' => [
                               'dda-form' =>[
                                   'component'=>'uiComponent',
                                   'displayArea'=>'dda-form',
                                   'children'=>[
                                       'shipping_dates' => [
                                          'component' => 'Magento_Ui/js/form/element/select',
                                          'config' => [
                                              'customScope' => 'shippingAddressAfter',
                                              'template' => 'Epicor_Comm/checkout/shipping/field',
                                              'value'    => $this->checkoutSession->getQuote()->getEccRequiredDate(),
                                              'elementTmpl' => 'ui/form/element/select',
                                              'options' => $datesoptions,
                                              'sectionblock' => 'branch_shippingdates',
                                              'id' => 'ecc_required_date'
                                          ],
                                          'dataScope' => 'shippingAddressAfter.ecc_required_date',
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
        
        return $jsLayout;
    }
    
 
}