<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model;


/**
 * @method int getId()
 * @method int getReference()
 * @method int setReference(string $valu)
 * @method int getErpAccountId()
 * @method int setErpAccountId(int $value)
 * @method int getQuoteNumber()
 * @method int setQuoteNumber(string $value)
 * @method int getCurrencyCode()
 * @method int setCurrencyCode(int $value)
 * @method int getStatusId()
 * @method int setStatusId(int $value)
 * @method string setExpires(string $value)
 * @method string getCreatedAt()
 * @method string setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method string setUpdatedAt(string $value)
 * @method bool getShowPrices()
 * @method bool setShowPrices(bool $value)
 * @method bool getSendCustomerReminders()
 * @method bool setSendCustomerReminders(bool $value)
 * @method bool getSendCustomerComments()
 * @method bool setSendCustomerComments(bool $value)
 * @method bool getSendCustomerUpdates()
 * @method bool setSendCustomerUpdates(bool $value)
 * @method bool getSendAdminReminders()
 * @method bool setSendAdminReminders(bool $value)
 * @method bool getSendAdminComments()
 * @method bool setSendAdminComments(bool $value)
 * @method bool getSendAdminUpdates()
 * @method bool setSendAdminUpdates(bool $value)
 * @method bool getIsGlobal()
 * @method bool setIsGlobal(bool $value)
 * @method int getStoreId()
 * @method bool setStoreId(int $integer)
 * 
 */
class Quote extends \Epicor\Database\Model\Quote
{

    protected $_eventPrefix = 'ecc_quote';
    protected $_eventObject = 'quote';

    const STATUS_PENDING_RESPONSE = 'pending'; // 1
    const STATUS_AWAITING_ACCEPTANCE = 'awaiting_acceptance'; // 2
    const STATUS_QUOTE_EXPIRED = 'expired'; // 3
    const STATUS_QUOTE_REJECTED_CUSTOMER = 'rejected_customer'; // 4
    const STATUS_QUOTE_REJECTED_ADMIN = 'rejected_admin'; // 5
    const STATUS_QUOTE_ACCEPTED = 'accepted'; // 6
    const STATUS_QUOTE_ORDERED = 'ordered'; // 7

    protected $_quoteStatuses = array(
        self::STATUS_PENDING_RESPONSE => 'Pending Response',
        self::STATUS_AWAITING_ACCEPTANCE => 'Awaiting Acceptance',
        self::STATUS_QUOTE_EXPIRED => 'Expired',
        self::STATUS_QUOTE_REJECTED_CUSTOMER => 'Rejected By Customer',
        self::STATUS_QUOTE_REJECTED_ADMIN => 'Rejected By Admin',
        self::STATUS_QUOTE_ACCEPTED => 'Accepted By Customer',
        self::STATUS_QUOTE_ORDERED => 'Ordered'
    );
    protected $_newCustomerIds = array();
    protected $_availableCustomerIds = array();
    protected $_availableNotes = array();
    protected $_newNotes = array();
    protected $_availableProducts = array();
    protected $_deletedProducts = array();
    protected $_lineNum = 1;

