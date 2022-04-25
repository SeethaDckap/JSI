<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request CDM - Configurator Details Message 
 * 
 * This message is sent from ECC to Epicor ERP after the EWA configurator has 
 * completed. The message gets the details of the configured product from E10 
 * so that it can be correctly displayed within ECC
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
 * @method setQty(string $qty)
 * @method string getQty()
 * 
 */
class Cdm extends \Epicor\Comm\Model\Message\Request
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Epicor\Common\Helper\Messaging\Cache
     */
    protected $commonMessagingCacheHelper;

    /**
     * Construct object and set message type.
     */
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;    
    
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Epicor\Common\Helper\Messaging\Cache $commonMessagingCacheHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->checkoutCart = $checkoutCart;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->commonMessagingCacheHelper = $commonMessagingCacheHelper;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $resource, $resourceCollection, $data);
        //$this->setUrl('http://paul.eccdev.dev/erpsimulator/request');
        $this->setMessageType('CDM');
        $this->setLicenseType(array('Consumer_Configurator', 'Customer_Configurator'));
        $this->setConfigBase('epicor_comm_enabled_messages/cdm_request/');
        $this->setAccountNumber($this->commHelper->getErpAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        $this->setTimeStamp(time());

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

        $erpCode = $this->getAccountNumber();

        $error = '';
        if (!$erpCode) {
            $error .= '"Missing Account Number" ';
        }

        if (!$this->getProductSku()) {
            $error .= '"Missing Product Sku" ';
        }

        if (!$this->getEwaCode() && !$this->getGroupSequence() && !$this->getTimeStamp() && !$this->getQuote()->getId()) {
            $error .= '"Missing Information" ';
        }

        if (empty($error)) {
            $this->setMessageSecondarySubject('QuoteId: ' . $helper->getPrefixedQuoteId($this) . '<br />ERP: ' . $erpCode . '<br />SKU: ' . $this->getProductSku());
            $message = $this->getMessageTemplate();
            $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
                'customer' => array(
                    'accountNumber' => $erpCode,
                    'currencyCode' => $this->getHelper()->getCurrencyMapping($this->getCurrencyCode()),
                    'languageCode' => $this->getHelper()->getLanguageMapping($this->getLanguageCode())
                ),
                'configurator' => array(
                    'unitOfMeasureCode' => $this->getProductUom(),
                    'quantity' => $this->getQty(),
                    'uniqueId' => array(
                        'eccQuoteId' => $helper->getPrefixedQuoteId($this),
                        'productCode' => $this->getProductSku(),
                        'timestamp' => $this->getTimeStamp(),
                        'ewaCode' => $this->getEwaCode(),
                        'groupSequence' => $this->getGroupSequence(),
                    )
                )
            ));
            $this->setOutXml($message);
            return true;
        } else {
            return $error;
        }
    }

    /**
     * Process the message response.
     * 
     * @return bool successful
     */
    public function processResponse()
    {
        if ($this->isSuccessfulStatusCode()) {
            $cacheHelper = $this->commonMessagingCacheHelper;
            /* @var $cacheHelper Epicor_Common_Helper_Messaging_Cache */

            $cacheKeysClear = array(
                'groupSequence' . $this->getResponse()->getConfigurator()->getGroupSequence(),
                'Ewa Code' . $this->getResponse()->getConfigurator()->getEwaCode()
            );

            $cacheHelper->cleanCache($cacheKeysClear);
        }
        return $this->isSuccessfulStatusCode();
    }

    protected function validateResponse()
    {
        $startTag = array('<description>', '<title>', '<shortDescription>');        // the nodes to have CDATA tags embedded 
        $endTag = array('</description>', '</title>', '</shortDescription>');

        $this->_xml_in = $this->commHelper->addCdataTags($this->_xml_in, '<configurator>', $startTag, $endTag);  // '<configurator>' is the block containing the tags required  

        parent::validateResponse();
    }

}
