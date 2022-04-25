<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer;


/**
 * Customer Return
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setErpReturnsNumber(string $value)
 * @method setWebReturnsNumber(string $value)
 * @method setRmaDate(string $value)
 * @method setReturnsStatus(string $value)
 * @method setCustomerReference(string $value)
 * @method setAddressCode(string $value)
 * @method setCustomerName(string $value)
 * @method setCreditInvoiceNumber(string $value)
 * @method setRmaCaseNumber(string $value)
 * @method setRmaContact(string $value)
 * @method setEmailAddress(string $value)
 * @method setNoteText(string $value)
 * @method setPreviousData(string $value)
 * @method setIsGlobal(bool $value)
 * @method setErpAccountId(int $value)
 * @method setCustomerId(int $value)
 * @method setActions(string $value)
 * @method setSubmitted(string $value)
 * @method setStoreId(int $value)
 * @method setErpSyncAction(string $value)
 * @method setErpSyncStatus(string $value)
 * @method setLastErpStatus(string $value)
 * @method setLastErpErrorDescription(string $value)
 * 
 * @method string getErpReturnsNumber()
 * @method string getWebReturnsNumber()
 * @method string getRmaDate()
 * @method string getReturnsStatus()
 * @method string getCustomerReference()
 * @method string getAddressCode()
 * @method string getCustomerName()
 * @method string getCreditInvoiceNumber()
 * @method string getRmaCaseNumber()
 * @method string getRmaContact()
 * @method string getEmailAddress()
 * @method string getNoteText()
 * @method string getPreviousData()
 * @method bool getIsGlobal()
 * @method int getErpAccountId()
 * @method int getCustomerId()
 * @method string getActions()
 * @method int getSubmitted()
 * @method int getStoreId()
 * @method string getErpSyncAction()
 * @method string getErpSyncStatus()
 * @method string getLastErpStatus()
 * @method string getLastErpErrorDescription()
 * 
 */
class ReturnModel extends \Epicor\Database\Model\Customer\ReturnModel
{

    protected $_eventPrefix = 'ecc_customer_return';
    protected $_eventObject = 'customer_return';

    /**
     *
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line\Collection
     */
    protected $_lines = null;
    protected $_attachments = null;
    protected $_attachmentIds = array();
    protected $_attachmentLinks = array();
    protected $_erpAccount = null;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line\CollectionFactory
     */
    protected $commResourceCustomerReturnModelLineCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory
     */
    protected $commResourceCustomerReturnModelAttachmentCollectionFactory;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel\AttachmentFactory
     */
    protected $commCustomerReturnModelAttachmentFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
     /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

     /**
     * Instance of import/export resource helper
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line\CollectionFactory $commResourceCustomerReturnModelLineCollectionFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory $commResourceCustomerReturnModelAttachmentCollectionFactory,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Model\Customer\ReturnModel\AttachmentFactory $commCustomerReturnModelAttachmentFactory,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commResourceCustomerReturnModelLineCollectionFactory = $commResourceCustomerReturnModelLineCollectionFactory;
        $this->commHelper = $commHelper;
        $this->commResourceCustomerReturnModelAttachmentCollectionFactory = $commResourceCustomerReturnModelAttachmentCollectionFactory;
        $this->commonFileFactory = $commonFileFactory;
        $this->customerSession = $customerSession;
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->scopeConfig = $scopeConfig;
        $this->commFileHelper = $commFileHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_encryptor  = $encryptor;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commCustomerReturnModelAttachmentFactory = $commCustomerReturnModelAttachmentFactory;
        $this->eventManager = $context->getEventDispatcher();
        $this->commReturnsHelper = $commReturnsHelper;
        $this->storeManager = $storeManager;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->urlBuilder = $urlBuilder;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\ReturnModel');
    }

    public function reloadChildren()
    {
        $this->_lines = null;
        $this->_attachments = null;
        $this->_attachmentIds = array();
        $this->_attachmentLinks = array();

        $this->getLines();
        $this->getAttachments();
    }

    /**
     * Attachments
     */

    /**
     * Get a Collection of Return Lines
     * 
     * @return \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line\Collection
     */
    public function getLines()
    {
        if ($this->_lines === null) {
            $lines = $this->commResourceCustomerReturnModelLineCollectionFactory->create();
            $lines->addFieldToFilter('return_id', array('eq' => $this->getId()));
            $lines = $lines->load()->getItems();
            $this->_lines = array();
            foreach ($lines as $x => $line) {
                /* @var $line \Epicor\Comm\Model\Customer\ReturnModel\Line */
                $line->setParent($this);
                $this->_lines[$x] = $line;
            }
        }
        return $this->_lines;
    }

