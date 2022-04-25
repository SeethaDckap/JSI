<?php

namespace Epicor\UDExample\Plugin\Block;

class LayoutProcessor {
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
    protected $_session;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Customer\Model\AddressFactory $customerAddressFactory, \Magento\Customer\Model\Session $session
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->checkoutSession = $checkoutSession;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->_session = $session;
    }

    public function afterProcess(
    \Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout
    ) {
        $checkConfig = $this->scopeConfig->getValue('udexample_settings/display_conformance_checkout/display_conformance', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($checkConfig) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children']['before-place-order']['children']['conformance'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'billingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/checkbox',
                    'id' => 'conformance',
                    'options' => []
                ],
                'dataScope' => 'orderconformance.conformance',
                'label' => '',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'sortOrder' => 300,
                'id' => 'conformance',
                'description' => __('Certificate of Conformance')
            ];
        }
        return $jsLayout;
    }

}
