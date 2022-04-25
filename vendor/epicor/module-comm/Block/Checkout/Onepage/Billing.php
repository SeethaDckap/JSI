<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Onepage;


class Billing extends \Magento\Checkout\Block\Onepage
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commHelper = $commHelper;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commonHelper = $commonHelper;
    }
    public function _construct()
    {
        $this->setAddressType('noaddresses');   // allows guests and new accounts with no addresses to checkout
        parent::_construct();
    }

    public function getAddressesHtmlSelect($type)
    {
        $html = '';
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            $addresses = array();
            $loadAddresses = true;
            $aType = ($type == 'billing') ? 'invoice' : 'delivery';

            $helper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */

            $this->registry->register('billing_address_checkout', true);

            $transportObject = $this->dataObjectFactory->create();
            $transportObject->setAddresses($addresses);
            $transportObject->setLoadAddresses($loadAddresses);
            $this->eventManager->dispatch('epicor_comm_onepage_get_checkout_addresses', array('quote' => $this->checkoutSession->getQuote(), 'type' => $aType, 'restrict_by_type' => $this->restrictAddressTypes(), 'addresses' => $transportObject));
            $addresses = $transportObject->getAddresses();
            $loadAddresses = $transportObject->getLoadAddresses();

            if ($loadAddresses) {
                $addresses = ($this->restrictAddressTypes()) ? $this->getCustomer()->getAddressesByType($aType) : $this->getCustomer()->getAddresses();
            }

            $this->setForcedAddressTypes($this->restrictAddressTypes());

            $fastFormat = $this->scopeConfig->isSetFlag('customer/address_templates/checkout_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            foreach ($addresses as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $fastFormat ? $this->fastFormat($address) : $address->format('oneline'),
                    'params' => array(
                        'data-iscustom' => $this->restrictAddressTypes() ? $address->getIsCustom() : 1
                    )
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();

            if (empty($addressId)) {
                if ($type == 'billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }
            $select = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
                ->setName($type . '_address_id')
                ->setId($type . '-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="' . $type . '.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            if ($this->canAddNew()) {
                $select->addOption('', __('New Address'));
            }

            $this->registry->unregister('billing_address_checkout');

            $html = $select->getHtml();
        }

        return $html;
    }

    public function restrictAddressTypes()
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        $force = $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($force == false && $helper->contractsEnabled()) {
            $quote = $this->checkoutSession->getQuote();
            /* @var $quote Epicor_Comm_Model_Quote */

            $contracts = $helper->getQuoteContracts($quote);
            if (empty($contracts) == false) {
                $force = true;
            }
        }

        return $force;
    }

    public function canAddNew()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->customerAddressPermissionCheck('create');
    }

    public function isMasquerading()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->isMasquerading();
    }

    public function hideNameFields()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        return $this->getCustomer()->isCustomer() || $helper->isMasquerading();
    }

    public function isPOMandatory()
    {

        $mandatory = false;
        $flag = $this->scopeConfig->isSetFlag('checkout/options/po_mandatory', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $helper->getErpAccountInfo();
        $po = $erpAccount->getPoMandatory();

        if ($po == 1) {
            $mandatory = true;
        } else if ($po == null && $flag) {
            $mandatory = true;
        }

        return $mandatory;
    }

    public function displayEmail()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayMobilePhone()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_mobile_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayInstructions()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Formats an address based on a static format ratehr than configurable
     * 
     * @param \Magento\Customer\Model\Address $address
     */
    protected function fastFormat($address)
    {
        //
        //{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}
        //{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}, {{var street}}, {{var city}}, {{var region}}
        //{{var postcode}}, {{var country}}
        //

        $addressTxt = '';

        if ($this->getCustomer()->isGuest()) {
            $addressTxt .= $address->getCustomer()->getName() . ', ';
        }

        $addressTxt .= ($address->getCompany()) ? $address->getCompany() . ', ' : '';
        $addressTxt .= ' ' . $address->getStreet1();
        $addressTxt .= ($address->getStreet2()) ? ', ' . $address->getStreet2() : '';
        $addressTxt .= ($address->getStreet3()) ? ', ' . $address->getStreet3() : '';
        $addressTxt .= ($address->getStreet4()) ? ', ' . $address->getStreet4() : '';
        $addressTxt .= ($address->getCity()) ? ', ' . $address->getCity() : '';
        $addressTxt .= ($address->getRegion()) ? ', ' . $address->getRegion() : '';
        $addressTxt .= ($address->getPostcode()) ? ' ' . $address->getPostcode() : '';
        $addressTxt .= ($address->getCountry()) ? ', ' . $address->getCountryModel()->getName() : '';

        return $addressTxt;
    }

}
