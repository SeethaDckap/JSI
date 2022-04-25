<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Cart;


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

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * LayoutProcessor checkout Cart.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory
     * @param \Epicor\Comm\Model\Checkout\Dates $shippingdates
     * @param \Magento\Customer\Model\AddressFactory $customerAddressFactory
     * @param \Magento\Customer\Model\Session $session
     * @param \Epicor\Common\Helper\Context $commonContext
     */
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
        $this->commonContext = $commonContext;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->timezone = $commonContext->getTimezone();
        $this->messageManager = $commonContext->getMessageManager();
    }

    /**
     * AfterProcess checkout Cart.
     *
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Cart\LayoutProcessor $subject,
        array  $jsLayout
    ) {
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

            $jsLayout['components']['block-summary']['children']['ecc_required_date'] = [
                'component' => 'Epicor_Comm/epicor/comm/js/form/element/require-date',
                'config' => [
                    'template' => 'ui/form/field',
                    'value' => $requireDate ?: "",
                    'elementTmpl' => 'ui/form/element/date',
                    'outputDateFormat' => 'YYYY-MM-dd',
                    'options' => [
                        "dateFormat" => "MMMM dd, yyyy",
                        "minDate" => "new date()",
                        //"buttonImageOnly" => true,
                        'buttonText' => '',
                        "showOn" => 'both'
                    ],
                    'isCart'=>true,
                    'id' => 'ecc-required-date'
                ],
                'dataScope' => 'shippingAddress.ecc_required_date',
                'label' => new \Magento\Framework\Phrase(__('Require Date')),
                'provider' => 'checkoutProvider',
                'additionalClasses' => 'date ecc_shipping_berore_field',
                'visible' => true,
                'sortOrder' => 257,
                'validationParams' => ["dateFormat" => "MMMM dd, yyyy"],
                'validation' => ["validate-date" => true, "validate-before-today-date" => true],
                'id' => 'ecc_required_date',
            ];
        }
        return $jsLayout;
    }
}