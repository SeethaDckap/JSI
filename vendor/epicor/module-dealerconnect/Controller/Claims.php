<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller;


/**
 * RFQs controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Claims extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqd
     */
    protected $customerconnectMessageRequestCrqd;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CdmFactory
     */
    protected $commMessageRequestCdmFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;
    
    protected $urlDecoder;
    
    protected $encryptor;
    
    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Deis
     */
    protected $dcMessageRequestDeid;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Dealerconnect\Model\Message\Request\Dcld $dealerconnectMessageRequestDcld,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid
    )
    {
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
        $this->registry = $registry;
        $this->dealerconnectHelper = $dealerconnectHelper;
        $this->request = $request;
        $this->dealerconnectMessageRequestDcld = $dealerconnectMessageRequestDcld;
        $this->generic = $generic;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->storeManager = $storeManager;
        $this->commMessageRequestCdmFactory = $commMessageRequestCdmFactory;
        $this->scopeConfig = $scopeConfig;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        $this->dcMessageRequestDeid = $dcMessageRequestDeid;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    protected function _loadClaim()
    {
        $newClaim = $this->customerSession->getNewClaim();

        $loaded = false;

        if ($newClaim) {
            $this->registry->register('dealer_connect_claim_details', $newClaim);
            $this->customerSession->unsNewClaim();
            $loaded = true;
        }

        $helper = $this->dealerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        if (!$this->registry->registry('dealer_connect_claim_details')) {
            $claim = $this->urlDecoder->decode($this->request->getParam('claim'));
            $claimDetails = unserialize($this->encryptor->decrypt($claim));
//            if (isset($quoteDetails['return'])) {
//                $this->registry->register('rfq_return_url', $quoteDetails['return']);
//                unset($quoteDetails['return']);
//            }
            if ($claimDetails['erp_account'] == $erpAccountNumber && !empty($claimDetails['case_number'])) {
                $dcld = $this->dealerconnectMessageRequestDcld;
                $messageTypeCheck = $dcld->getHelper()->getMessageType('DCLD');
                if ($dcld->isActive() && $messageTypeCheck) {
                    $dcld->setAccountNumber($erpAccountNumber)
                        ->setCaseNumber($claimDetails['case_number'])
                        ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
                    if ($dcld->sendMessage()) {
                        $_claim = $dcld->getResults();
                        $status = '';
                        if (isset($claimDetails['case_status'])) {
                            $status = $claimDetails['case_status'];
                        }
                        $_claim->setStatus($status);
                        $this->setClaimInventory($erpAccountNumber, $_claim);
                        $this->registry->register('dealer_connect_claim_details', $_claim);
                        $loaded = true;
                    } else {
                        $this->messageManager->addErrorMessage(__('Failed to retrieve Claim Details'));
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('ERROR - Claim Details not available'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('ERROR - Invalid Claim Number'));
            }
        } else {
            $loaded = true;
            $_claim = $this->registry->registry('dealer_connect_claim_details');
            $this->setClaimInventory($erpAccountNumber, $_claim);            
        }

//        if ($loaded) {
//            $accessHelper = $this->commonAccessHelper;
//            $editable = $accessHelper->customerHasAccess(
//                'Epicor_Dealerconnect', 'Claims', 'update', '', 'Access'
//            );
//
//            $helper = $this->customerconnectMessagingHelper;
//            $rfq = $this->registry->registry('customer_connect_rfq_details');
//            $status = $helper->getErpquoteStatusDescription($rfq->getQuoteStatus(), '', 'state');
//
//            if ($editable) {
//                if ($status != \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_PENDING) {
//                    $editable = false;
//                }
//            }
//
//            $msgHelper = $this->commMessagingHelper;
//
//            if ($editable && $rfq->getCurrencyCode() != $msgHelper->getCurrencyMapping()) {
//                $editable = false;
//            }
//
//            $enabled = $msgHelper->isMessageEnabled('dealerconnect', 'dclu');
//
//            if ($enabled && $status == \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_AWAITING) {
//                $this->registry->register('claims_editable_partial', true);
//            }
//
//            if (!$enabled) {
//                $editable = false;
//            }
//
//            $this->registry->register('claims_editable', $editable);
//        }

        return $loaded;
    }

    protected function _ewaProcess()
    {
        $helper = $this->commConfiguratorHelper;
        $productHelper = $this->commProductHelper;

        $ewaCode = $this->urlDecoder->decode($this->request->getParam('EWACode'));
        $groupSequence = $this->urlDecoder->decode($this->request->getParam('GroupSequence'));
        $productSku = $this->urlDecoder->decode($this->request->getParam('SKU'));
        $qty = $this->urlDecoder->decode($this->request->getParam('qty')) ?: 1;
        $quoteId = $this->urlDecoder->decode($this->request->getParam('quoteId'));
        $lineNumber = $this->urlDecoder->decode($this->request->getParam('lineNumber'));

        try {
            $product = $this->catalogProductFactory->create();
            $errors = array();

            $product->setStoreId($this->storeManager->getStore()->getId())
                ->load($product->getIdBySku($productSku));

            $prodArray = array();

            $cdm = $this->commMessageRequestCdmFactory->create();

            $cdm->setProductSku($product->getSku());
            $cdm->setProductUom($product->getEccUom());
            $cdm->setGroupSequence($groupSequence);
            $cdm->setTimeStamp(null);
            $cdm->setQty($qty);
            $cdm->setEwaCode($ewaCode);
            $cdm->setQuoteId(!empty($quoteId) ? $quoteId : null);
            $cdm->setLineNumber($lineNumber);

            if ($cdm->sendMessage()) {

                $configurator = $cdm->getResponse()->getConfigurator();
                $ewaTitle = $configurator->getTitle();
                $ewaShortDescription = $configurator->getShortDescription();
                $ewaDescription = $configurator->getDescription();
                $ewaSku = $configurator->getConfiguredProductCode();
                $productCurrency = $configurator->getCurrencies()->getCurrency();

                $ewaAttributes = array(
                    array('description' => 'Ewa Code', 'value' => $ewaCode),
                    array('description' => 'Ewa Description', 'value' => $ewaDescription),
                    array('description' => 'Ewa Short Description', 'value' => $ewaShortDescription),
                    array('description' => 'Ewa SKU', 'value' => $ewaSku),
                    array('description' => 'Ewa Title', 'value' => $ewaTitle),
                );

                $product->setEwaCode($ewaCode);
                $product->setEwaSku($ewaSku);

                $product->setEwaDescription(base64_encode($ewaDescription));
                $product->setEwaShortDescription(base64_encode($ewaShortDescription));
                $product->setEwaTitle(base64_encode($ewaTitle));

                $product->setEwaAttributes(base64_encode(serialize($ewaAttributes)));

                $basePrice = $productCurrency->getBasePrice();
                $customerPrice = $productCurrency->getCustomerPrice();
                $product->setEccMsqBasePrice($basePrice);
                $product->unsFinalPrice();

                $customerPriceUsed = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/cusomterpriceused', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                // Set prices
                if ($customerPriceUsed || $product->getTypeId() == 'bundle') {
                    // NOTe Bundle products cannot have special prices like other products 
                    // as it's expecting a percentage, not a price!
                    $product->setPrice($customerPrice);
                } else {
                    $product->setPrice($basePrice);
                    $product->setSpecialPrice($customerPrice);
                }

                $product->setFinalPrice($customerPrice);
                $product->setMinimalPrice($customerPrice);
                $product->setMinPrice($customerPrice);
                $product->setCustomerPrice($customerPrice);
                $product->setMsqQty($configurator->getQuantity());
                $product->setQty($configurator->getQuantity());
                $product->setUsePrice($customerPrice);


                $set = false;
                if (!$this->registry->registry('msq-processing')) {
                    $this->registry->register('msq-processing', true);
                    $set = true;
                }
                $product->getFinalPrice();
                if ($set) {
                    $this->registry->unregister('msq-processing');
                }

                $price = $product->unsFinalPrice()->getFinalPrice(1);

                $mHelper = $this->commMessagingHelper;
                $currencyCode = $mHelper->getCurrencyMapping($productCurrency->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);

                $formattedPrice = $helper->formatPrice($price, true, $currencyCode);
                $formattedTotal = $helper->formatPrice($price * 1, true, $currencyCode);
                $product->setMsqFormattedPrice($formattedPrice);
                $product->setMsqFormattedTotal($formattedTotal);

                $prodArray[] = $productHelper->getProductInfoArray($product);
            } else {
                $errors[] = __('Failed to retrieve configured details.');
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->messageManager->addErrorMessage($error);
            }
        }

        $response = array(
            'errors' => $errors,
            'products' => $prodArray
        );
        $this->registry->register('line_add_json', str_replace('\\', '\\\\', json_encode($response)));
        $this->registry->register('double_parent', true);
        $result = $this->resultLayoutFactory->create();

        $this->getResponse()->setBody(
            $result->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lineaddbyjs')->toHtml()
        );
    }

    protected function _priceProduct(&$finalProduct, $qty, $currencyCode, $success)
    {
        $helper = $this->commMessagingHelper;

        $price = $finalProduct->getPrice();
        $tierPrice = $finalProduct->getTierPrice($qty);

        if (is_array($tierPrice)) {
            $tierPrice = $tierPrice[0]['website_price'];
        }

        if (!is_null($tierPrice)) {
            $price = $price > 0 ? min($price, $tierPrice) : $tierPrice;
        }

        $special = $finalProduct->getSpecialPrice();

        if (!is_null($special)) {
            $price = $price > 0 ? min($price, $special) : $special;
        }

        $finalProduct->setUsePrice($price);

        $formattedPrice = $helper->formatPrice($price, true, $currencyCode);
        $formattedTotal = $helper->formatPrice($price * $qty, true, $currencyCode);
        $finalProduct->setMsqFormattedPrice($formattedPrice);
        $finalProduct->setMsqFormattedTotal($formattedTotal);
        $finalProduct->setMsqQty($qty);
        $finalProduct->setQty($qty);

        $optionValues = array();

        $customOptions = $finalProduct->getCustomOptions();
        $options = $finalProduct->getOptions();
        if(!is_array($options) && empty($options)){
            $options = array();
        }
        foreach ($options as $option) {
            if (isset($customOptions['option_' . $option->getId()])) {
                $optionVal = $customOptions['option_' . $option->getId()];
                /* @var $optionVal \Magento\Catalog\Model\Product\Configuration\Item\Option */
                $optionValues[] = array(
                    'description' => $option->getTitle(),
                    'value' => $optionVal->getValue()
                );
            }
        }

        $finalProduct->setOptionValues($optionValues);

        if (!empty($optionValues)) {
            $optionValues = base64_encode(serialize($optionValues));
        } else {
            $optionValues = '';
        }

        $finalProduct->setConfiguredOptions($optionValues);

        if ((!$success || !$finalProduct->getIsSalable())) {
            $finalProduct->setError(1);
        } else {
            $finalProduct->setError(0);
        }

        return $finalProduct;
    }

    protected function _initNewClaim()
    {
        $claim = $this->commonXmlvarienFactory->create();

        $customer = $this->customerSession->getCustomer();

        // only add contact if you have a contact code
        if ($customer->getEccContactCode()) {

            $contact = $this->commonXmlvarienFactory->create(
                [
                    'data' => array(
                        'number' => $customer->getEccContactCode(),
                        'name' => $customer->getName(),
                    )
                ]
            );

            $contactArr = $this->commonXmlvarienFactory->create(
                [
                    'data' => array(
                        'contact' => array(
                            $contact
                        )
                    )
                ]
            );

            $claim->setContacts($contactArr);
        }


        $this->registry->register('claims_editable', true);
        $this->registry->register('claim_new', true);

        return $claim;
    }
    
    /**
     * Triggers DEID message to get inventory information for claim
     * 
     * @param int $erpAccountNumber
     * @param object $_claim
     * @return void
     */
    protected function setClaimInventory($erpAccountNumber, $_claim)
    {
        $helper = $this->dealerconnectHelper;
        $deid = $this->dcMessageRequestDeid;
        $deid->setAccountNumber($erpAccountNumber)
             ->setLocationNumber($_claim->getLocationNumber())
             ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
        if ($deid->isActive()) {
            if ($deid->sendMessage()) {
                $inventoryInfo = $deid->getResults();
                $this->registry->register('dealer_connect_claim_inventory', $inventoryInfo);
            }
        }
    }

}
