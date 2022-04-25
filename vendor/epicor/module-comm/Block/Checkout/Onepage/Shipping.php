<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Onepage;


class Shipping extends \Magento\Checkout\Block\Onepage
{

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
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;
    
    /*
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->commonHelper = $commonHelper;
        $this->commHelper = $commHelper;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
    }
    public function getAddressesHtmlSelect($type)
    {

        $html = '';

        if ($this->isCustomerLoggedIn()) {
            $options = array();

            $addresses = array();
            $loadAddresses = true;
            $aType = ($type == 'billing') ? 'invoice' : 'delivery';
            $transportObject = $this->dataObjectFactory->create();
            $transportObject->setAddresses($addresses);
            $transportObject->setLoadAddresses($loadAddresses);
            $this->eventManager->dispatch('epicor_comm_onepage_get_checkout_addresses', array('quote' => $this->checkoutSession->getQuote(), 'type' => $aType, 'restrict_by_type' => $this->restrictAddressTypes(), 'addresses' => $transportObject));
            $addresses = $transportObject->getAddresses();
            $loadAddresses = $transportObject->getLoadAddresses();

            if ($loadAddresses) {
                $addresses = ($this->restrictAddressTypes()) ? $this->getCustomer()->getAddressesByType($aType) : $this->getCustomer()->getAddresses();
            }

            $fastFormat = $this->scopeConfig->isSetFlag('customer/address_templates/checkout_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            foreach ($addresses as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $fastFormat ? $this->fastFormat($address) : $address->format('oneline'),
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();

            if (empty($addressId)) {
                if ($this->isMasquerading()) {
                    $addCode = $this->getAddress()->getEccErpAddressCode();
                    $helper = $this->commHelper;
                    /* @var $helper Epicor_Comm_Helper_Data */
                    $erpAccount = $helper->getErpAccountInfo();
                    if ($erpAccount && $addCode) {
                        $addressColl = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
                        /* @var $addressColl Epicor_Comm_Model_Resource_Customer_Erpaccount_Address_Collection */
                        $addressColl->addFieldToFilter('erp_code', $addCode);
                        $addressColl->addFieldToFilter('erp_customer_group_code', $erpAccount->getErpCode());
                        $address = $addressColl->getFirstItem();
                        $addressId = 'erpaddress_' . $address->getId();
                    }
                } else {
                    if ($type == 'billing') {
                        $address = $this->getCustomer()->getPrimaryBillingAddress();
                    } else {
                        $address = $this->getCustomer()->getPrimaryShippingAddress();
                    }

                    if ($address) {
                        $addressId = $address->getId();
                    }
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

            $html = $select->getHtml();
        }

        return $html;
    }

    public function restrictAddressTypes()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
        $quote = $this->checkoutSession->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */

        $contact = $quote->getEccSalesrepChosenCustomerId() . $quote->getEccSalesrepChosenCustomerInfo();
        return false; //$this->getCustomer()->isSalesRep() && $this->isMasquerading() && !empty($contact);
    }

    public function getAddress()
    {
        $address = parent::getAddress();
        $this->eventManager->dispatch('epicor_comm_onepage_shipping_get_address', array('quote' => $this->checkoutSession->getQuote(), 'address' => $address));
        return $address;
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
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        if ($helper->isMasquerading() == false) {
            $addressTxt .= $address->getCustomer()->getName() . ', ';
        }

        $addressTxt .= ($address->getCompany()) ? $address->getCompany() : '';
        $addressTxt .= ', ' . $address->getStreet1();
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
