<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Upload - GQR Delivery Dates Availability
 * Message used for uploading Quotes from an ERP
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Gqr extends \Epicor\Comm\Model\Message\Upload
{

    /**
     * @var \Epicor\Quotes\Model\Quote
     */
    private $_quote;
    private $_request;
    private $_isNew;
    private $_prefix;
    private $_deliveryAddress;
    private $_productLinesNotOnFile = array();
    private $_lines;
    private $_itemOriginalValues = array();
    private $_notesOriginalValues = array();
    private $_lineNotesInUploadMessage = array();


    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Epicor\Quotes\Model\Quote\CustomerFactory
     */
    protected $quotesQuoteCustomerFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Quotes\Model\Quote\NoteFactory
     */
    protected $quotesQuoteNoteFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Quotes\Model\Quote\CustomerFactory $quotesQuoteCustomerFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Quotes\Model\Quote\NoteFactory $quotesQuoteNoteFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->quotesQuoteCustomerFactory = $quotesQuoteCustomerFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->quotesQuoteNoteFactory = $quotesQuoteNoteFactory;
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/gqr_mapping/');
        $this->setMessageType('GQR');
        $this->setLicenseType(array('Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_QUOTES);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->_prefix = $this->scopeConfig->getValue('epicor_quotes/general/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Sets the quote for this request
     */
    public function loadQuote()
    {
        $this->_quote = $this->quotesQuoteFactory->create();
        /* @var $this->_quote Epicor_Quotes_Model_Quote */

        $quoteNumber = $this->_request->getQuote()->getQuoteNumber();

        $this->_quote->load($quoteNumber, 'quote_number');
        $this->setMessageSecondarySubject('Quote Number: ' . $quoteNumber);

        $this->_isNew = $this->_quote->isObjectNew();

        $reference = $this->_request->getQuote()->getReference();

        if ($this->_isNew && !empty($reference)) {
            $this->_quote->load($reference, 'reference');

            $this->_isNew = $this->_quote->isObjectNew();
        }

        if (!$this->_isNew) {
            $expectedReference = $this->_prefix . $this->_quote->getId();
            if ($reference != $expectedReference) {
//                throw new Exception(
//                'Quote reference ' . $reference . ' does not match for quote number ' . $quotenumber
//                . ', Quote reference expected: ' . $expectedReference, self::STATUS_VALUES_DO_NOT_TALLY
//                );
            }
        }
    }

    /**
     * returns the quote for this request
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Processes the GQR response
     * 
     * @return boolean
     */
    public function processAction()
    {
        $this->registry->register('gqr-processing', true);

        $this->_request = $this->getRequest();

        $this->loadQuote();

        $delete = $this->_request->getQuote()->getData('_attributes')->getDelete();
        if ($delete == 'Y') {
            $this->_quote->delete();
        } else {
            $this->_processQuoteDetails();

            if ($this->_request->getQuote()->getDeliveryAddress()) {
                $this->_deliveryAddress = $this->_request->getQuote()->getDeliveryAddress()->getData();
            }

            $linesObj = $this->_request->getLines();
            if ($linesObj) {
                $lines = $linesObj->getasarrayLine();                // only process if lines are supplied
                $this->_processLines($lines);
            }

            $this->_processNotes('customer');
            $this->_processNotes('shopkeeper');

            $this->_setQuoteStore(); 
            $this->_quote->save();
            if (!$this->_isNew) {
                //$this->removeProductsNotInMsgLines();                   // if product is not on gqr, remove from quote
            }
        }
        $this->buildResponse();

        $this->registry->unregister('gqr-processing');
    }

    protected function _processQuoteDetails()
    {
        $this->_quote->setQuoteNumber($this->_request->getQuote()->getQuoteNumber());

        // set up currency code for the quote
        $code = $this->_request->getCurrencyCode();
        $currency = $this->getHelper()->getCurrencyMapping($code, \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $this->_quote->setCurrencyCode($currency);

        // sort out what customer is assigned to this quote
        $this->_setQuoteCustomer($currency);

        // update quote expiry date
        $expires = $this->_request->getQuote()->getDateExpires()->getNew();
        $this->_quote->setExpires($expires);

        // update quote status
        $newStatus = $this->_request->getQuote()->getStatusCode()->getNew();
        $this->_quote->setStatusId($newStatus);

        // if new, then set created date
        if ($this->_isNew) {
            $this->_quote->setCreatedAt($this->_request->getQuote()->getDateCreated());
        }
    }

    /**
     * Updates quote customer / erp account ID
     * 
     * @param array $notes
     */
    private function _setQuoteCustomer($currency)
    {
        $brand = $this->_getBranding();
        $this->_brandAccountNumber($brand);
        $accountNumber = $this->_request->getAccountNumber();
        $erpAccount = $this->getErpAccount($accountNumber);
        if (!$erpAccount || $erpAccount->isObjectNew()) {
            $error = $this->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_CUSTOMER_NOT_ON_FILE, $accountNumber);
            throw new \Exception($error, \Epicor\Comm\Model\Message::STATUS_CUSTOMER_NOT_ON_FILE);
        }

        $websites = $this->_getWebsitesForCurrenciesAndBranding($currency);

        $this->_quote->setErpAccountId($erpAccount->getId());

        $contact = $this->_request->getQuote()->getContact();
        $this->_quote->setIsGlobal(false);                  // default to not global  
        $email = null;
        $code = null;
        $erpAccountId = null;

        $defaults = $erpAccount->getDefaultForStores();
        $b2bCustomer = true;
        foreach ($websites as $website) {
            if (isset($defaults[$website])) {
                $b2bCustomer = false;
            }
        }

        if (!$contact) {    // if no contact and quote exists, that's ok  
            if ($b2bCustomer) {                          // if no contact and b2b, global to true
                $this->_quote->setIsGlobal(true);
                $this->_quote->deleteCustomersFromQuote();  // delete existing contacts from quote
            } else {
                if (!$this->_isNew) {           // quote is not new  
                    $customerId = $this->quotesQuoteCustomerFactory->create()->load($this->_quote->getId(), 'quote_id')->getCustomerId();
                    if ($customerId) {         // if customer exists
                        $customer = $this->customerCustomerFactory->create()->load($customerId);
                        $email = $customer->getEmail();
                        $code = $customer->getEccContactCode();
                        $erpAccountId = $erpAccount->getId();
                    } else {                                                              // customer doesn't exist: error
                        $errorCode = \Epicor\Comm\Model\Message::STATUS_CUSTOMER_NOT_ON_FILE;
                        $error = $this->getErrorDescription($errorCode, $this->_quote->getCustomerId());
                        throw new \Exception($error, $errorCode);
                    }
                } else {                                                                 // no contact, b2c, and new quote: error
                    $errorCode = \Epicor\Comm\Model\Message::STATUS_INVALID_CONTACT;
                    $error = $this->getErrorDescription($errorCode, 'Contact could not be found');
                    throw new \Exception($error, $errorCode);
                }
            }
        } else {
            if ($contact->getEmailAddress() && !$contact->getContactCode()) {
                $email = $contact->getEmailAddress();
            } else if ($contact->getEmailAddress() && $contact->getContactCode()) {
                $email = $contact->getEmailAddress();
                $code = $contact->getContactCode();
                $erpAccountId = $erpAccount->getId();
            }
            $customers = $this->customerResourceModelCustomerCollectionFactory->create();
            /* @var $customers Mage_Customer_Model_Resource_Customer_Collection */

            $customers->addAttributeToSelect('ecc_contact_code');

            if ($code) {
                $customers->addAttributeToFilter('ecc_contact_code', $code);
            }

            $customers = $this->addErpFilter($customers, $erpAccountId);
            $customers->addAttributeToFilter('email', $email);

            if ($customers->getSize() == 0) {
                $customers = $this->customerResourceModelCustomerCollectionFactory->create();
                /* @var $customers Mage_Customer_Model_Resource_Customer_Collection */
                $customers = $this->addErpFilter($customers, $erpAccountId);
                $customers->addAttributeToFilter('ecc_contact_code', null);
                $customers->addAttributeToFilter('email', $email);
            }

            if ($customers->getSize() == 0) {  // if contact is supplied, but not on ecc, error (no need to specify $contact anymore)
                $errorCode = \Epicor\Comm\Model\Message::STATUS_INVALID_CONTACT;
                $error = $this->getErrorDescription($errorCode, 'Contact could not be found');
                throw new \Exception($error, $errorCode);
            }
            $this->_quote->deleteCustomersFromQuote();   // remove customers from existing quote - apply to quote only customers supplied in GQR 
            if ($this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                foreach ($customers as $customer) {
                    /* @var $customer Epicor_Comm_Model_Customer */
                    $websiteId = $customer->getStore()->getWebsiteId();
                    if (in_array($websiteId, $websites)) {
                        $this->_quote->addCustomerId($customer->getId());
                    }
                }
            } else {
                $customer = $customers->getFirstItem();
                /* @var $customer Epicor_Comm_Model_Customer */
                $this->_quote->addCustomerId($customer->getId());
            }
        }
    }

    protected function _getBranding()
    {
        $brands = $this->_request->getBrands();

        $brand = null;
        if (!is_null($brands)) {
            $brand = $brands->getBrand();
        }

        if (is_array($brand)) {
            $brand = $brand[0];
        }

        if (empty($brand) || !$brand->getCompany()) {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End
        }

        return $brand;
    }

    protected function _brandAccountNumber($brand)
    {
        $company = $brand->getCompany();
        $this->_request->setBrandCompany($company);

        if (!empty($company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $this->_request->setAccountNumber($company . $delimiter . $this->_request->getAccountNumber());
        }
    }

    /**
     * Updates Notes for the quote
     */
    private function _processNotes($type)
    {
        $notes = array();
        $notesObj = false;

        if ($type == 'customer') {
            $notesObj = $this->_request->getQuote()->getNotes()->getCustomer();
        } else if ($type == 'shopkeeper') {
            $notesObj = $this->_request->getQuote()->getNotes()->getShopkeeper();
        }

        if ($notesObj) {
            $notes = $notesObj->getasarrayNote();
        }

        foreach ($notes as $note) {
            $visible = ($note->getNew()->getData('_attributes')->getVisible() == 'Y') ? true : false;
            $private = ($note->getNew()->getData('_attributes')->getPublic() == 'Y') ? false : true;

            $newNote = $note->getNew();
            $existingNote = $this->_quote->getNote($newNote->getWebRef());
            //save old value in message
            if ($note->getOriginal()) {
                $this->_notesOriginalValues[$note->getNew()->getErpRef()]['web_ref'] = $note->getOriginal()->getWebRef();
                $this->_notesOriginalValues[$note->getNew()->getErpRef()]['erp_ref'] = $note->getOriginal()->getErpRef();
            }
            if (!$existingNote) {
                $quoteNote = $this->quotesQuoteNoteFactory->create();
                /* @var $quoteNote Epicor_Quotes_Model_Quote_Note */
                $quoteNote->setNote($newNote->getNoteText());
                $quoteNote->setAdminId(($type == 'shopkeeper'));
                $quoteNote->setIsVisible($visible);
                $quoteNote->setIsPrivate($private);
                $quoteNote->setCreatedAt(time());
                $quoteNote->setSendEmail(false);
                $quoteNote->setErpRef($newNote->getErpRef());

                $this->_quote->addNoteObject($quoteNote);
            } else {
                $existingNote->setNote($newNote->getNoteText());
                $existingNote->setErpRef($newNote->getErpRef());
                $existingNote->setIsVisible($visible);
                $existingNote->setIsPrivate($private);
                $this->_quote->updateNote($existingNote);
            }
        }
    }

    /**
     * Updates lines for the quote
     * 
     * @param array $lines
     */
    private function _processLines($lines)
    {
        $helper = $this->getHelper();
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $processedWebLines = array();
        $processedErpLines = array();

        foreach ($lines as $line) {
            $uom = $line->getUnitOfMeasureCode();
            $separator = $helper->getUOMSeparator();
            $uomPartNo = $line->getProductCode() . $separator . $uom;

            $productId = $this->catalogProductFactory->create()->getIdBySku($uomPartNo);
            //product is not UOM product
            if (!$productId) {
                $productId = $this->catalogProductFactory->create()->getIdBySku($line->getProductCode());
            }

            $product = $this->catalogProductFactory->create()->load($productId);

            if ($product->getId()) {

                $webLineNumber = $line->getData('_attributes')->getNumber();

                if (empty($webLineNumber)) {
                    $webLineNumber = $line->getWebLineNumber();
                }

                $erpLineNumber = $line->getErpLineNumber();

                $item = $this->_quote->getProductData($webLineNumber, $erpLineNumber);

                $processedWebLines[] = $webLineNumber;
                $processedErpLines[] = $erpLineNumber;

                if ($item->isObjectNew()) {
                    $item->setQuoteId($this->_quote->getId());
                    $item->setProductId($product->getId());
                    $item->setOrigQty($line->getQuantity()->getOriginal());
                    $item->setOrigPrice($line->getPrice()->getOriginal());
                }

                $item->setErpLineNumber($erpLineNumber);
                $item->setNewQty($line->getQuantity()->getNew());
                $item->setNewPrice($line->getPrice()->getNew());
                $notes = $line->getNotes();
                if ($notes) {
                    $this->_lineNotesInUploadMessage = true;
                    $note = $notes->getNote();
                    if ($note->getNew()) {
                        if ($note->getOriginal()) {
                            $erpNoteRef = $note->getNew()->getErpRef();
                            $this->_itemOriginalValues[$erpNoteRef]['erp_ref'] = $note->getOriginal()->getErpRef();
                            $this->_itemOriginalValues[$erpNoteRef]['web_ref'] = $note->getOriginal()->getWebRef();
                        }
                        $item->setErpNoteRef($note->getNew()->getErpRef());
                        $item->setNote($note->getNew()->getNoteText());
                    }
                }
                if ($item->isObjectNew()) {
                    $this->_quote->addItem($item);
                } else {
                    $this->_quote->updateItem($item);
                }
            } else {
                $this->_productLinesNotOnFile[$line->getData('_attributes')->getNumber()] = $line;
            }
        }

        if (!empty($processedWebLines) || !empty($processedErpLines)) {
            $products = $this->_quote->getProducts();

            foreach ($products as $lineId => $product) {
                $webId = $product->getId();
                $erpId = $product->getErpLineNumber();
                $delete = true;

                if (!empty($webId) && in_array($webId, $processedWebLines)) {
                    $delete = false;
                }
                if (!empty($erpId) && in_array($erpId, $processedErpLines)) {
                    $delete = false;
                }

                if ($delete) {
                    $this->_quote->removeItem($lineId);
                }
            }
        }
    }

    private function _setQuoteStore()
    {
        if ($this->_isNew) {
            $brand = $this->_request->getBrand();

            if (!empty($brand)) {
                $brandStores = $this->getHelper()->getStoreFromBranding($brand->getCompany(), $brand->getSite(), $brand->getWarehouse(), $brand->getGroup());
            } else {
                $brandStores = $this->getHelper()->getStoreFromBranding(null);
            }

            if (!empty($brandStores)) {
                $store = array_shift($brandStores);
            } else {
                throw new \Exception(
                'Provided Brands do not match any stores', \Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR
                );
            }
            if($store && $store->getId()){
              $id = (int) $store->getId();
               $this->_quote->setStoreId($id);
            }
        }
    }

    public function buildResponse()
    {
        $message = $this->getMessageTemplate();
        $message['messages']['response']['body'] = array(
            'status' => array(
                'code' => $this->getStatusCode(),
                'description' => $this->getStatusDescription(),
            ),
        );

        if ($this->isActive()) {

            $quoteId = $this->_quote->getId();
            unset($this->_quote);
            $this->_quote = $this->quotesQuoteFactory->create()->load($quoteId);
            $recurringQuote = $this->_request->getQuote()->getRecurringQuote();

            if (!$this->_quote->isObjectNew()) {
                $quote = array();
                $quote['reference'] = $this->_quote->getReference();
                $quote['quoteNumber'] = $this->_quote->getQuoteNumber();

                if (isset($recurringQuote)) {
                    $quote['recurringQuote'] = $this->_request->getQuote()->getRecurringQuote();
                }
                $quote['contact'] = $this->contactForQuote();
                $quote['notes'] = array(
                    'customer' => array(),
                    'shopkeeper' => array()
                );
                if (isset($this->_deliveryAddress)) {
                    $quote['deliveryAddress'] = $this->_deliveryAddress;
                }

                $lines = array();

                $notes = $this->_quote->getNotes();

                foreach ($notes as $note) {
                    $noteInfo = array(
                        'original' => array(
                            'erpRef' => isset($this->_notesOriginalValues[$note->getErpRef()]['erp_ref']) ? $this->_notesOriginalValues[$note->getErpRef()]['erp_ref'] : $note->getErpRef(),
                            'webRef' => isset($this->_notesOriginalValues[$note->getErpRef()]['web_ref']) ? $this->_notesOriginalValues[$note->getErpRef()]['web_ref'] : $note->getId(),
                        ),
                        'new' => array(
                            'erpRef' => $note->getErpRef(),
                            'webRef' => $note->getId(),
                        ),
                    );

                    if ($note->getAdminId()) {
                        $quote['notes']['shopkeeper']['note'][] = $noteInfo;
                    } else {
                        $quote['notes']['customer']['note'][] = $noteInfo;
                    }
                }

                $items = $this->_quote->getProducts();
                $lines = array();
                foreach ($items as $lineNum => $item) {
                    /* @var $item Epicor_Quotes_Model_Quote_Product */
                    $uomArr = $this->getHelper()->splitProductCode($item->getSku());
                    $productSku = $uomArr[0];
                    $this->_lines['line'][$lineNum] = array(
                        '_attributes' => array(
                            'number' => $lineNum,
                        ),
                        'webLineNumber' => $item->getId(),
                        'erpLineNumber' => $item->getErpLineNumber(),
                        'productCode' => $productSku,
                        'unitOfMeasureCode' => $item->getEccUom(),
                        'lineStatus' => array(
                            'code' => '',
                            'description' => '',
                            'erpErrorCode' => '',
                        ),
                    );
                    if ($this->_lineNotesInUploadMessage) {
                        $this->_lines['line'][$lineNum]['notes'] = array(
                            'note' => array(
                                'original' => array(
                                    'erpRef' => isset($this->_itemOriginalValues[$item->getErpNoteRef()]['erp_ref']) ? $this->_itemOriginalValues[$item->getErpNoteRef()]['erp_ref'] : null,
                                    'webRef' => isset($this->_itemOriginalValues[$item->getErpNoteRef()]['web_ref']) ? $this->_itemOriginalValues[$item->getErpNoteRef()]['web_ref'] : null,
                                ),
                                'new' => array(
                                    'erpRef' => $item->getErpNoteRef(),
                                    'webRef' => $item->getId(),
                                )
                            )
                        );
                    } else {
                        $this->_lines['line'][$lineNum]['notes'] = '';
                    }
                }
                // add products not on file to lines list 

                foreach ($this->_productLinesNotOnFile as $linenof) {
                    $this->lineResponse($linenof);
                }
                $rejected = ($this->isSuccessfulStatusCode()) ? 'N' : 'Y';
                $message['messages']['response']['body']['rejected'] = $rejected;
                $message['messages']['response']['body']['quote'] = $quote;
                $message['messages']['response']['body']['lines'] = $this->_lines;
            }
        }
        $this->setOutXml($message);
    }

    private function contactForQuote()
    {
        $customerContact = $this->_quote->getCustomer();
        $contact = array();
        if ($customerContact) {
            $contact['code'] = $customerContact->getEccContactCode();
            $contact['name'] = $customerContact->getName();
            $contact['function'] = $customerContact->getFunction();
            $contact['telephoneNumber'] = $customerContact->getTelephoneNumber();
            $contact['faxNumber'] = $customerContact->getFaxNumber();
            $contact['email'] = $customerContact->getEmail();
        }
        return $contact;
    }

    private function lineResponse($linenof)
    {
        $lineNumber = $linenof->getData('_attributes')->getNumber();
        $this->_lines['line'][$lineNumber] = array(
            '_attributes' => array(
                'number' => $lineNumber,
            ),
            'productCode' => $linenof->getProductCode(),
            'unitOfMeasureCode' => $linenof->getUnitOfMeasureCode(),
            'lineStatus' => array(
                'code' => '011',
                'description' => 'Product not on File',
                'erpErrorCode' => '',
            ),
            'notes' => '',
        );
//        if($this->_lineNotesInUploadMessage){                         // to be removed if notes are not to be supplied with error product
//            $this->_lines['line'][$lineNumber]['notes'] = array(
//                       'notes' => array(
//                        'note' => array(
//                            'original' => array(
//                                'erpRef' => '',
//                                'webRef' => '',
//                            ),
//                            'new' => array(
//                                'erpRef' => '' ,
//                                'webRef' => '' ,
//                            )
//                        )
//                    ),
//                );
//        }       
    }

    /**
     * Add ERP account fiter to customer collection
     *
     * @param $customers
     * @param $erpAccountId
     * @return mixed
     */
    private function addErpFilter($customers, $erpAccountId)
    {
        if (is_null($erpAccountId)) {
            $customers->addAttributeToFilter('ecc_erpaccount_id', null, 'left');
        } else {
            $customers->addAttributeToFilter('ecc_erpaccount_id', $erpAccountId);
        }
        return $customers;
    }

}
