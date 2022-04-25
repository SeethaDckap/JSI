<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer\ReturnModel;


/**
 * 
 * @method string getErpLineNumber()
 * @method string getReturnId()
 * @method string getProductCode()
 * @method string getRevisionLevel()
 * @method string getUnitOfMeasureCode()
 * @method string getQtyOrdered()
 * @method string getQtyReturned()
 * @method string getReturnsStatus()
 * @method string getOrderNumber()
 * @method string getOrderLine()
 * @method string getOrderRelease()
 * @method string getInvoiceNumber()
 * @method string getInvoiceLine()
 * @method string getSerialNumber()
 * @method string getReasonCode()
 * @method string getNoteText()
 * @method string getToBeDeleted()
 * @method string getPreviousData()
 * @method string getShipmentNumber()
 * @method string getActions()
 * 
 * @method setReturnId(string $value)
 * @method setProductCode(string $value)
 * @method setRevisionLevel(string $value)
 * @method setUnitOfMeasureCode(string $value)
 * @method setQtyOrdered(string $value)
 * @method setQtyReturned(string $value)
 * @method setReturnsStatus(string $value)
 * @method setOrderNumber(string $value)
 * @method setOrderLine(string $value)
 * @method setOrderRelease(string $value)
 * @method setShipmentNumber(string $value)
 * @method setInvoiceNumber(string $value)
 * @method setInvoiceLine(string $value)
 * @method setSerialNumber(string $value)
 * @method setReasonCode(string $value)
 * @method setNoteText(string $value)
 * @method setToBeDeleted(string $value)
 * @method setErpLineNumber(string $value)
 * @method setPreviousData(string $value)
 * @method setActions(string $value)
 * 
 * Customer group class for Erp
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Line extends \Epicor\Database\Model\Customer\ReturnModel\Line
{

    /**
     *
     * @var \Epicor\Comm\Model\Customer\ReturnModel 
     */
    protected $_return;
    protected $_eventPrefix = 'ecc_customer_return_line';
    protected $_eventObject = 'customer_return_line';
    protected $_attachments = null;
    protected $_attachmentIds = array();
    protected $_attachmentLinks = array();

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel\LineFactory
     */
    protected $commCustomerReturnModelLineFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory
     */
    protected $commResourceCustomerReturnModelAttachmentCollectionFactory;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel\AttachmentFactory
     */
    protected $commCustomerReturnModelAttachmentFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ReturnModel\LineFactory $commCustomerReturnModelLineFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory $commResourceCustomerReturnModelAttachmentCollectionFactory,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Epicor\Comm\Model\Customer\ReturnModel\AttachmentFactory $commCustomerReturnModelAttachmentFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commCustomerReturnModelLineFactory = $commCustomerReturnModelLineFactory;
        $this->commResourceCustomerReturnModelAttachmentCollectionFactory = $commResourceCustomerReturnModelAttachmentCollectionFactory;
        $this->commonFileFactory = $commonFileFactory;
        $this->scopeConfig = $scopeConfig;
        $this->commFileHelper = $commFileHelper;
        $this->commCustomerReturnModelAttachmentFactory = $commCustomerReturnModelAttachmentFactory;
        $this->eventManager = $context->getEventDispatcher();
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
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line');
    }

    public function getParent()
    {
        return $this->_return;
    }

    /**
     * Sets the parent for this line
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel $return
     */
    public function setParent($return)
    {
        $this->_return = $return;
    }

    public function reloadChildren()
    {
        $this->_attachments = null;
        $this->_attachmentIds = array();
        $this->_attachmentLinks = array();

        $this->getAttachments();
    }

    public function getPreviousErpData()
    {
        $previousData = $this->getPreviousData();
        if (!empty($previousData)) {
            $previous = $this->commCustomerReturnModelLineFactory->create();
            /* @var $previous \Epicor\Comm\Model\Customer\ReturnModel\Line */
            $previous->setData(unserialize($previousData));
        } else {
            $previous = array();
        }

        return $previous;
    }

    /**
     * Get a Collection of Attachments
     * 
     * @return array()
     */
    public function getAttachments()
    {

        if ($this->_attachments === null) {
            $collection = $this->commResourceCustomerReturnModelAttachmentCollectionFactory->create();
            $collection->addFieldToFilter('return_id', array('eq' => $this->getReturnId()));
            $collection->addFieldToFilter('line_id', array('eq' => $this->getId()));

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
     * @return $this
     */
    public function addAttachment($attachment)
    {
        if ($this->_attachments === null) {
            $this->getAttachments();
        }

        if ($this->hasAttachment($attachment->getId())) {
            $this->_attachments[$this->_attachmentIds[$attachment->getId()]] = $attachment;
        } else {
            $this->_attachments[] = $attachment;
        }

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
     * Deletes an attachment from the return
     * 
     * @param integer $id - id of the attachment
     * @param boolean $force - whether to force delete the local data
     * 
     * @return $this
     */
    public function deleteAttachment($id, $force = false)
    {
        $attachment = $this->getAttachment($id);

        if ($attachment) {
            $link = $this->getAttachmentLink($id);
            if ($attachment->getErpId() && !$force) {
                $link->setToBeDeleted('Y');
                $link->save();
                $this->_attachmentLinks[$id] = $link;

                $this->setDataChanges(false);
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

                $this->setDataChanges(true);
            }
        }



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

    /**
     * Syncs this returns files with the ERP (if FSUB is set to instant)
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
    }

    public function isActionAllowed($action)
    {
        if (!$this->scopeConfig->getValue('epicor_comm_returns/returns/actions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $allowed = true;
        } else {
            $actionStr = $this->getActions();

            if (empty($actionStr)) {
                $actionStr = $this->getParent()->getActions();
            }

            $actions = explode(',', $actionStr);

            if ($actionStr == 'None' || in_array('None', $actions)) {
                $allowed = false;
            } else {
                $allowed = in_array('All', $actions) || in_array($action, $actions);
            }
        }
        return $allowed;
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

        if ($this->_attachments !== null) {
            foreach ($this->_attachments as $attachment) {
                /* @var $attachment \Epicor\Common\Model\File */

                $attachment->save();

                if (!$this->getAttachmentLink($attachment->getId())) {
                    $link = $this->commCustomerReturnModelAttachmentFactory->create();
                    /* @var $attachment \Epicor\Comm\Model\Customer\ReturnModel\Attachment */
                    $link->setReturnId($this->getReturnId());
                    $link->setAttachmentId($attachment->getId());
                    $link->setLineId($this->getId());

                    $this->_attachmentLinks[$attachment->getId()] = $link;
                }
            }
        }

        if (!empty($this->_attachmentLinks)) {
            foreach ($this->_attachmentLinks as $link) {
                /* @var $link \Epicor\Comm\Model\Customer\ReturnModel\Attachment */
                $link->setToBeDeleted('N');
                $link->save();
            }
        }

        $this->cleanModelCache();
        $this->eventManager->dispatch($this->_eventPrefix . '_save_complete', $this->_getEventData());
    }

    /**
     * Works out the source for a line
     *
     * @return string
     */
    public function getSourceType()
    {
        if ($this->getShipmentNumber()) {
            $source = 'shipment';
        } else if ($this->getOrderNumber()) {
            $source = 'order';
        } else if ($this->getInvoiceNumber()) {
            $source = 'invoice';
        } else if ($this->getSerialNumber()) {
            $source = 'serial';
        } else {
            $source = 'sku';
        }
        return $source;
    }

}
