<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message;


/**
 * Customerconnect Request Message
 * 
 */
class Request extends \Epicor\Comm\Model\Message\Request
{

    protected $_currencies = array();
    protected $_deliveryLines = array();
    protected $_deliveryDetails = array();
    protected $_contacts = array();

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->_localeResolver = $localeResolver;
        $this->commonFileHelper = $context->getCommonFileHelper();
        $this->customerSession = $context->getCustomerSession();
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setAccountNumber($this->getReturnAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$this->setLanguageCode($this->getHelper()->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));
        $this->setLanguageCode($this->customerconnectMessagingHelper->getLanguageMapping($this->_localeResolver->getLocale()));
        //M1 > M2 Translation End

        $this->setLicenseType('Customer');
    }

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {
            $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // account number
            $this->addSecondaryAccountNumbers();
            if ($this->_accountNumbers) {
                $this->addDisplayOption('accounts', $this->_accountNumbers);
            }
            if ($this->getIsOrder()) {                                                    // order number  
                $this->addDisplayOption('orderNumber', $this->getOrderNumber());
            }
            if ($this->getIsShipment()) {                                                 // shipment    
                $this->addDisplayOption('shipmentNumber', $this->getShipmentNumber());
            }
            if ($this->getIsInvoice()) {                                                  // invoice   
                $this->addDisplayOption('invoiceNumber', $this->getInvoiceNumber());
                $this->addDisplayOption('type', $this->getType());
            }

            $this->addDisplayOption('languageCode', $this->getLanguageCode());

            if ($this->getIsCurrency()) {                                                 // currency code
                $currencies = array(
                    'currency' => $this->_currencies
                );
                $this->addDisplayOption('currencies', $currencies);
            }
            if ($this->getIsContact()) {
                $contacts = $this->_contacts;
                $this->addDisplayOption('contacts', $contacts);
            }
            if ($this->getIsDeliveryAvailability()) {
                $this->addDisplayOption('delivery', $this->_deliveryDetails);
            }

            $data = $this->getMessageTemplate();
            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $this->_displayData);

            $this->setOutXml($data);
            return true;
        } else {
            return 'Missing account number';
        }
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            // getVarienDataFromPath converts xml into a varien object, which can be referenced from controller
            $this->setResults($this->getResponse()->getVarienDataFromPath($this->getResultsPath()));
            return true;
        } else {
            return false;
        }
    }

    public function addCurrencyOption($fieldName, $value)
    {
        $this->_currencies[] = array(
            $fieldName => $value
        );
        return $this;
    }

    public function addContacts($delete, $code, $name, $function, $phone, $fax, $email, $login)
    {
        $this->_contacts['contact'][] = array(
            'contactCode' => $code,
            'name' => $name,
            'function' => $function,
            'telephoneNumber' => $phone,
            'faxNumber' => $fax,
            'emailAddress' => $email,
            'loginId' => $login
        );
        return $this;
    }

    public function getHelper()
    {
        if (!$this->_messaging_helper)
            $this->_messaging_helper = $this->customerconnectMessagingHelper;

        return $this->_messaging_helper;
    }

    public function addDeliveryLines($productCode, $quantity)
    {
        $this->_deliveryLines['line'][] = array(
            'productCode' => $productCode,
            'quantity' => $quantity
        );
        return $this;
    }

    public function getReturnAccountNumber()
    {
        if ($this->getMessageType() == 'CUOD') {
            if (!$this->customerSession->isLoggedIn()) {
                $returnGuestName = $this->customerSession->getReturnGuestName();
                $returnGuestEmail = $this->customerSession->getReturnGuestEmail();
                if (($returnGuestName != '' && $returnGuestName != null) &&
                    ($returnGuestEmail != '' && $returnGuestEmail != null)) {
                    $accountNumber = $this->customerSession->getReturnAccountNumber();
                    if (($accountNumber != '' && $accountNumber != null)) {
                        return $accountNumber;
                    }
                }
            }
        }
        return $this->commHelper->getErpAccountNumber();
    }
}
