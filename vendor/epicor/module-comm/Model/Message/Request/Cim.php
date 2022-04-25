<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request CIM - Configurator Initiation Message 
 * 
 * This message is sent from ECC to Epicor ERP before the EWA configurator is called. 
 * The message sets the state within Epicor ERP and EWA and returns the information 
 * required to authenticate the ECC access to Epicor ERP
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setAccountNumber(string $erpAccountCode)
 * @method string getAccountNumber()
 * @method setCurrencyCode(string $currencyCode)
 * @method string getCurrencyCode()
 * @method setQuote(Mage_Sales_Model_Quote $currencyCode)
 * @method Mage_Sales_Model_Quote getQuote()
 * @method setProductSku(string $sku)
 * @method string getProductSku()
 * @method setProductUom(string $uom)
 * @method string getProductUom()
 * @method setTimeStamp(int $timestamp)
 * @method int getTimeStamp()
 * @method setEwaCode(string $ewaCode)
 * @method string getEwaCode()
 * @method setGroupSequence(string $groupSequence)
 * @method string getGroupSequence()
 * 
 */
class Cim extends \Epicor\Comm\Model\Message\Request
{

    protected $_deliveryAddress = null;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * Construct object and set message type.
     */

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;    
    
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->checkoutCart = $checkoutCart;
        $this->customerFactory = $customerFactory;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $resource, $resourceCollection, $data);
//        $this->setUrl('http://paul.eccdev.dev/erpsimulator/request');
        $this->setMessageType('CIM');
        $this->setLicenseType(array('Consumer_Configurator', 'Customer_Configurator'));
        $this->setConfigBase('epicor_comm_enabled_messages/cim_request/');        
        $this->setAccountNumber($this->commHelper->getErpAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        $this->setTimeStamp(str_replace('.', '', microtime(true)));

        $cart = $this->checkoutCart;
        /* @var $cart Mage_Checkout_Model_Cart */
        $quote = $cart->getQuote();
        if (!$quote->getId()) {
            $this->registry->register('configurator-no-bsv', true);
            $cart->saveQuote();
        }

        $this->setQuote($quote);
    }
    /**
     * Bulds the XML request from the set data on this message.
     * @return bool successful message.
     */
    public function buildRequest()
    {
        $helper = $this->commConfiguratorHelper;
        /* @var $helper Epicor_Comm_Helper_Configurator */
        /* @var $helper Epicor_Comm_Helper_Configurator */
        $checkLicensed = $helper->checkConfiguratorProductLicensed(); 
        
        $erpCode = $this->getAccountNumber();
        if ($erpCode && $this->getProductSku() && $checkLicensed) {
            $this->setMessageSecondarySubject('QuoteId: ' . $helper->getPrefixedQuoteId($this) . '<br />ERP: ' . $erpCode . '<br />SKU: ' . $this->getProductSku());
            $message = $this->getMessageTemplate();
            $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
                'customer' => array(
                    'accountNumber' => $erpCode,
                    'currencyCode' => $this->getHelper()->getCurrencyMapping($this->getCurrencyCode()),
                    'deliveryAddress' => $this->getDeliveryAddress()
                ),
                'configurator' => array(
                    '_attributes' => array(
                        'action' => $this->getAction()
                    ),
                    'uniqueId' => array(
                        'eccQuoteId' => $helper->getPrefixedQuoteId($this),
                        'productCode' => $this->getProductSku(),
                        'productCategory' => $this->getProductCategory()
                            ?: null,
                        'timestamp' => $this->getTimeStamp(),
                        'ewaCode' => $this->getEwaCode(),
                        'groupSequence' => $this->getGroupSequence()
                    ),
                    'unitOfMeasureCode' => $this->getProductUom()
                )
            ));
           //echo '<pre>'; print_r($message); exit;
            $this->setOutXml($message);
            return true;
        } else {
            $error = '';
            if (!$erpCode)
                $error .= '"Missing Account Number" ';
            if (!$this->getProductSku())
                $error .= '"Missing Product Sku" ';
            
            if(!$checkLicensed) {
                $error .= '"License key not valid for this feature" ';
            }            

            return $error;
        }
    }

    private function getDeliveryAddress()
    {
        if (!$this->getData('delivery_address')) {
            $helper = $this->getHelper();
            $shippingAddress = $this->getQuote()->getShippingAddress();
            $shippingAddressCode = $helper->getErpAddress($this->getQuote()->getShippingAddress()->getCustomerAddressId(), $this->getAccountNumber(true));

            $this->setDeliveryAddress(array(
                'addressCode' => $shippingAddressCode->getEccErpAddressCode(),
                'name' => $helper->stripNonPrintableChars($shippingAddress->getName()),
                'companyName' => $helper->stripNonPrintableChars($shippingAddress->getCompany()),
                'address1' => $helper->stripNonPrintableChars($shippingAddress->getStreet1()),
                'address2' => $helper->stripNonPrintableChars($shippingAddress->getStreet2()),
                'address3' => $helper->stripNonPrintableChars($shippingAddress->getStreet3()),
                'city' => $helper->stripNonPrintableChars($shippingAddress->getCity()),
                'county' => $helper->stripNonPrintableChars($shippingAddress->getRegion()),
                'country' => $helper->getErpCountryCode($shippingAddress->getCountry_id()),
                'postcode' => $helper->stripNonPrintableChars($shippingAddress->getPostcode()),
                'emailAddress' => $helper->stripNonPrintableChars($shippingAddress->getEmail()),
                'telephoneNumber' => $helper->stripNonPrintableChars($shippingAddress->getTelephone()),
                'mobileNumber' => $helper->stripNonPrintableChars($shippingAddress->getEccMobileNumber()),
                'faxNumber' => $helper->stripNonPrintableChars($shippingAddress->getFax()),
            ));
        }

        return $this->getData('delivery_address');
    }

    /**
     * Process the message response.
     * 
     * @return bool successful
     */
    public function processResponse()
    {
        return $this->isSuccessfulStatusCode();
    }

}