    /**
     * Get erp account for return customer
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpAccount()
    {

        if (empty($this->_erpAccount)) {

            $helper = $this->commHelper;
            /* @var $helper \Epicor\Comm\Helper\Data */
            $this->_erpAccount = $helper->getErpAccountInfo($this->getErpAccountId());
        }

        return $this->_erpAccount;
    }

    /**
     * Add/ update a line
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel\Line $line
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function addLine($line)
    {
        if ($this->_lines === null) {
            $this->getLines();
        }

        $id = $line->getId();

        if (!empty($id)) {
            $this->_lines[$id] = $line;
        } else {
            $this->_lines[] = $line;
        }

        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Get a line by id
     * 
     * @param integer $id
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel\Line
     */
    public function getLine($id, $erpId = false)
    {
        if ($this->_lines === null) {
            $this->getLines();
        }

        $line = false;

        if (isset($this->_lines[$id])) {
            $line = $this->_lines[$id];
        } else if (!empty($erpId)) {
            foreach ($this->_lines as $l) {
                /* @var $l \Epicor\Comm\Model\Customer\ReturnModel\Line */
                if ($l->getErpLineNumber() == $erpId) {
                    $line = $l;
                }
            }
        }

        return $line;
    }

    /**
     * Deletes a line from the retur
     * 
     * @param integer $id - id of the line
     * @param boolean $force - whether to force delete the local data
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function deleteLine($id, $force = false)
    {
        $line = $this->getLine($id);

        if ($line) {
            if ($line->getErpLineNumber() && !$force) {
                $line->setToBeDeleted('Y');
            } else {
                $line->delete();
                unset($this->_lines[$id]);
            }
        }

        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Attachments
     */

    /**
     * Get a Collection of Attachments
     * 
     * @return \Epicor\Common\Model\ResourceModel\Attachment
     */
    public function getAttachments()
    {

        if ($this->_attachments === null) {
            $collection = $this->commResourceCustomerReturnModelAttachmentCollectionFactory->create();
            $collection->addFieldToFilter('return_id', array('eq' => $this->getId()));
            $collection->addFieldToFilter('line_id', array('null' => true));

            $this->_attachments = array();
            $this->_attachmentIds = array();
            $this->_attachmentLinks = array();

            foreach ($collection->getItems() as $x => $att) {
                /* @var $file \Epicor\Comm\Model\Customer\ReturnModel\Attachment */
                $id = $att->getAttachmentId();
                $this->_attachmentIds[$id] = $x;
                $this->_attachmentLinks[$id] = $att;
                $this->_attachments[$x] = $this->commonFileFactory->create()->load($id);
            }
        }

        return $this->_attachments;
    }

    /**
     * Adds an attachment
     * 
     * @param \Epicor\Common\Model\File $attachment
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function addAttachment($attachment)
    {
        if ($this->_attachments === null) {
            $this->getAttachments();
        }

        if ($this->hasAttachment($attachment->getId())) {
            $link = $this->getAttachmentLink($attachment->getId());
            $link->setToBeDeleted('N');
            $this->_attachmentLinks[$attachment->getId()] = $link;
            $this->_attachments[$this->_attachmentIds[$attachment->getId()]] = $attachment;
        } else {
            $this->_attachments[$attachment->getId()] = $attachment;
        }

        $this->setDataChanges(true);

        return $this;
    }

    /**
     * Get an Attachment by id
     * 
     * @param integer $id
     * @param integer $erpId
     * 
     * @return \Epicor\Common\Model\File
     */
    public function getAttachment($id, $erpId = false)
    {
        if ($this->_attachments === null) {
            $this->getAttachments();
        }

        $attachment = false;

        if ($this->hasAttachment($id)) {
            $attachment = $this->_attachments[$this->_attachmentIds[$id]];
        } else {
            foreach ($this->_attachments as $a) {
                /* @var $a \Epicor\Common\Model\File */
                if ($a->getErpId() == $erpId) {
                    $attachment = $a;
                }
            }
        }

        return $attachment;
    }

    /**
     * Deletes an attachment from the retur
     * 
     * @param integer $id - id of the attachment
     * @param boolean $force - whether to force delete the local data
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function deleteAttachment($id, $force = false)
    {
        $attachment = $this->getAttachment($id);

        if ($attachment) {
            $link = $this->getAttachmentLink($id);
            if ($attachment->getErpId() && !$force) {
                $link->setToBeDeleted('Y');
                $this->_attachmentLinks[$id] = $link;
            } else {
                if ($attachment->getErpId()) {
                    $attachment->setAction('R');
                    $attachment->save();
                } else {
                    $attachment->delete();
                }

                $link->delete();
                unset($this->_attachmentLinks[$id]);
                unset($this->_attachments[$this->_attachmentIds[$id]]);
                unset($this->_attachmentIds[$id]);
            }
        }

        $this->setDataChanges(true);

        return $this;
    }

    public function hasAttachment($attachmentId)
    {
        return isset($this->_attachmentIds[$attachmentId]);
    }

    /**
     * Get an Attachment Link by attachment id
     * 
     * @param integer $id
     * 
     * @return \Epicor\Common\Model\File
     */
    public function getAttachmentLink($id)
    {
        if ($this->_attachments === null) {
            $this->getAttachments();
        }

        if ($this->hasAttachment($id)) {
            $attachment = $this->_attachmentLinks[$id];
        } else {
            $attachment = false;
        }

        return $attachment;
    }

    public function canBeAccessedByCustomer($customer = null)
    {
        $session = $this->customerSession;
        /* @var $session \Magento\Customer\Model\Session */

        $access = false;

        if ($session->isLoggedIn()) {

            if ($customer == null) {
                $customer = $session->getCustomer();
                /* @var $customer \Epicor\Comm\Model\Customer */
            }

            $commHelper = $this->commHelper;
            /* @var $commHelper \Epicor\Comm\Helper\Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $defaults = $erpAccount->getDefaultForStores();
            $b2b = (!$defaults) ? true : false;

            if ($customer->isCustomer()) {
                if ($b2b && $this->getErpAccountId() == $erpAccount->getId()) {
                    $access = true;
                }
            } else if ($this->hasCustomerId($customer->getId())) {
                $access = true;
            }
        } else {
            $customerId = $this->getCustomerId();

            $sessionEmail = $session->getReturnGuestEmail();
            $returnEmail = $this->getEmailAddress();

            //if (empty($customerId) && strtolower($sessionEmail) == strtolower($returnEmail)) {
            if (strtolower($sessionEmail) == strtolower($returnEmail)) {
                $access = true;
            }
        }

        return $access;
    }

    public function hasCustomerId($customerId)
    {
        return $customerId == $this->getCustomerId();
    }

    public function getPreviousErpData()
    {
        $previousData = $this->getPreviousData();
        if (!empty($previousData)) {
            $previous = $this->commCustomerReturnModelFactory->create();
            /* @var $previous \Epicor\Comm\Model\Customer\ReturnModel */
            $previous->setData(unserialize($previousData));
        } else {
            $previous = array();
        }

        return $previous;
    }

    /**
     * Sends a CRRU to the ERP for this message
     * 
     * @return boolean
     */
    public function sendToErp()
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        if ($helper->isMessageEnabled('epicor_comm', 'crru')) {
            $data = array(
                'return' => $this
            );

            $message = $helper->sendErpMessage('epicor_comm', 'crru', $data, array(), array('setCustomerGroupId' => array($this->getErpAccountId())));
            $success = $message['success'];

            $this->syncErpFiles();
        }

        return $success;
    }

    /**
     * Syncs this returns files witht he ERP (if FSUB is set to instant)
     */
    public function syncErpFiles()
    {
        $frequency = $this->scopeConfig->getValue('epicor_comm_enabled_messages/fsub_request/frequency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sendFsub = ($frequency == 'instant') ? true : false;
        $commFileHelper = $this->commFileHelper;
        /* @var $commFileHelper \Epicor\Comm\Helper\File */

        foreach ($this->getAttachments() as $file) {
            if ($file->getAction() == 'U') {
                $commFileHelper->removeTempFile($file);
            }

            if ($sendFsub && $file->getAction()) {
                $commFileHelper->submitFile($file, $file->getAction());
            }
        }

        foreach ($this->getLines() as $line) {
            /* @var $line \Epicor\Comm\Model\Customer\ReturnModel\Line */
            $line->syncErpFiles();
        }
    }

    public function getReturnType()
    {
        $customerId = $this->getCustomerId();
        if (!empty($customerId)) {
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            /* @var $customer \Epicor\Comm\Model\Customer */
            if ($customer->isCustomer()) {
                $type = 'b2b';
            } else {
                $type = 'b2c';
            }
        } else {
            $type = 'guests';
        }

        return $type;
    }

    /**
     * Sends a CRRD request to the erp for this return and uses it to update the message
     * 
     * @return boolean
     */
    public function updateFromErp()
    {
        $success = true;

        if ($this->getErpReturnsNumber() && $this->getErpSyncAction() == '') {
            $helper = $this->commMessagingHelper;
            /* @var $helper \Epicor\Comm\Helper\Messaging */

            $data = array(
                'erp_returns_number' => $this->getErpReturnsNumber(),
                'updated_return' => $this
            );

            $message = $helper->sendErpMessage('epicor_comm', 'crrd', $data, array(), array('setCustomerGroupId' => $this->getErpAccountId()));
            $success = $message['success'];

            if ($success) {
                $this->save();
            }
        }

        return $success;
    }

    public function isActionAllowed($action)
    {
        if (!$this->scopeConfig->getValue('epicor_comm_returns/returns/actions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $allowed = true;
        } else {
            $actions = explode(',', $this->getActions());

            if ($this->getActions() == 'None' || in_array('None', $actions)) {
                $allowed = false;
            } else {
                $allowed = in_array('All', $actions) || in_array($action, $actions);
            }
        }

        return $allowed;
    }

    public function hasBeenSubmitted()
    {
        $submitted = false;
        if ($this->getErpReturnsNumber()) {
            $submitted = true;
        } else if ($this->getSubmitted()) {
            $submitted = true;
        }

        return $submitted;
    }

    public function _beforeDelete()
    {
        if ($this->_attachments !== null) {
            foreach ($this->_attachments as $attachment) {
                /* @var $attachment \Epicor\Common\Model\File */

                if ($attachment->getErpId()) {
                    $attachment->setAction('R');
                }

                $attachment->save();
            }
        }

        parent::_beforeDelete();
    }

    public function afterSave()
    {
        parent::afterSave();

        if (!$this->isObjectNew() && $this->getReturnsStatus() != $this->getOrigData('returns_status')) {
            $this->sendStatusUpdateEmails();
        }

        if ($this->_lines !== null) {
            foreach ($this->_lines as $line) {
                /* @var $line \Epicor\Comm\Model\Customer\ReturnModel\Line */
                $line->setReturnId($this->getId());
                $line->save();
            }
        }

        if ($this->_attachments !== null) {
            foreach ($this->_attachments as $attachment) {
                /* @var $attachment \Epicor\Common\Model\File */

                $attachment->save();

                if (!$this->getAttachmentLink($attachment->getId())) {
                    $link = $this->commCustomerReturnModelAttachmentFactory->create();
                    /* @var $attachment \Epicor\Comm\Model\Customer\ReturnModel\Attachment */
                    $link->setReturnId($this->getId());
                    $link->setAttachmentId($attachment->getId());

                    $this->_attachmentLinks[$attachment->getId()] = $link;
                }
            }
        }

        if (!empty($this->_attachmentLinks)) {
            foreach ($this->_attachmentLinks as $link) {
                /* @var $link \Epicor\Comm\Model\Customer\ReturnModel\Attachment */
                $link->save();
            }
        }

        $this->cleanModelCache();
        $this->eventManager->dispatch($this->_eventPrefix . '_save_complete', $this->_getEventData());
    }
    
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->isObjectNew()) {                       
            $mainTable = $this->getResource()->getMainTable();
            $nextReturnId = $this->_resourceHelper->getNextAutoincrement($mainTable);
            $webReturn = $this->scopeConfig->getValue('epicor_comm_returns/returns/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
            $webReturn .= $nextReturnId;
            $this->setWebReturnsNumber($webReturn);                 
        }       
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Message\Request $message
     * @param \Epicor\Comm\Model\Customer\ReturnModel $return
     * 
     * @return boolean
     */
    public function sendErrorEmails($message)
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper \Epicor\Comm\Helper\Returns */

        $storeId = $this->getStoreId() ?: $this->storeManager->getStore()->getId();
        $type = $this->getReturnType();

        /* @var $message \Epicor\Comm\Model\Message\Request */
        $code = $message->getStatusCode();
        $msgDesc = $message->getErrorDescription($code);
        $statusDescription = $message->getStatusDescription();

        $quoteDetails = array(
            'id' => $this->getId()
        );

        $requested = $this->urlEncoder->encode($this->_encryptor->encrypt(serialize($quoteDetails)));

        //M1 > M2 Translation Begin (Rule p2-4)
        //$url = Mage::getUrl('epicor_comm/returns/view', array('return' => $requested));
        $url = $this->urlBuilder->getUrl('epicor_comm/returns/view', array('return' => $requested));
        //M1 > M2 Translation End

        $data = array(
            'return' => $this,
            'errorCode' => $msgDesc,
            'errorDesc' => $statusDescription,
            'customerName' => $this->getCustomerName(),
            'customerEmail' => $this->getEmailAddress(),
            'myReturnsUrl' => $url
        );

        $this->_sendEmails($type, 'error', $storeId, $data);
    }

    public function sendStatusUpdateEmails()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper \Epicor\Comm\Helper\Returns */

        $storeId = $this->getStoreId() ?: $this->storeManager->getStore()->getId();
        $type = $this->getReturnType();

        $quoteDetails = array(
            'id' => $this->getId()
        );

        $requested = $this->urlEncoder->encode($this->_encryptor->encrypt(serialize($quoteDetails)));

        //M1 > M2 Translation Begin (Rule p2-4)
        //$url = Mage::getUrl('epicor_comm/returns/view', array('return' => $requested));
        $url = $this->urlBuilder->getUrl('epicor_comm/returns/view', array('return' => $requested));
        //M1 > M2 Translation End

        $msgHelper = $this->customerconnectMessagingHelper;
        /* @var $helper \Epicor\Customerconnect\Helper\Messaging */

        $data = array(
            'return' => $this,
            'returnStatus' => $msgHelper->getRmaStatusDescription($this->getReturnsStatus()),
            'customerName' => $this->getCustomerName(),
            'customerEmail' => $this->getEmailAddress(),
            'myReturnsUrl' => $url
        );

        $this->_sendEmails($type, 'status', $storeId, $data);
    }

    /**
     * Sends emails for returns for admin & customer
     * 
     * @param string $returnType
     * @param string $emailType
     * @param int $storeId
     * @param array $data
     */
    private function _sendEmails($returnType, $emailType, $storeId, $data)
    {

        $helper = $this->commReturnsHelper;
        /* @var $helper \Epicor\Comm\Helper\Returns */

        $adminEnabled = $this->scopeConfig->isSetFlag(
                'epicor_comm_returns/' . $returnType . '/send_admin_' . $emailType . '_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );

        $customerEnabled = $this->scopeConfig->isSetFlag(
                'epicor_comm_returns/' . $returnType . '/send_customer_' . $emailType . '_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );

        if ($adminEnabled) {

            $template = $this->scopeConfig->getValue(
                    'epicor_comm_returns/' . $returnType . '/admin_' . $emailType . '_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $from = $this->scopeConfig->getValue(
                    'epicor_comm_returns/' . $returnType . '/admin_' . $emailType . '_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $to = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $name = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $data['name'] = $name;

            $helper->sendTransactionalEmail($template, $from, $to, $name, $data, $storeId);
        }

        if ($customerEnabled) {

            $template = $this->scopeConfig->getValue(
                    'epicor_comm_returns/' . $returnType . '/customer_' . $emailType . '_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $from = $this->scopeConfig->getValue(
                    'epicor_comm_returns/' . $returnType . '/customer_' . $emailType . '_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $to = $this->getEmailAddress();
            $name = $this->getCustomerName();

            $data['name'] = $name;

            $helper->sendTransactionalEmail($template, $from, $to, $name, $data, $storeId);
        }
    }

    public function getStatusDisplay()
    {
        $helper = $this->customerconnectMessagingHelper;
        /* @var $helper \Epicor\Customerconnect\Helper\Messaging */

        $status = $helper->getRmaStatusDescription($this->getReturnsStatus());

        if (empty($status)) {
            if ($this->getSubmitted()) {
                $status = __('Awaiting Submission');
            } else {
                $status = __('Not Submitted');
            }
        }

        return $status;
    }

}
