<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Configurator extends \Epicor\Comm\Helper\Data
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\CdmFactory
     */
    protected $commMessageRequestCdmFactory;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

     protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;    
    
     
    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory,
        \Epicor\Comm\Helper\Product $commProductHelper
    ) {
        $this->commMessageRequestCdmFactory = $commMessageRequestCdmFactory;
        $this->commProductHelper = $commProductHelper;
        $this->messageManager = $context->getMessageManager();
        $this->logger = $context->getLogger();
        $this->_storeManager = $context->getStoreManager();
        $this->customerSession = $context->getCustomerSession();
        $this->commCustomerErpaccountFactory = $context->getCommCustomerErpaccountFactory();
        parent::__construct($context);
    }
    /**
     * Returns the if the said field should be displayed
     * @param type $field
     * @return type
     */
    public function getEwaDisplay($field)
    {
        $ewaDisplay = $this->scopeConfig->getValue('Epicor_Comm/ewa_options/ewa_display', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $values = explode(',', $ewaDisplay);

        return in_array($field, $values);
    }

    /**
     * Handling Ewa Sorting in Order/SOU Email
     * @return array()
     */

    public function getEwaOptions($itemOptions)
    {
        $productOptions = $itemOptions;
        if($productOptions){
            $newoptionsOrder = $this->scopeConfig->getValue('Epicor_Comm/ewa_options/cart_display_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $newoptionsOrder = $newoptionsOrder ? unserialize($newoptionsOrder) : null;
            $newOptionByTypeOrder = array();

            foreach($newoptionsOrder as $key => $option){
                $newOptionByTypeOrder[$option['ewacartsortorder']] = $option['ewacartsortorder'] ;
            }
            $optionByTypeOrder = array();
            foreach($productOptions as $option2){
                if(isset($option2['option_type'])){
                    if(substr($option2['option_type'], 0, 4) == 'ewa_') {
                        $optionByTypeOrder[$option2['option_type']] = $option2 ;
                    }
                }
            }
            if(!empty($optionByTypeOrder)) {
                $newSortOrderForItems = array_replace($newOptionByTypeOrder, $optionByTypeOrder);
                $newOptions = array();

                $optionsSelected =  array_flip(explode(',', $this->scopeConfig->getValue('Epicor_Comm/ewa_options/ewa_display', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
                    $optionsSelected['ewa_code']=-1;


                if(isset($optionsSelected['base_product_description'])){
                    unset($optionsSelected['base_product_description']);
                    $optionsSelected['base_description'] = 0;
                }
                $requiredOptions = array_intersect_key($newSortOrderForItems, $optionsSelected);
                $requiredOptionsInSortOrder = array_intersect_key($newSortOrderForItems, $requiredOptions);
                foreach($requiredOptionsInSortOrder as $newOrder){
                    $newOptions[] = $newOrder;
                }
                return $newOptions;
            } else {
               return  $productOptions;
            }
        }
        return $productOptions;
    }

    /**
     *
     * @param int $productId
     * @param string $code
     * @param string $isEwaCode ewa_code/group_sequence
     * @return \Epicor\Comm\Model\Message\Request\Cim
     */
    public function sendCim($productId, $cimData)
    {
        $product = $this->catalogProductFactory->create()->load($productId);

        $cimData['product_sku'] = $product->getSku();
        $cimData['product_uom'] = $product->getEccUom();

        if (!isset($cimData['ewa_code']) || empty($cimData['ewa_code'])) {
            $cimData['timestamp'] = $product->getEccUom();
        }

        $helper = $this->commMessagingHelper;
        $response = $helper->sendErpMessage('epicor_comm', 'cim', $cimData);
        $cim = $response['message'];
        /* @var $cim \Epicor\Comm\Model\Message\Request\Cim */

        return $cim;
    }

    /**
     * Reorder Configurator Product
     *
     * @param int $productId
     * @param string $groupSequence
     * @return string Redirect Url
     */
    public function reorderProduct($productId, $groupSequence, $qty = 1)
    {

        $url = '';

        $cimData = array(
            'group_sequence' => $groupSequence,
        );

        $cim = $this->sendCim($productId, $cimData);

        if ($cim->isSuccessfulStatusCode()) {
            $configurator = $cim->getResponse()->getConfigurator();
            $productSku = $configurator->getProductCode();
            $ewaCode = $configurator->getRelatedToRowId();
            $url = $this->addProductToBasket($productSku, $ewaCode, true, $qty);
        }

        return $url;
    }

    /**
     * Add Configurator Product to Basket
     *
     * @param string $productSku
     * @param string $ewaCode
     * @return string Redirect Url
     * @throws \Exception
     */
    public function addProductToBasket($productSku, $ewaCode, $silent = false, $qty = 1, $locationCode = '', $itemId = false)
    {
        try {
            $url = '';

            $product = $this->_initProduct($productSku);
            /* @var $product Epicor_Comm_Model_Product */

            if ($product && $product->getEccConfigurator()) {

                if (!$silent && is_null($this->registry->registry('send_msq')))
                    $this->registry->register('send_msq', true, true);

                $cart = $this->checkoutCart;
                // Send Cdm for Product with EWACode Attribute

                $cdm = $this->commMessageRequestCdmFactory->create();
                /* @var $cdm Epicor_Comm_Model_Message_Request_Cdm */

                $cdm->setProductSku($product->getSku());
                $cdm->setProductUom($product->getEccUom());
                $cdm->setTimeStamp(null);
                $cdm->setQty($qty);
                $cdm->setEwaCode($ewaCode);
                $cdm->setItemId($itemId);
                if ($cdm->sendMessage()) {
                    $configurator = $cdm->getResponse()->getConfigurator();

                    $ewaTitle = $configurator->getTitle();
                    $ewaShortDescription = $configurator->getShortDescription();
                    $ewaDescription = $configurator->getDescription();
                    $ewaSku = $configurator->getConfiguredProductCode();
                    $productCurrency = $configurator->getCurrencies()->getCurrency();
                    $qty = $configurator->getQuantity() ?: $qty;



                    $basePrice = $productCurrency->getBasePrice();
                    $customerPrice = $productCurrency->getCustomerPrice();

                    $product->unsFinalPrice();

                    $customerPriceUsed = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/cusomterpriceused', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    // Set prices
                    if ($customerPriceUsed || $product->getTypeId() == 'bundle') {
                        // NOTe Bundle products cannot have special prices like other products as it's expecting a percentage, not a price!
                        $product->setPrice($customerPrice);
                    } else {
                        $product->setPrice($basePrice);
                        $product->setSpecialPrice($customerPrice);
                    }

                    $product->setFinalPrice($customerPrice);
                    $product->setMinimalPrice($customerPrice);
                    $product->setMinPrice($customerPrice);
                    $product->setCustomerPrice($customerPrice);

                    $set = false;
                    if (!$this->registry->registry('msq-processing')) {
                        $this->registry->register('msq-processing', true);
                        $set = true;
                    }
                    $product->getFinalPrice();
                    if ($set) {
                        $this->registry->unregister('msq-processing');
                    }
                } else {
                    throw new \Exception(__('Failed to retrieve configured details.'));
                }

                /* @var $cart Mage_Checkout_Model_Cart */
                $quote = $this->checkoutSession->getQuote();
                /* @var $quote \Epicor\Comm\Model\Quote */
                $cart->setQuote($quote);

                $ewaCodeOptionId = 0;
                $ewaDescOptionId = 0;
                $ewaShortDescOptionId = 0;
                $ewaTitleOptionId = 0;
                $ewaSkuOptionId = 0;
                // locate the configurator option
                foreach ($product->getOptions() as $option) {
                    /* @var $option Mage_Catalog_Model_Product_Option */
                    if ($option->getType() == 'ewa_code') {
                        $ewaCodeOptionId = $option->getId();
                    } else if ($option->getType() == 'ewa_description') {
                        $ewaDescOptionId = $option->getId();
                    } else if ($option->getType() == 'ewa_short_description') {
                        $ewaShortDescOptionId = $option->getId();
                    } else if ($option->getType() == 'ewa_title') {
                        $ewaTitleOptionId = $option->getId();
                    } else if ($option->getType() == 'ewa_sku') {
                        $ewaSkuOptionId = $option->getId();
                    }
                }

                $options = array(
                    $ewaCodeOptionId => $ewaCode,
                    $ewaDescOptionId => $ewaDescription,
                    $ewaShortDescOptionId => $ewaShortDescription,
                    $ewaTitleOptionId => $ewaTitle,
                    $ewaSkuOptionId => $ewaSku
                );
                $product->setHasOptions(1);
                $item = $this->_productFromBasket($product, $ewaCode);
                if ($item === false) {
                    $quoteOptions = array(
                        'qty' => $qty,
                        'force_price' => $product->getFinalPrice(),
                        'custom_options' => $options,
                        'location_code' => $locationCode
                    );
                    $quote->addLine($product, $quoteOptions);
                } else {

                    $item->setEccBsvPrice(null);
                    $item->setEccBsvPriceInc(null);
                    $item->setEccBsvLineValue(null);
                    $item->setEccBsvLineValueInc(null);
                    $_fromCurr = $quote->getBaseCurrencyCode();
                    $_toCurr = $this->storeManager->getStore()->getCurrentCurrencyCode();
                    $item->setOriginalCustomPrice($this->directoryHelper->currencyConvert($item->getProduct()->getFinalPrice($item->getQty()), $_fromCurr, $_toCurr));
                    $this->updateCartProductCustomOptions($item, $options, $qty);
                    //M1 > M2 Translation Begin (Rule 55)
                    //$this->messageManager->addSuccess($this->__('%s was updated in your shopping cart', $product->getName()));
                    $this->messageManager->addSuccess(__('%1 was updated in your shopping cart', $product->getName()));
                    //M1 > M2 Translation End

                    $sessionItems = $this->customerSessionFactory->create()->getCartMsqRegistry();
                    if (isset($sessionItems[$item->getId()])) {
                        unset($sessionItems[$item->getId()]);
                    }

                    $this->customerSessionFactory->create()->setCartMsqRegistry($sessionItems);
                }
                $this->checkoutSession->setCartWasUpdated(true);
                $this->customerSessionFactory->create()->setBsvTriggerTotals(array());
                $cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
                $cart->save();
                $cart->getQuote()->setTriggerRecollect(1);

                // remove product from configure products list
                $helper = $this->commProductHelper;
                /* @var $helper Epicor_Comm_Helper_Product */

                $helper->removeConfigureListProduct($product->getId());

                if ($item === false) {
                    $this->_eventManager->dispatch('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->request, 'response' => $this->response));
                    //M1 > M2 Translation Begin (Rule 55)
                    //$message = $this->__('%s was added to your shopping cart.', $this->escapeHtml($product->getName()));
                    if (!$cart->getQuote()->getHasError()) {
                        $message = __('%1 was added to your shopping cart.', $product->getName());
                        //M1 > M2 Translation End
                        $this->messageManager->addSuccess($message);
                    }
                }

                //M1 > M2 Translation Begin (Rule p2-4)
                //$url = $this->scopeConfig->getValue('checkout/cart/redirect_to_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? Mage::getUrl('checkout/cart') : '';
                $url = $this->scopeConfig->getValue('checkout/cart/redirect_to_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->_getUrl('checkout/cart') : '';
                //M1 > M2 Translation End
            } else {
                $this->messageManager->addError(__('Error Occured while adding this Configurator Product (Error - 1000)'));
            }
        } catch (\Exception $e) {
            // store the error in the session here
            $this->messageManager->addExceptionMessage($e, __('Can not add item to shopping cart'));
            $this->logger->debug('Configurator add to basket Error - ' . $e->getMessage());
        }

        return $url;
    }

    /**
     * Checks if the Configurator product is not already in the basket
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param string $ewaCode
     * @return \Magento\Quote\Model\Quote\Item
     */
    protected function _productFromBasket($product, $ewaCode)
    {
        $basketItem = false;

        /* @var $cart Mage_Checkout_Model_Cart */
        $quote = $this->checkoutSession->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        foreach ($quote->getAllItems() as $item) {
//            if ($basketItem !== false) {
//                break;
//            }
            /* @var $item Mage_Sales_Model_Quote_Item */
            if ($item->getProductId() == $product->getId()) {
                foreach ($item->getOptions() as $option) {
                  //  echo $option->getValue() .'=='.$option->getCode().'  =='. $ewaCode.'<br>';
                    if ($option->getValue() == $ewaCode) {
                        $basketItem = $item;
                        break 2;
                    }
                }
            }
        }

        return $basketItem;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Epicor\Comm\Model\Product || false
     */
    protected function _initProduct($sku)
    {

        if ($sku) {

            $product = $this->catalogProductFactory->create();
            $product->setStoreId($this->storeManager->getStore()->getId())
                ->load($product->getIdBySku($sku));

            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Checks the basket for configurator products, anc checks if you're licensed for them
     * If not, it removes them
     *
     * @param \Epicor\Comm\Model\Quote $quote
     */
    public function removeUnlicensedConfiguratorProducts($quote, $collect = true)
    {
        $removed = false;
        $customer = $this->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        $items = $quote->getItemsCollection();
        /* @var $items \Magento\Quote\Model\ResourceModel\Quote\Item\Collection */
        foreach ($items as $key => $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $remove = false;
           // $product = $this->catalogProductFactory->create()->load($item->getProduct()->getId());
            $product = $item->getProduct();
            if ($product && $product->getEccConfigurator()) {
                if ($customer->isGuest()) {
                    if (!$this->isLicensedFor(array('Consumer_Configurator'))) {
                        $remove = true;
                    }
                } else if ($customer->isCustomer()) {
                    if (!$this->isLicensedFor(array('Customer_Configurator'))) {
                        $remove = true;
                    }
                } else {
                    $remove = true;
                }

                if ($remove) {
                    $removed = true;
                    if (!$item->getItemId()) {
                        $quote->getItemsCollection()->removeItemByKey($key);
                    } else {
                        $quote->removeItem($item->getItemId());
                    }
                }
            }
        }

        if ($removed) {
            $quote->getShippingAddress()->unsetData('cached_items_all');
            $quote->getShippingAddress()->unsetData('cached_items_nominal');
            $quote->getShippingAddress()->unsetData('cached_items_nonnominal');

            $quote->getShippingAddress()->getAllItems();
            if ($collect) {
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
            }
        }

        return $removed;
    }

    /**
     * Changes de JSON Encoded Address to the CIM format
     * @param string $addressEncoded
     * @return array
     */
    public function getDeliveryAddressFromRFQ($addressEncoded)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        $addressDecoded = json_decode(html_entity_decode($addressEncoded), true);
        $deliveryAddress = $this->dataObjectFactory->create((array) $addressDecoded);

        return array(
            'addressCode' => $helper->stripNonPrintableChars($deliveryAddress->getAddressCode()),
            'name' => $helper->stripNonPrintableChars($deliveryAddress->getContactName()),
            'companyName' => $helper->stripNonPrintableChars($deliveryAddress->getCompany() ?: $deliveryAddress->getName()),
            //M1 > M2 Translation Begin (Rule 9)
            //'address1' => $helper->stripNonPrintableChars($deliveryAddress->getAddress1()),
            //'address2' => $helper->stripNonPrintableChars($deliveryAddress->getAddress2()),
            //'address3' => $helper->stripNonPrintableChars($deliveryAddress->getAddress3()),
            'address1' => $helper->stripNonPrintableChars($deliveryAddress->getData('address1')),
            'address2' => $helper->stripNonPrintableChars($deliveryAddress->getData('address2')),
            'address3' => $helper->stripNonPrintableChars($deliveryAddress->getData('address3')),
            //M1 > M2 Translation End
            'city' => $helper->stripNonPrintableChars($deliveryAddress->getCity()),
            'county' => $helper->stripNonPrintableChars($deliveryAddress->getCounty()),
            'country' => $helper->stripNonPrintableChars($deliveryAddress->getCountry()),
            'postcode' => $helper->stripNonPrintableChars($deliveryAddress->getPostcode()),
            'emailAddress' => $helper->stripNonPrintableChars($deliveryAddress->getEmail()),
            'telephoneNumber' => $helper->stripNonPrintableChars($deliveryAddress->getTelephone()),
            'mobileNumber' => $helper->stripNonPrintableChars($deliveryAddress->getMobileNumber()),
            'faxNumber' => $helper->stripNonPrintableChars($deliveryAddress->getFax())
        );
    }

    /**
     * Returns the Quote Id with the corresponding prefix
     * @return string
     */
    public function getPrefixedQuoteId($message)
    {
        if (!$message->hasData('prefixed_quote_id')) {
            if ($message->getQuoteId()) {
                $prefix = $this->scopeConfig->getValue('epicor_comm_enabled_messages/cim_request/eccquoteid_prefix_rfq', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $quoteId = $message->getQuoteId();
                $lineNumber = $message->getLineNumber() ?: 1;
            } else {
                $prefix = $this->scopeConfig->getValue('epicor_comm_enabled_messages/cim_request/eccquoteid_prefix_basket', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $quoteId = $message->getQuote()->getId();
                $lineNumber = $this->getQuoteLineNumber($message->getQuote(), $message->getItemId());
            }

            $prefixedQuoteId = $prefix . $quoteId . '-' . $lineNumber;
            $message->setPrefixedQuoteId($prefixedQuoteId);
        }

        return $message->getData('prefixed_quote_id');
    }

    public function getQuoteLineNumber($quote, $itemId)
    {
        $lineNumber = 1;
        foreach ($quote->getAllItems() as $item) {
            if ($item->getId() == $itemId) {
                break;
            }
            $lineNumber++;
        }
        return $lineNumber;
    }

    public function getCurrentStoreId() {
        return $this->_storeManager->getStore()->getStoreId();
    }

    public function checkConfiguratorProductLicensed()
    {
        $valid = true;

        if (!$this->customerSession->isLoggedIn()) {
           $erpAccountId = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
           $getAccountType = $erpAccount->getAccountType();
           if(($getAccountType =="B2C") && (!$this->isLicensedFor(array('Consumer_Configurator')))) {
               $valid = false;
           } else if(($getAccountType =="B2B") && (!$this->isLicensedFor(array('Customer_Configurator')))) {
               $valid = false;
           }
        } else {
            $customer = $this->getCustomer();

            if ($customer->isGuest()) {
                if (!$this->isLicensedFor(array('Consumer_Configurator'))) {
                    $valid = false;
                }
            } else if ($customer->isCustomer()) {
                if (!$this->isLicensedFor(array('Customer_Configurator'))) {
                    $valid = false;
                }
            } else if ($customer->isSalesRep() && !$customer->isGuest() && !$customer->isCustomer()) {
                if (!$this->isLicensedFor(array('Consumer_Configurator'))) {
                    $valid = false;
                }
            }
        }

        return $valid;
    }

}