    /**
     * @var \Epicor\Quotes\Model\Quote\CustomerFactory
     */
    protected $quotesQuoteCustomerFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Customer\CollectionFactory
     */
    protected $quotesResourceQuoteCustomerCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Note\CollectionFactory
     */
    protected $quotesResourceQuoteNoteCollectionFactory;

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory
     */
    protected $quotesResourceQuoteProductCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Quotes\Model\Quote\NoteFactory
     */
    protected $quotesQuoteNoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Quotes\Model\Quote\ProductFactory
     */
    protected $quotesQuoteProductFactory;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     * 
     */
    protected  $translationStateInterface;
    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    //protected $emailTemplateFactory;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;
        
    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Epicor\Quotes\Model\Quote\CustomerFactory $quotesQuoteCustomerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Quotes\Model\ResourceModel\Quote\Customer\CollectionFactory $quotesResourceQuoteCustomerCollectionFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Quotes\Model\ResourceModel\Quote\Note\CollectionFactory $quotesResourceQuoteNoteCollectionFactory,
        \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory $quotesResourceQuoteProductCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Quotes\Model\Quote\NoteFactory $quotesQuoteNoteFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Quotes\Model\Quote\ProductFactory $quotesQuoteProductFactory,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Translate\Inline\StateInterface $translationStateInterface,
        \Epicor\Quotes\Helper\Data $quotesHelper,
        //\Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Url $urlBuilder,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,       
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->quotesQuoteCustomerFactory = $quotesQuoteCustomerFactory;
        $this->eventManager = $context->getEventDispatcher();
        $this->scopeConfig = $scopeConfig;
        $this->quotesResourceQuoteCustomerCollectionFactory = $quotesResourceQuoteCustomerCollectionFactory;
        $this->commHelper = $commHelper;
        $this->quotesResourceQuoteNoteCollectionFactory = $quotesResourceQuoteNoteCollectionFactory;
        $this->quotesResourceQuoteProductCollectionFactory = $quotesResourceQuoteProductCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        $this->quotesQuoteNoteFactory = $quotesQuoteNoteFactory;
        $this->registry = $registry;
        $this->quotesQuoteProductFactory = $quotesQuoteProductFactory;
        $this->taxCalculation = $taxCalculation;
        $this->checkoutCart = $checkoutCart;
        $this->commonCartHelper = $commonCartHelper;
        $this->generic = $generic;
        $this->checkoutSession = $checkoutSession;
        $this->translationStateInterface = $translationStateInterface;
        $this->quotesHelper = $quotesHelper;
       // $this->emailTemplateFactory = $emailTemplateFactory;
        $this->transportBuilder = $transportBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Quotes\Model\ResourceModel\Quote');
    }

    public function loadQuoteData()
    {
        $this->_getCustomerIdsData();
        $this->_getNotesData();
        $this->_getProductsData();
    }

    public function afterSave()
    {
        // process new notes before parent call so that GQR request can get the correct info
        foreach ($this->_newNotes as $x => $note) {
            $note->setQuoteId($this->getId());
            $note->save();
            unset($this->_newNotes[$x]);
            $this->_availableNotes[$note->getId()] = $note->getId();
            $this->setData('note_' . $note->getId(), $note);
        }

        parent::afterSave();

        foreach ($this->_availableNotes as $noteId) {
            $note = $this->getNote($noteId);
            if ($note->hasDataChanges()) {
                $note->save();
            }
        }

        foreach ($this->_deletedProducts as $line => $productId) {
            $product = $this->getData('line_' . $line);
            if ($product && $product->getId()) {
                $product->delete();
            }
        }

        foreach ($this->_availableProducts as $line => $productId) {
            $product = $this->getProductData($line);

            if ($product->hasDataChanges()) {
                $product->setQuoteId($this->getId());
                $product->save();
            }
        }

        foreach ($this->_newCustomerIds as $customerId) {
            $quoteCustomer = $this->quotesQuoteCustomerFactory->create();
            /* @var $quoteCustomer Epicor_Quotes_Model_Quote_Customer */

            $quoteCustomer->setQuoteId($this->getId());
            $quoteCustomer->setCustomerId($customerId);
            $quoteCustomer->save();
        }

        if ($this->getOrigData('status_id') != $this->getStatusId()) {
            $this->sendCustomerUpdate();
            $this->sendAdminUpdate();
        }                
        
        $this->cleanModelCache();
        $this->eventManager->dispatch($this->_eventPrefix . '_save_complete', $this->_getEventData());
    }

    public function beforeSave()
    {
        parent::beforeSave();
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(time());
        }

        if (!$this->getCreatedBy()) {
            $customer = $this->getCustomer(true);
            /* @var $customer Epicor_Comm_Model_Customer */
            $name = $customer->getName();
            $email = $customer->getEmail();

            $this->setCreatedBy($name . ' (' . $email . ')');
        }

        if ($this->isObjectNew()) {
            $reminders = $this->scopeConfig->getValue('epicor_quotes/email_alerts/send_reminders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->setSendAdminComments($this->scopeConfig->getValue('epicor_quotes/email_alerts/send_admin_note_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $this->setSendAdminReminders(in_array($reminders, array('admin', 'both')));
            $this->setSendAdminUpdates($this->scopeConfig->getValue('epicor_quotes/email_alerts/send_admin_update_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            $this->setSendCustomerComments($this->scopeConfig->getValue('epicor_quotes/email_alerts/send_customer_note_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $this->setSendCustomerReminders(in_array($reminders, array('customer', 'both')));
            $this->setSendCustomerUpdates($this->scopeConfig->getValue('epicor_quotes/email_alerts/send_customer_update_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                                  
            $mainTable = $this->getResource()->getMainTable();            
            $nextReferenceId = $this->customerconnectRfqHelper->getNextRfqWebRef();
            $reference = $this->scopeConfig->getValue('epicor_quotes/general/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $reference .= $nextReferenceId;
            $this->setReference($reference);                 
        }

        $this->_updateShowPrices();
        $this->setUpdatedAt(time());
    }

    /**
     * UPdates the show prices attribute based on status
     */
    private function _updateShowPrices()
    {
        $noShow = array(
            self::STATUS_PENDING_RESPONSE,
        );

        $show = array(
            self::STATUS_AWAITING_ACCEPTANCE,
            self::STATUS_QUOTE_ACCEPTED,
            self::STATUS_QUOTE_REJECTED_CUSTOMER,
            self::STATUS_QUOTE_ORDERED
        );

        if (in_array($this->getStatusId(), $show)) {
            $this->setShowPrices(true);
        } else if (in_array($this->getStatusId(), $noShow)) {
            $this->setShowPrices(false);
        }
    }

    public function _afterLoad()
    {
        parent::_afterLoad();
        if (!$this->isObjectNew()) {
            $this->loadQuoteData();
            $this->checkExpired();
        }
    }

    public function checkExpired()
    {
        if ($this->isActive()) {
            if (strtotime($this->getExpires()) < time()) {
                $this->setStatusId(self::STATUS_QUOTE_EXPIRED);
                $this->setUpdatedAt(time());
                $this->save();
            }
        }

        return !$this->isActive();
    }

    /**
     * Get All Quote Cusotmer IDs
     * 
     */
//    private function _getCustomerIdsData()
    private function _getCustomerIdsData()
    {
        if (empty($this->_availableCustomerIds)) {
            $customers = $this->quotesResourceQuoteCustomerCollectionFactory->create();
            /* @var $notes Mage_Core_Model_Resource_Collection_Abstract */
            $customers->addFieldToFilter('quote_id', $this->getId());
            foreach ($customers->getItems() as $customer) {
                /* @var $customer Epicor_Quotes_Model_Quote_Customer */
                $this->_availableCustomerIds[] = $customer->getCustomerId();
            }
        }
    }

    /**
     * 
     * @param integer $customerId
     */
    public function addCustomerId($customerId)
    {
        if (!$this->hasCustomerId($customerId) && !in_array($customerId, $this->_newCustomerIds)) {
            $this->_newCustomerIds[] = $customerId;
        }
    }

    public function deleteCustomersFromQuote()
    {
        $customers = $this->quotesResourceQuoteCustomerCollectionFactory->create();
        /* @var $notes Mage_Core_Model_Resource_Collection_Abstract */
        $customers->addFieldToFilter('quote_id', $this->getId());
        foreach ($customers as $customer) {
            /* @var $customer Epicor_Quotes_Model_Quote_Customer */
            $customer->delete();
        }

        $this->_availableCustomerIds = array();
    }

    /**
     * 
     * @param integer $customerId
     */
    public function hasCustomerId($customerId)
    {
        return in_array($customerId, $this->_availableCustomerIds);
    }

    public function canBeAccessedByCustomer($customer, $erpAccount = null)
    {
        if (is_null($erpAccount)) {
            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        }

        $access = false;

        if ($this->hasCustomerId($customer->getId()) || $this->getIsGlobal() && $this->getErpAccountId() == $erpAccount->getId()) {
            $access = true;
        }

        return $access;
    }

    /**
     * Get All Quote Notes
     * 
     * @return \Epicor\Quotes\Model\ResourceModel\Quote\Note\Collection
     */
    private function _getNotesData()
    {
        if (empty($this->_availableNotes)) {
            $notes = $this->quotesResourceQuoteNoteCollectionFactory->create();
            /* @var $notes Mage_Core_Model_Resource_Collection_Abstract */
            $notes->addFieldToFilter('quote_id', $this->getId());
            $notes->setOrder('created_at', 'DESC');
            foreach ($notes->getItems() as $note) {
                $this->setData('note_' . $note->getId(), $note);
                $this->_availableNotes[$note->getId()] = $note->getId();
            }
        }
    }

    /**
     * get all Quote Products
     * 
     * @return \Epicor\Quotes\Model\ResourceModel\Quote\Product\Collection
     */
    private function _getProductsData()
    {
        if (empty($this->_availableProducts)) {
            $lines = $this->quotesResourceQuoteProductCollectionFactory->create();
            /* @var $lines Mage_Core_Model_Resource_Collection_Abstract */
            $lines->addFieldToFilter('quote_id', $this->getId());

            $maxId = 0;

            foreach ($lines->getItems() as $line) {
                /* @var $product Epicor_Quotes_Model_Quote_Product */
                $line->setDataChanges(false);
                $this->setData('line_' . $line->getId(), $line);
                $this->_availableProducts[$line->getId()] = $line->getProductId();
                if ($line->getId() > $maxId) {
                    $maxId = $line->getId();
                }
            }

            $this->_lineNum = $maxId + 1;
        }
    }

    public function isActive()
    {
        return in_array(
            $this->getStatusId(), array(
            self::STATUS_AWAITING_ACCEPTANCE,
            self::STATUS_PENDING_RESPONSE,
            self::STATUS_QUOTE_ACCEPTED,
            null
            )
        );
    }

    public function hasExpired()
    {
        return ($this->getStatusId() == self::STATUS_QUOTE_EXPIRED);
    }

    public function getExpires()
    {

        if (is_int($this->getData('expires')))
            return date('Y-m-d', $this->getData('expires'));

        return $this->getData('expires');
    }

    public function getStatus()
    {
        return $this->_quoteStatuses[$this->getStatusId() ?: self::STATUS_PENDING_RESPONSE];
    }

    public function getQuoteStatuses()
    {
        return $this->_quoteStatuses;
    }

    public function isAcceptable()
    {
        $acceptable = false;

        $okStatuses = array(
            self::STATUS_AWAITING_ACCEPTANCE,
            self::STATUS_QUOTE_ACCEPTED
        );

        if (in_array($this->getStatusId(), $okStatuses)) {
            $acceptable = true;
        }

        return $acceptable;
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer($onlyFirst = false)
    {
        if (!$this->getData('customer')) {

            if ($this->isObjectNew()) {
                $customers = $this->_newCustomerIds;
            } else {
                $collection = $this->quotesResourceQuoteCustomerCollectionFactory->create();
                /* @var $collection Epicor_Quotes_Model_Resource_Quote_Customer_Collection */

                $collection->addFieldToFilter('quote_id', $this->getId());

                $customers = $collection->getItems();
            }

            // check the config for the customer scope
            // 1 - customers per website
            // 0 - global customers


            if (!$onlyFirst && $this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $customer = $this->customerCustomerFactory->create();

                foreach ($customers as $quoteCustomer) {
                    /* @var $quoteCustomer Epicor_Quotes_Model_Quote_Customer */

                    $customerId = (is_numeric($quoteCustomer)) ? $quoteCustomer : $quoteCustomer->getCustomerId();

                    $customerModel = $this->customerCustomerFactory->create()->load($customerId);
                    /* @var $customerModel Epicor_Comm_Model_Customer */

                    if ($customerModel->getWebsiteId() == $this->storeManager->getWebsite()->getId() || $this->storeManager->getWebsite()->getId() == 0) {
                        $customer = $customerModel;
                        break;
                    }
                }
            } else {
                if ($this->isObjectNew()) {
                    $customerId = array_pop($customers);
                } else {
                    $customerId = $collection->getFirstItem()->getCustomerId();
                }

                $customer = $this->customerCustomerFactory->create()->load($customerId);
                /* @var $customer Epicor_Comm_Model_Customer */
            }

            $this->setData('customer', $customer);
        }

        return $this->getData('customer');
    }

    /**
     * 
     * @param bool $admin
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getCustomerGroup($admin = false)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->getErpAccountInfo($this->getErpAccountId());
    }

    /**
     * Adds a note to the quote
     * 
     * @param string $note
     * @param integer $admin
     * @param boolean $visible
     * @param boolean $private
     * @param boolean $sendEmail
     */
    public function addNote($note, $admin = null, $visible = true, $private = false, $sendEmail = false)
    {
        $quoteNotes = $this->scopeConfig->getValue('epicor_quotes/notes/quote_note_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($quoteNotes == 'single') {
            $notes = $this->getNotes();
            if (count($notes) == 0) {
                $addNote = true;
            } else {
                $addNote = false;
                $singleNote = array_pop($notes);
                $updatedText = $this->formatNoteText($singleNote->getNote(), $note, $admin);
                $singleNote->setNote($updatedText);
                $this->setData('note_' . $singleNote->getId(), $singleNote);
            }
        } else {
            $addNote = true;
        }

        if ($addNote) {
            $note = $this->formatNoteText('', $note, $admin);
            $quoteNote = $this->quotesQuoteNoteFactory->create();
            /* @var $quoteNote Epicor_Quotes_Model_Quote_Note */
            $quoteNote->setNote($note);
            $quoteNote->setAdminId($admin);
            $quoteNote->setIsVisible($visible);
            $quoteNote->setIsPrivate($private);
            $quoteNote->setCreatedAt(time());
            $quoteNote->setSendEmail($sendEmail);

            $this->_newNotes[] = $quoteNote;

            if (($visible && !$private && $admin == NULL) || $admin != NULL) {
                $this->registry->register('latestNote', $note);
            }
        }

        $this->setDataChanges(true);
    }

    /**
     * Updates a note
     * 
     * @param \Epicor\Quotes\Model\Quote\Note $note
     */
    public function formatNoteText($oldNote, $newNote, $isAdmin = false)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        $quoteNotes = $this->scopeConfig->getValue('epicor_quotes/notes/quote_note_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($quoteNotes == 'single') {
            $singleNote = $this->scopeConfig->getValue('epicor_quotes/notes/single_note_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $person = $isAdmin ? 'Admin' : $this->getCustomer()->getName();

            if ($singleNote == 'simple') {
                //M1 > M2 Translation Begin (Rule 32)
                //$date = $helper->getLocalDate(time(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
                $date = $helper->getLocalDate(time(), \IntlDateFormatter::MEDIUM, true);
                //M1 > M2 Translation End
                $oldNote = !empty($oldNote) ? $oldNote . "\n\n" : $oldNote;

                $formattedNote = $oldNote
                    . $newNote . "\n"
                    . __(' - Note Added By ') . $person . __(' on ') . $date;
            } else {
                $date = date('Y-m-d H:i:s');

                $oldNote = !empty($oldNote) ? $oldNote : $oldNote;

                $formattedNote = $oldNote
                    . '[name:' . $person . ']' . "\n"
                    . '[date:' . $date . ']' . "\n"
                    . $newNote;
            }
        } else {
            $formattedNote = $newNote;
        }

        return $formattedNote;
    }

    public function splitFormattedNote($note)
    {
        preg_match_all('/\[name\:([^[]*)\][\s]+\[date\:([^[]*)\][\s]+([^[]*)/', $note->getNote(), $matches);
        $formattedNotes = array();
        foreach ($matches[1] as $x => $name) {
            $formattedNotes[$x] = $this->quotesQuoteNoteFactory->create();
            $formattedNotes[$x]->setName($name);
            $formattedNotes[$x]->setIsFormatted(true);
        }

        foreach ($matches[2] as $x => $date) {
            $formattedNotes[$x]->setCreatedAt($date);
        }

        foreach ($matches[3] as $x => $note) {
            $formattedNotes[$x]->setNote($note);
        }
        return $formattedNotes;
    }

    /**
     * Adds a note to the quote, from a provided object
     * 
     * @param \Epicor\Quotes\Model\Quote\Note $note
     */
    public function addNoteObject($quoteNote)
    {
        $this->_newNotes[] = $quoteNote;
        $this->setDataChanges(true);
    }

    /**
     * Forces a refresh of notes data
     * 
     * used by submitnewnoteAction so that is renders the notes in date order
     */
    public function refreshNotes()
    {
        $this->_availableNotes = array();
        $this->_getNotesData();
    }

    /**
     * Get All Quote Notes
     * 
     * @return array
     */
    public function getNotes()
    {
        $notes = array();
        foreach ($this->_availableNotes as $noteId) {
            $notes[$noteId] = $this->getData('note_' . $noteId);
        }
        return $notes;
    }

    /**
     * Get All Quote Notes
     * 
     * @return \Epicor\Quotes\Model\Quote\Note
     */
    public function getNote($noteId)
    {
        return $this->getData('note_' . $noteId);
    }

    /**
     * Checks if quote has values for requested note id
     * 
     * @param integer $noteId
     * 
     * @return bool
     */
    public function hasNote($noteId)
    {
        return isset($this->_availableNotes[$noteId]);
    }

    /**
     * Updates a note
     * 
     * @param \Epicor\Quotes\Model\Quote\Note $note
     */
    public function updateNote($note)
    {
        $this->setDataChanges(true);
        return $this->setData('note_' . $note->getId(), $note);
    }

    /**
     * Add Quote_Product to Quote
     * 
     * @param \Epicor\Quotes\Model\Quote\Product $quoteProduct
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function setNoteErpRef($erpRef, $noteId)
    {
        $this->getNote($noteId)->setErpRef($erpRef);
        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Get All Visible Public Quote Notes
     * 
     * @return \Epicor\Quotes\Model\ResourceModel\Quote\Note\Collection
     */
    public function getVisibleNotes()
    {
        $notes = $this->quotesResourceQuoteNoteCollectionFactory->create();
        /* @var $notes Mage_Core_Model_Resource_Collection_Abstract */
        $notes->addFieldToFilter('quote_id', $this->getId());
        $notes->addFieldToFilter('is_visible', true);
        $notes->addFieldToFilter('is_private', false);
        $notes->setOrder('created_at', 'DESC');
        return $notes->getItems();
    }

    /**
     * Get All Non Visible Public Quote Notes
     * 
     * @return \Epicor\Quotes\Model\ResourceModel\Quote\Note\Collection
     */
    public function getNonVisibleNotes()
    {
        $notes = $this->quotesResourceQuoteNoteCollectionFactory->create();
        /* @var $notes Mage_Core_Model_Resource_Collection_Abstract */
        $notes->addFieldToFilter('quote_id', $this->getId());
        $notes->addFieldToFilter('is_visible', false);
        $notes->addFieldToFilter('is_private', false);
        $notes->setOrder('created_at', 'ASC');
        return $notes;
    }

    /**
     * Add Quote_Product to Quote
     * 
     * @param \Epicor\Quotes\Model\Quote\Product $quoteProduct
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function addItem($quoteProduct)
    {
        $productId = $quoteProduct->getProductId();
        $this->_availableProducts[$this->_lineNum] = $productId;

        $this->setData('line_' . $this->_lineNum, $quoteProduct);
        $this->_lineNum++;

        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Updates a quote product
     * 
     * @param \Epicor\Quotes\Model\Quote\Product $quoteProduct
     */
    public function updateItem($quoteProduct)
    {
        $this->setDataChanges(true);

        if ($this->hasItem($quoteProduct->getId())) {
            $this->setData('line_' . $quoteProduct->getId(), $quoteProduct);
        } else {
            $this->addItem($quoteProduct);
        }

        return $this;
    }

    /**
     * Removes a product from the quote
     * 
     * @param integer $productId
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function removeItem($lineId)
    {
        if ($this->hasItem($lineId)) {
            $this->_deletedProducts[$lineId] = $lineId;
            unset($this->_availableProducts[$lineId]);
            $this->setDataChanges(true);
        }
        return $this;
    }

    /**
     * Set NewQty for the product on the given line
     * 
     * @param \Epicor\Quotes\Model\Quote\Product $quoteProduct
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function setProductNewQty($qty, $lineId)
    {
        $this->getProductData($lineId)->setNewQty($qty);
        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Set NewPrice for the given product
     * 
     * @param \Epicor\Quotes\Model\Quote\Product $quoteProduct
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function setProductNewPrice($price, $lineId)
    {

        $this->getProductData($lineId)->setNewPrice($price);
        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Set ErpNoteRef for the given product
     * 
     * @param \Epicor\Quotes\Model\Quote\Product $quoteProduct
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function setProductErpNoteRef($erpRef, $lineId)
    {
        $this->getProductData($lineId)->setErpNoteRef($erpRef);
        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Get Quote Product data
     * 
     * @param integer $productId
     * 
     * @return \Epicor\Quotes\Model\Quote\Product
     */
    public function getProductData($lineId, $erpLineNumber = null)
    {
        if ($this->hasItem($lineId)) {
            $product = $this->getData('line_' . $lineId);
        } else {
            $product = $this->quotesQuoteProductFactory->create();
            if (!empty($erpLineNumber)) {
                foreach ($this->getProducts() as $line) {
                    /* @var $line Epicor_Quotes_Model_Quote_Product */
                    if ($line->getErpLineNumber() == $erpLineNumber) {
                        $product = $this->getData('line_' . $line->getId());
                    }
                }
            }
        }

        return $product;
    }

    /**
     * Checks if quote has values for requested line id
     * 
     * @param integer $productId
     * 
     * @return bool
     */
    public function hasItem($lineId)
    {
        return isset($this->_availableProducts[$lineId]);
    }

    /**
     * get all Quote Products
     * 
     * @return \Epicor\Quotes\Model\ResourceModel\Quote\Product\Collection
     */
    public function getProducts()
    {
        $products = array();
        foreach ($this->_availableProducts as $line => $productId) {
            $products[$line] = $this->getData('line_' . $line);
        }

        return $products;
    }

    public function productsSaleable()
    {
        $productsSaleable = true;

        foreach ($this->getProducts() as $epicQuoteProduct) {
            /* @var $epicQuoteProduct Epicor_Quotes_Model_Quote_Product */

            $product = $epicQuoteProduct->getProduct();
            /* @var $product Mage_Catalog_Model_Product */

            if (!$product->isSaleable()) {
                $productsSaleable = false;
            }
        }

        return $productsSaleable;
    }

    public function getSubtotal()
    {
        $subtotal = 0;

        foreach ($this->getProducts() as $product) {
            /* @var $product Epicor_Quotes_Model_Quote_Product */
            $subtotal += ($product->getNewPrice() * $product->getNewQty());
        }

        return $subtotal;
    }

    public function getTaxTotal()
    {
        $taxTotal = 0;

        foreach ($this->getProducts() as $quoteProduct) {
            /* @var $quoteProduct Epicor_Quotes_Model_Quote_Product */
            $row_total = ($quoteProduct->getNewPrice() * $quoteProduct->getNewQty());
            $productTax = $this->getProductTax($quoteProduct->getProduct(), $row_total);
            $taxTotal += $productTax;
        }

        return $taxTotal;
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param float $price
     * @return type
     */
    public function getProductTax($product, $price)
    {
        $taxHelper = $this->taxCalculation;

        $store = $this->storeManager->getStore($this->getStoreId());
        $customer = $this->getCustomer();

        $taxHelper = $this->taxCalculation;
        $taxHelper->setCustomer($customer);
        $taxRequest = $taxHelper->getRateRequest(null, null, null, $store);
        $taxRequest->setProductClassId($product->getTaxClassId());
        $rate = $taxHelper->getRate($taxRequest);
        $tax = $taxHelper->calcTaxAmount($price, $rate);

        return $tax;
    }

    public function getGrandTotal()
    {
        return $this->getSubtotal() + $this->getTaxTotal();
    }

    /**
     * 
     * @param \Epicor\Quotes\Model\Quote $epic_quote
     */
    public function setQuoteAsCart()
    {
        try {

            $cart = $this->checkoutCart;
            /* @var $cart Mage_Checkout_Model_Cart */
            //$cart->truncate();
            $quote = $cart->getQuote();
            $cart->truncate();
            /* @var $quote Epicor_Comm_Model_Quote */

            $eccData = [
                'ecc_bsv_goods_total' => null, 
                'ecc_bsv_goods_total_inc' => null, 
                'ecc_bsv_carriage_amount' => null, 
                'ecc_bsv_carriage_amount_inc' => null, 
                'ecc_bsv_grand_total' => null, 
                'ecc_bsv_grand_total_inc' => null
            ];
            
            $quote->addData($eccData);
            if ($quote->getShippingAddress()) {
                $quote->getShippingAddress()->addData($eccData);
            }
            
            $quote->setEccQuoteId($this->getId());
            $quote->setEccErpQuoteId($this->getQuoteNumber());
            $quote->setAllowSaving(true);
            $quote->save();
            //print_r($quote->getData()); die('sss');
            $cart->setQuote($quote);

            $helper = $this->commonCartHelper;
            /* @var $helper Epicor_Common_Helper_Cart */

            foreach ($this->getProducts() as $epicQuoteProduct) {
                /* @var $epicQuoteProduct Epicor_Quotes_Model_Quote_Product */

                // convert any options for the product so they can be re-added to the cart
                $rawOptions = $epicQuoteProduct->getProductOptions();
                $customOptions = array();
                if (!empty($rawOptions)) {
                    foreach ($rawOptions as $option) {
                        $customOptions[$option['option_id']] = $option['option_value'];
                    }
                }

                $options = array(
                    'qty' => $epicQuoteProduct->getNewQty(),
                    'location_code' => $epicQuoteProduct->getLocationCode(),
                    'custom_options' => $customOptions,
                    'force_price' => $epicQuoteProduct->getNewPrice(),
                    'original_price' => $epicQuoteProduct->getOrigPrice(),
                    'gqr_line_number' => $epicQuoteProduct->getId()
                );

                $quote->addLine($epicQuoteProduct->getProduct(), $options);
            }
        } catch (\Exception $e) {
            $this->generic->addError($e->getMessage());
        } catch (Mage_Exception $e) {
            $this->generic->addError($e->getMessage());
        }

        $this->checkoutSession->setCartWasUpdated(true);
        $cart->save();
        return true;
    }

    public function sendCustomerUpdate()
    { 
        $enabled = $this->scopeConfig->isSetFlag('epicor_quotes/email_alerts/send_customer_update_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gqr_no_email_to_customer = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gqr_request/submit_to_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        if ($enabled && $this->getSendCustomerUpdates()) {
        if ($enabled && $this->getSendCustomerUpdates() && $gqr_no_email_to_customer == 'N') {
            $customerStates = explode(',', $this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_states', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if (in_array($this->getStatusId(), $customerStates)) {
                $newNote = $this->registry->registry('latestNote');
                $customerGroup = $this->getCustomerGroup(true);
                $customer = $this->getCustomer(true);

                $storeId = $this->getStoreId();
                $from = $this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_update_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                $sender = [
                    'name' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                    'email' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                ];
                $to = $customer->getEmail();
                $name = $customer->getName();
                $translate = $this->translationStateInterface;
                /* @var $translate Mage_Core_Model_Translate */
                $translate->suspend();
                $routeParams = ['id' => $this->getId(), '_nosid' => true, '_scope' => $storeId];
                $vars = array(
                    'name' => $name,
                    'epicquote' => $this,
                    'epicquotereference' => $this->getReference() ?: $this->getId(),
                    'lastcomment' => nl2br($newNote),
                    'commentExists' => !empty($newNote),
                    //M1 > M2 Translation Begin (Rule p2-4)
                    //'myQuotesUrl' => Mage::getUrl('quotes/manage/view', array('id' => $this->getId()))
                    'myQuotesUrl' => $this->urlBuilder->getUrl('quotes/manage/view', $routeParams)
                    //M1 > M2 Translation End
                );
                $helper = $this->quotesHelper;
                /* @var $helper Epicor_Quotes_Helper_Data */

                if (empty($to)) {
                    foreach ($customerGroup->getCustomers()->getItems() as $groupCustomer) {
                        /* @var $groupCustomer Epicor_Comm_Model_Customer */
                        $to = $groupCustomer->getEmail();
                        $name = $groupCustomer->getName();
                       
                        $helper->sendTransactionalEmail($this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_update_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), $sender, $to, $name, $vars, $storeId);
                    }
                } else {  
                    $helper->sendTransactionalEmail($this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_update_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), $sender, $to, $name, $vars, $storeId);
                }
                $translate->resume();
            }
        }
    }

//    private function sendAdminUpdate()
    public function sendAdminUpdate()
    {
        $enabled = $this->scopeConfig->isSetFlag('epicor_quotes/email_alerts/send_admin_update_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gqr_no_email_to_customer = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gqr_request/submit_to_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($enabled && $gqr_no_email_to_customer == 'N' && $this->getSendAdminUpdates()) {
            $adminStates = explode(',', $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_states', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if (in_array($this->getStatusId(), $adminStates)) {
                $newNote = $this->registry->registry('latestNote');
                $customerGroup = $this->getCustomerGroup(true);
                $expires = $this->quotesHelper->getHumanExpires($this);

                $storeId = $this->getStoreId();
                $from = $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_update_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                $sender = [
                    'name' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                    'email' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                ];
                $to = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                $name = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

                $translate = $this->translationStateInterface;
                /* @var $translate Mage_Core_Model_Translate */
                $translate->suspend();
                $vars = array(
                    'adminname' => $name,
                    'epicquote' => $this,
                    'epicquotereference' => $this->getReference() ?: $this->getId(),
                    'customer' => $this->getCustomer(true),
                    'customerGroupName' => $customerGroup->getName(),
                    'lastcomment' => nl2br($newNote),
                    'commentExists' => !empty($newNote)
                );
                /*
                $this->emailTemplateFactory->create()
                    ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                    ->sendTransactional(
                        $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_update_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), $setting, $to, $name, $vars
                );
                */
                try {
                    $template = $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_update_email_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                    $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
                    $mail = $this->transportBuilder->setTemplateIdentifier($template)
                                    ->setTemplateOptions($templateOptions)
                                    ->setTemplateVars($vars)
                                    ->setFrom($sender)
                                    ->addTo($to)
                                    ->getTransport();
                    $mail->sendMessage();
                    $translate->resume();
                    //$mail->getSentSuccess();
                } catch (\Exception $e) {
                     $translate->resume();
                }
                $translate->resume();
            }
        }
    }

    public function sendCustomerReminderEmail()
    {
        $gqr_no_email_to_customer = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gqr_request/submit_to_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->getValue('epicor_quotes/email_alerts/send_reminders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (in_array($enabled, array('customer', 'both')) && $this->getSendCustomerReminders() && $gqr_no_email_to_customer == 'N') {
            $this->sendReminderEmail('customer');
        }
    }

    public function sendAdminReminderEmail()
    {
        $gqr_no_email_to_customer = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gqr_request/submit_to_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->getValue('epicor_quotes/email_alerts/send_reminders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (in_array($enabled, array('admin', 'both')) && $this->getSendAdminReminders() && $gqr_no_email_to_customer == 'N') {
            $this->sendReminderEmail('admin');
        }
    }

    private function sendReminderEmail($type)
    {
        $customerGroup = $this->getCustomerGroup(true);
        $storeId = $this->getStoreId();
        $from = $this->scopeConfig->getValue('epicor_quotes/email_alerts/reminder_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $sender = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
            'email' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
        ];
        if ($type == 'admin') {
            $to = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $name = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $to = $this->getCustomer(true)->getEmail();
            $name = $this->getCustomer(true)->getName();
        }

        $translate = $this->translationStateInterface;
        /* @var $translate Mage_Core_Model_Translate */
        $translate->disable();
        $routeParams = ['id' => $this->getId(), '_nosid' => true, '_scope' => $storeId];
        $vars = array(
            'name' => $name,
            'epicquote' => $this,
            'quotestatus' => $this->getStatus(),
            'epicquotereference' => $this->getReference() ?: $this->getId(),
            //M1 > M2 Translation Begin (Rule p2-4)
            //'myQuotesUrl' => Mage::getUrl('quotes/manage/view', array('id' => $this->getId())),
            'myQuotesUrl' => $this->urlBuilder->getUrl('quotes/manage/view', $routeParams),
            //M1 > M2 Translation End
            'admin' => ($type == 'admin') ? true : false,
            'customer' => $this->getCustomer(true),
            'customerGroupName' => $customerGroup->getName(),
            'expires' => $this->quotesHelper->getHumanExpires($this)
        );
        /*
        $this->emailTemplateFactory->create()
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $this->scopeConfig->getValue('epicor_quotes/email_alerts/reminder_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), $setting, $to, $name, $vars
        );
        */
        try {
            $template = $this->scopeConfig->getValue('epicor_quotes/email_alerts/reminder_email_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
            $mail = $this->transportBuilder->setTemplateIdentifier($template)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($vars)
                            ->setFrom($sender)
                            ->addTo($to)
                            ->getTransport();
            $mail->sendMessage();
            $translate->resume();
            //$mail->getSentSuccess();
        } catch (\Exception $e) {
             $translate->resume();
        }
        
    }

}
