<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request - GQR Delivery Dates Availability
 * Message used for requesting delivery dates from an erp.
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Gqr extends \Epicor\Comm\Model\Message\Request
{

    /**
     * @var \Epicor\Quotes\Model\Quote
     */
    private $_quote;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $taxClassModelFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = [])
    {
        $this->taxClassModelFactory = $taxClassModelFactory;
        $this->registry = $context->getRegistry();
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('GQR');
        $this->setLicenseType('Customer');
        $this->setConfigBase('epicor_comm_enabled_messages/gqr_request/');
        $this->commonHelper = $commonHelper;
    }

    /**
     * Sets the quote for this request
     * 
     * @param \Epicor\Quotes\Model\Quote $quote
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
        /* @var $helper Epicor_Comm_Helper_Data */
        $helper = $this->commHelper;
        $this->setAccountNumber($helper->getErpAccountNumber($quote->getErpAccountId(), $this->getStoreId()));
        $reference = $quote->getReference() ? : $quote->getId();
        $this->setMessageSecondarySubject('Quote ID: ' . $reference);
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
     * Get original expires time.
     *
     * @return string
     */
    private function getOriginalExpiresTime()
    {
        $time = !empty($this->_quote->getOrigData('expires'))
            ? $this->_quote->getOrigData('expires')
            : $this->_quote->getExpires();

        return $this->getHelper()->getLocalDate(strtotime($time), \IntlDateFormatter::LONG);

    }//end getOriginalExpiresTime()


    /**
     * Get new expires time.
     *
     * @return string
     */
    private function getNewExpiresTime()
    {
        $time = $this->_quote->getExpires();

        return $this->getHelper()->getLocalDate(strtotime($time), \IntlDateFormatter::LONG);

    }//end getNewExpiresTime()


    /**
     * Create a GQR request
     *
     * @return boolean
     */
    public function buildRequest()
    {
        // Added to ensure quoteData has been loaded before creating message.
        $this->_quote->loadQuoteData();

        $helper = $this->getHelper();
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $this->_brand = $helper->getStoreBranding($this->_quote->getStoreId());
        $this->_company = $this->_brand->getCompany();

        $data = $this->getMessageTemplate();

        $accountNumber = $this->getAccountNumber();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $data['messages']['request']['body']['accountNumber'] = $accountNumber;
        $data['messages']['request']['body']['currencyCode'] = $this->getHelper()->getCurrencyMapping($this->storeManager->getStore()->getBaseCurrencyCode());
        $data['messages']['request']['body']['languageCode'] = $this->getHelper()->getLanguageMapping($this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()));

        $createdDate = $this->_quote->getCreatedAt();
        $createdTimestamp = !is_int($createdDate) ? strtotime($createdDate) : $createdDate;
        
        $reference = $this->_quote->getReference() ?: $this->getId();
        $quote = array(
            '_attributes' => array(
                'delete' => $this->_quote->isDeleted() ? 'Y' : 'N',
                'submitToCustomer' => $this->getConfig('submit_to_customer')
            ),
            'reference' => $reference,
            //M1 > M2 Translation Begin (Rule 32)
            //'dateCreated' => $helper->getLocalDate($createdTimestamp, self::DATE_FORMAT),
            //'dateExpires' => array(
            //    'original' => $helper->getLocalDate(
            //        strtotime($this->_quote->getOrigData('expires')), self::DATE_FORMAT
            //    ),
            //    'new' => $helper->getLocalDate(strtotime($this->_quote->getExpires()), self::DATE_FORMAT)
            //),
            'dateCreated' => $helper->getLocalDate($createdTimestamp, \IntlDateFormatter::LONG),
            'dateExpires'  => [
                'original' => $this->getOriginalExpiresTime(),
                'new'      => $this->getNewExpiresTime(),
            ],
            //M1 > M2 Translation End
            'statusCode' => array(
                'original' => $this->_quote->getOrigData('status_id'),
                'new' => $this->_quote->getStatusId(),
            ),
            'quoteNumber' => $this->_quote->getQuoteNumber(),
            'contractCode' => $this->_quote->getContractCode(),
            'contact' => array(),
            'notes' => array(
                'customer' => array(),
                'shopkeeper' => array()
            ),
        );

        $customer = $this->_quote->getCustomer(true);
        /* @var $customer Epicor_Comm_Model_Customer */

        $quote['contact'] = array(
            'contactCode' => $customer->getEccContactCode(),
            'name' => $customer->getName(),
            'function' => $customer->getEccFunction(),
            'telephoneNumber' => $customer->getEccTelephoneNumber(),
            'faxNumber' => $customer->getEccFaxNumber(),
            'emailAddress' => $customer->getEmail(),
        );

        $quote['deliveryAddress'] = $this->getHelper()->formatAddress(
            $customer->getDefaultShippingAddress(), 'shipping'
        );

        $notes = $this->_quote->getNotes();

        foreach ($notes as $note) {
            $noteInfo = array(
                'original' => array(
                    '_attributes' => array(
                        'public' => $note->getOrigData('is_private') ? 'N' : 'Y',
                        'visible' => $note->getOrigData('is_visible') ? 'Y' : 'N'
                    ),
                    'erpRef' => $note->getOrigData('erp_ref'),
                    'webRef' => $note->getOrigData('entity_id'),
                    'noteText' => $note->getOrigData('note')
                ),
                'new' => array(
                    '_attributes' => array(
                        'public' => $note->getisPrivate() ? 'N' : 'Y',
                        'visible' => $note->getisVisible() ? 'Y' : 'N'
                    ),
                    'erpRef' => $note->getErpRef(),
                    'webRef' => $note->getId(),
                    'noteText' => $note->getNote()
                ),
            );

            if ($note->getAdminId()) {
                $quote['notes']['shopkeeper']['note'][] = $noteInfo;
            } else {
                $quote['notes']['customer']['note'][] = $noteInfo;
            }
        }

        $data['messages']['request']['body']['quote'] = $quote;

        $items = $this->_quote->getProducts();

        $defaultShippingDays = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //M1 > M2 Translation Begin (Rule 32)
        //$dateRequired = $this->getHelper()->getLocalDate(
        //    strtotime(" +$defaultShippingDays day"), self::DATE_FORMAT
        //);
        $dateRequired = $this->getHelper()->getLocalDate(
            strtotime(" +$defaultShippingDays day"), \IntlDateFormatter::LONG
        );
        //M1 > M2 Translation End

        foreach ($items as $lineNum => $item) {
            /* @var $item Epicor_Quotes_Model_Quote_Product */

            $uomArr = $helper->splitProductCode($item->getSku());
            $productSku = $uomArr[0];
            $uomCode = $this->commonHelper->getProductUom($uomArr, $item);
            $attributes = array();

            $productOptions = $item->getProductOptions();

            if (!empty($productOptions)) {
                $attributes['attribute'] = array();
                foreach ($productOptions as $option) {
                    $attributes['attribute'][] = array(
                        'description' => $option['label'],
                        'value' => $option['value']
                    );
                }
            }

            $taxClass = $this->taxClassModelFactory->create()->load($item->getProduct()->getTaxClassId());

            $line = array(
                '_attributes' => array(
                    'number' => $item->getId(),
                ),
                'webLineNumber' => $item->getId(),
                'erpLineNumber' => $item->getErpLineNumber(),
                'productCode' => $productSku,
                'dateRequired' => $dateRequired,
                'taxCode' => $taxClass->getClassName(),
                'unitOfMeasureCode' => $uomCode,
                'locationCode' => $item->getLocationCode(),
                'contractCode' => $item->getContractCode(),
                'quantity' => array(
                    'requested' => $item->getOrigData('new_qty'),
                    'original' => $item->getOrigQty(),
                    'new' => $item->getNewQty(),
                ),
                'price' => array(
                    'requested' => $item->getOrigData('new_price'),
                    'original' => $item->getOrigPrice(),
                    'new' => $item->getNewPrice(),
                ),
                'notes' => array(),
                'attributes' => $attributes
            );

            if ($item->getNote()) {
                $line['notes']['note'] = array(
                    'original' => array(
                        'erpRef' => $item->getOrigData('erp_note_ref'),
                        'webRef' => $item->getId(),
                        'noteText' => $item->getOrigData('note')
                    ),
                    'new' => array(
                        'erpRef' => $item->getErpNoteRef(),
                        'webRef' => $item->getId(),
                        'noteText' => $item->getNote()
                    )
                );
            }

            $lines[] = $line;
        }

        $data['messages']['request']['body']['lines']['line'] = $lines;

        $this->setOutXml($data);
        return true;
    }

    /**
     * Processes the GQR response
     * 
     * @return boolean
     */
    public function processResponse()
    {
        $success = false;

        if ($this->isSuccessfulStatusCode()) {
            $this->registry->register('gqr-processing', true);
            if ($this->getResponse()->getRejected() == 'N') {

                $this->_quote = $this->quotesQuoteFactory->create()->load($this->_quote->getId());

                $customer = $this->getResponse()->getQuote()->getNotes()->getCustomer();
                if ($customer) {
                    $this->_updateNotes($customer->getNote());
                }

                $shopkeeper = $this->getResponse()->getQuote()->getNotes()->getShopkeeper();
                if ($shopkeeper) {
                    $this->_updateNotes($shopkeeper->getNote());
                }

                $linesGroup = $this->getResponse()->getLines();

                if ($linesGroup) {
                    $this->_updateLines($linesGroup->getasarrayLine());
                }

                $this->_quote->setQuoteNumber($this->getResponse()->getQuote()->getQuoteNumber());
                $this->_quote->setDataChanges(true);
                $this->_quote->save();
                $success = true;
            } else {
                throw new \Exception('Quote request rejected by ERP');
            }
            $this->registry->unregister('gqr-processing');
        }

        return $success;
    }

    /**
     * Updates Notes for the quote
     * 
     * @param array $notes
     */
    private function _updateNotes($notes)
    {
        if (!is_array($notes)) {
            $notes = array($notes);
        }

        foreach ($notes as $note) {
            if ($this->_quote->hasNote($note->getNew()->getWebRef())) {
                $this->_quote->setNoteErpRef($note->getNew()->getErpRef(), $note->getNew()->getWebRef());
            }
        }
    }

    /**
     * Updates lines for the quote
     * 
     * @param array $lines
     */
    private function _updateLines($lines)
    {
        $helper = $this->getHelper();
        /* @var $helper Epicor_Comm_Helper_Messaging */

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
                /* @var $item Epicor_Quotes_Model_Quote_Product */
                if (!$item->isObjectNew()) {

                    $item->setErpLineNumber($erpLineNumber);
                    $notes = $line->getNotes();
                    if ($notes) {
                        $note = $notes->getNote();
                        if ($note->getNew()) {
                            if ($note->getNew()->getNoteText()) {
                                $item->setNote($note->getNew()->getNoteText());
                            }
                            $item->setErpNoteRef($note->getNew()->getErpRef());
                        }
                    }

                    $this->_quote->updateItem($item);
                }
            }
        }
    }

}
