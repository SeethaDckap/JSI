<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;

/**
 * Request CRRU - Customer Returns Update
 * 
 * @method setReturn(Epicor_Comm_Model_Customer_ReturnModel $return)
 * @method Epicor_Comm_Model_Customer_ReturnModel getReturn()
 * 
 */
class Crru extends \Epicor\Comm\Model\Message\Request
{

    private $_lineNumber = 0;
    private $_attachmentNumber = 0;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    private $_erpAccount;

    /**
     * @var \Epicor\Comm\Model\Customer | array
     */
    private $_customer;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $customerResourceModelAddressCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel\LineFactory
     */
    protected $commCustomerReturnModelLineFactory;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,       
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerResourceModelAddressCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        \Epicor\Comm\Model\Customer\ReturnModel\LineFactory $commCustomerReturnModelLineFactory,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,        
        array $data = [])
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerResourceModelAddressCollectionFactory = $customerResourceModelAddressCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        $this->commCustomerReturnModelLineFactory = $commCustomerReturnModelLineFactory;
        $this->commonFileFactory = $commonFileFactory;
        $this->commonFileHelper = $context->getCommonFileHelper();
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;        
        $this->commHelper = $context->getCommHelper();
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('CRRU');
        $this->setLicenseType('Consumer');
        $this->setConfigBase('epicor_comm_enabled_messages/CRRU_request/');

    }

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {
            $returnObj = $this->getReturn();
            $this->storeManager->setCurrentStore($returnObj->getStoreId());
            /* @var $returnObj Epicor_Comm_Model_Customer_ReturnModel */

            $this->_brand = $this->getHelper()->getStoreBranding($returnObj->getStoreId());
            $this->_company = $this->_brand->getCompany();

            $message = $this->getMessageTemplate();
            
            $webReturn = $returnObj->getWebReturnsNumber() ?: $returnObj->getId();
            
            $subject = 'Web Return: ' . $webReturn;

            if ($returnObj->getErpReturnsNumber()) {
                $subject = "\n" . 'ERP Return: ' . $returnObj->getErpReturnsNumber();
            }

            $this->setMessageSecondarySubject($subject);

            $new = $returnObj->getData();
            $old = $returnObj->getPreviousErpData();

            $message['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
                     
            $return = array(
                '_attributes' => array(
                    'action' => $returnObj->getErpSyncAction()
                ),
                'oldReturn' => array(
                ),
                'webReturnsNumber' => $webReturn,
                'erpReturnsNumber' => $returnObj->getErpReturnsNumber(),
                'rmaDate' => $this->commHelper->convertDateToIso8601($returnObj->getRmaDate()),
                'returnsStatus' => $returnObj->getReturnsStatus(),
                'customerReference' => $returnObj->getCustomerReference(),
                'deliveryAddress' => $this->_getDeliveryAddress($returnObj),
                'customerName' => $returnObj->getCustomerName(),
                //'emailAddress' => $returnObj->getEmailAddress(),
                'creditInvoiceNumber' => $returnObj->getCreditInvoiceNumber(),
                'rmaCaseNumber' => $returnObj->getRmaCaseNumber(),
                'rmaContact' => $returnObj->getRmaContact(),
                'noteText' => $returnObj->getNoteText(),
                'lines' => array(),
                'attachments' => array(),
            );

            if (!empty($old)) {
                $return['oldReturn'] = array(
                    'webReturnsNumber' => $returnObj->getId(),
                    'rmaDate' => $this->commHelper->convertDateToIso8601($old->getRmaDate()),
                    'returnsStatus' => $old->getReturnsStatus(),
                    'customerReference' => $old->getCustomerReference(),
                    'customerName' => $old->getCustomerName(),
                    'creditInvoiceNumber' => $old->getCreditInvoiceNumber(),
                    'rmaCaseNumber' => $old->getRmaCaseNumber(),
                    'rmaContact' => $old->getRmaContact(),
                    'noteText' => $old->getNoteText(),
                );
            }

            $lines = $this->_buildLines();

            if ($lines) {
                $return['lines'] = array('line' => $lines);
            }

            $attachments = $this->_buildAttachments($returnObj);
            if ($attachments) {
                $return['attachments'] = array('attachment' => $attachments);
            }

            $message['messages']['request']['body']['return'] = $return;

            $this->setOutXml($message);
            return true;
        } else {
            return 'Missing account number';
        }
    }

    /**
     * Works out the delivery address
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel $returnObj
     */
    private function _getDeliveryAddress($returnObj)
    {
        $formatted = array();

        $addressCode = $returnObj->getAddressCode();
        $address = false;
        if (!empty($addressCode)) {
            $address = $this->customerResourceModelAddressCollectionFactory->create()->addAttributeToFilter('ecc_erp_address_code', array('eq' => $addressCode))
                ->addAttributeToSelect('*')
                ->getFirstItem();
            /* @var $address Mage_Customer_Model_Address */
        } else {

            $customerId = $this->getCustomerId();
            if (!empty($customerId)) {
                $customer = $this->customerCustomerFactory->create()->load($customerId);
                /* @var $customer Epicor_Comm_Model_Customer */

                $address = $customer->getDefaultShippingAddress();
            } else {
                $lines = $returnObj->getLines() ?: array();
                foreach ($lines as $line) {
                    /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */
                    $orderNum = $line->getOrderNumber();
                    $order = $this->salesOrderFactory->create()->load($orderNum, 'ecc_erp_order_number');
                    /* @var $order Mage_Sales_Model_Order */
                    if (!$order->isObjectNew()) {
                        $address = $order->getShippingAddress();
                        break;
                    }
                }
            }
        }

        if ($address) {
            $helper = $this->commMessagingCustomerHelper;
            /* @var $helper Epicor_Comm_Helper_Messaging_Customer */
            $formatted = $helper->formatCustomerAddress($address, true, true);

            if (empty($formatted['emailAddress'])) {
                $formatted['emailAddress'] = $returnObj->getEmailAddress();
            }
        }

        return $formatted;
    }

    /**
     * Processes the CRRU response
     * 
     * @return boolean
     */
    public function processResponse()
    {
        $success = false;

        if ($this->isSuccessfulStatusCode()) {
            $return = $this->getReturn();
            $erpReturn = $this->getResponse()->getReturn();

            $subject = 'Web Return: ' . $return->getId();

            if ($erpReturn->getErpReturnsNumber()) {
                $subject = "\n" . 'ERP Return: ' . $erpReturn->getErpReturnsNumber();
            }

            $this->setMessageSecondarySubject($subject);

            $return->setErpReturnsNumber($erpReturn->getErpReturnsNumber());
            $return->setWebReturnsNumber($erpReturn->getWebReturnsNumber());
            $return->setRmaDate($erpReturn->getRmaDate());
            $return->setReturnsStatus($erpReturn->getReturnsStatus());
            $return->setCustomerReference($erpReturn->getCustomerReference());

            $this->_loadCustomer($erpReturn);

            if ($erpReturn->getDeliveryAddress()) {
                $return->setAddressCode($erpReturn->getDeliveryAddress()->getAddressCode());
            }

            $return->setCreditInvoiceNumber($erpReturn->getCreditInvoiceNumber());
            $return->setRmaCaseNumber($erpReturn->getRmaCaseNumber());
            $return->setRmaContact($erpReturn->getRmaContact());

            $this->_updateLines($erpReturn);

            $this->_updateActions($return, $erpReturn);
            $this->_updateAttachments($return, $erpReturn);

            $return->setPreviousData(serialize($return->getData()));

            $code = $this->getStatusCode();
            $statusDescription = $this->getStatusDescription();

            $return->setErpSyncAction('');
            $return->setLastErpStatus($code);
            $return->setLastErpErrorDescription($statusDescription);
            $return->setErpSyncStatus('S');

            $return->save();

            $success = true;
        } else {

            $return = $this->getReturn();

            $code = $this->getStatusCode();
            $statusDescription = $this->getStatusDescription();

            $return->setLastErpStatus($code);
            $return->setLastErpErrorDescription($statusDescription);
            $return->setErpSyncStatus('E');

            $return->save();

            $return->sendErrorEmails($this);
        }

        return $success;
    }

    private function _updateLines($erpReturn)
    {
        $lineData = array();

        if ($erpReturn->getLines()) {
            $lineData = $erpReturn->getLines()->getasarrayLine();
        }

        $return = $this->getReturn();
        $lines = $return->getLines();
        $processedLines = array();

        foreach ($lineData as $line) {
            $webLineId = $line->getData('_attributes')->getWebLineNumber();
            $erpLineId = $line->getData('_attributes')->getErpLineNumber();

            $webLine = false;

            if ($webLineId || $erpLineId) {
                $webLine = $return->getLine($webLineId, $erpLineId);
                if ($webLine) {
                    $webLine->setPreviousData(serialize($webLine->getData()));
                }
            }

            if (!$webLine) {
                $webLine = $this->commCustomerReturnModelLineFactory->create();
            }

            $serialsArr = $line->getSerialNumbers();
            $serials = '';
            if (!empty($serialsArr)) {
                $serailsArr = $serialsArr->getasarraySerialNumber();
                $serials = implode(',', $serailsArr);
            }

            /* @var $webLine Epicor_Comm_Model_Customer_ReturnModel_Line */

            $processedLines[] = $erpLineId;

            $webLine->setErpLineNumber($erpLineId);
            $webLine->setReturnsStatus($line->getReturnsStatus());
            $webLine->setProductCode($line->getProductCode());
            $webLine->setRevisionLevel($line->getRevisionLevel());
            $webLine->setUnitOfMeasureCode($line->getUnitOfMeasureCode());
            $webLine->setQtyOrdered($line->getQuantities()->getOrdered());
            $webLine->setQtyReturned($line->getQuantities()->getReturned());
            $webLine->setReturnsStatus($line->getReturnsStatus());
            $webLine->setOrderNumber($line->getOrderNumber());
            $webLine->setOrderLine($line->getOrderLine());
            $webLine->setOrderRelease($line->getOrderRelease());
            $webLine->setInvoiceNumber($line->getInvoiceNumber());
            $webLine->setSerialNumber($serials);
            $webLine->setReasonCode($line->getReasonCode());
            $webLine->setNoteText($line->getNoteText());
            $webLine->setToBeDeleted('N');

            $this->_updateActions($webLine, $line);
            $this->_updateAttachments($webLine, $line);
        }

        foreach ($lines as $line) {
            /* @var $line Epicor_Comm_Model_Customer_ReturnModel_line */
            if ($line->getToBeDeleted() == 'Y' || !in_array($line->getErpLineNumber(), $processedLines)) {
                $return->deleteLine($line->getId(), true);
            }
        }
    }

    /**
     * Updates attachments from message data to the supplied object
     * 
     * (same code used for return & lines attachments)
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel | \Epicor\Comm\Model\Customer\ReturnModel\Line $object
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    private function _updateAttachments($object, $erpData)
    {
        $attachments = array();
        if ($erpData->getAttachments()) {
            $attachments = $erpData->getAttachments()->getasarrayAttachment();
        }

        $processedAtts = array();

        if (!empty($attachments)) {
            foreach ($attachments as $attachmentData) {
                $attachment = $this->commonFileFactory->create();
                /* @var $attachment Epicor_Common_Model_File */
                if ($attachmentData->getWebFileId()) {
                    $attachmentObj = $object->getAttachment($attachmentData->getWebFileId());

                    if ($attachmentObj) {
                        $attachment = $attachmentObj;
                    }

                    $processedAtts[] = $attachmentData->getWebFileId();
                }

                if (!$attachment->isObjectNew()) {
                    $attachment->setPreviousData(serialize($attachmentData->getData()));
                }

                $attachment->setErpId($attachmentData->getErpFileId());

                if ($attachment->getErpId()) {
                    $action = 'U';
                } else {
                    $action = 'A';
                }

                $attachment->setAction($action);
                $attachment->setFilename($attachmentData->getFilename());
                $attachment->setDescription($attachmentData->getDescription());
                $attachment->setUrl($attachmentData->getUrl());
                $attachment->setSource('erp');

                if ($this->_customer && !is_array($this->_customer) && $this->_customer->isObjectNew()) {
                    $attachment->setCustomerId($this->_customer->getId());
                }

                $attachment->setErpAccountId($this->_erpAccount->getId());

                $object->addAttachment($attachment);
            }
        }

        $objAttachments = $object->getAttachments();

        foreach ($objAttachments as $attachment) {
            /* @var $attachment Epicor_Common_Model_File */
            if (!in_array($attachment->getId(), $processedAtts)) {
                $object->deleteAttachment($attachment->getId(), true);
            }
        }
    }

    /**
     * Processes actions from message data to the supplied object
     * 
     * (same code used for return & lines attachments)
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel | \Epicor\Comm\Model\Customer\ReturnModel\Line $object
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    private function _updateActions($object, $erpData)
    {
        $actions = array();
        if ($erpData->getActions()) {
            $actions = $erpData->getActions()->getasarrayAction();
        }

        $actionsStr = '';

        if (!empty($actions)) {
            $actionsStr = implode(',', $actions);
        }

        $object->setActions($actionsStr);
    }

    /**
     * Processes post data for Price breaks / Supplier UOM / Cross reference parts
     * into arrays for the xml
     * 
     * @param string $key - post data array key to look at
     * @param array $data - posted data
     * 
     * @return array
     */
    private function _buildLines()
    {
        $returnLines = $this->getReturn()->getLines();
        $lines = array();
        foreach ($returnLines as $line) {
            /* @var $line Epicor_Comm_Model_Customer_ReturnModel_line */
            if ($line->getToBeDeleted() == 'Y') {
                $action = 'R';
            } else if ($line->getErpLineNumber()) {
                $action = 'U';
            } else {
                $action = 'A';
            }

            $lines[] = $this->_buildLine($line, $action);
        }

        return $lines;
    }

    /**
     * Builds a line array to be added to the message
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel\line $line - line data
     * @param string $action - action type for the action attribute
     * 
     * @return type
     */
    private function _buildLine($line, $action)
    {
        $serials = explode(',', $line->getSerialNumber());

        if (!empty($serials)) {
            $serials = array('serialNumber' => $serials);
        }

        $lineData = array(
            '_attributes' => array(
                'erpLineNumber' => $line->getErpLineNumber(),
                'webLineNumber' => $line->getId(),
                'type' => 'N',
                'action' => $action
            ),
            'oldLine' => array(),
            'productCode' => $line->getProductCode(),
            'unitOfMeasureCode' => $line->getUnitOfMeasureCode(),
            'revisionLevel' => $line->getRevisionLevel(),
            'quantities' => array(
                'ordered' => $line->getQtyOrdered(),
                'returned' => $line->getQtyReturned(),
            ),
            'returnsStatus' => $line->getReturnsStatus(),
            'orderNumber' => $line->getOrderNumber(),
            'orderLine' => $line->getOrderLine(),
            'orderRelease' => $line->getOrderRelease(),
            'invoiceNumber' => $line->getInvoiceNumber(),
            'invoiceLine' => $line->getInvoiceLine(),
            'serialNumbers' => $serials,
            'reasonCode' => $line->getReasonCode(),
            'noteText' => $line->getNoteText(),
            'attachments' => array()
        );

        $attachments = $this->_buildAttachments($line);
        if (!empty($attachments)) {
            $lineData['attachments']['attachment'] = $attachments;
        }

        $oldData = $line->getPreviousErpData();

        if ($action != 'A' && !empty($oldData)) {

            $serials = explode(',', $oldData->getSerialNumber());

            if (!empty($serials)) {
                $serials = array('serialNumber' => $serials);
            }

            $lineData['oldLine'] = array(
                'productCode' => $oldData->getProductCode(),
                'unitOfMeasureCode' => $oldData->getUnitOfMeasureCode(),
                'quantities' => array(
                    'ordered' => $oldData->getQtyOrdered(),
                    'returned' => $oldData->getQtyReturned(),
                ),
                'returnsStatus' => $oldData->getReturnsStatus(),
                'orderNumber' => $oldData->getOrderNumber(),
                'orderLine' => $oldData->getOrderLine(),
                'orderRelease' => $oldData->getOrderRelease(),
                'invoiceNumber' => $oldData->getInvoiceNumber(),
                'invoiceLine' => $oldData->getInvoiceLine(),
                'serialNumbers' => $serials,
                'reasonCode' => $oldData->getReasonCode(),
                'noteText' => $oldData->getNoteText(),
            );
        }

        return $lineData;
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel | \Epicor\Comm\Model\Customer\ReturnModel\Line $element
     * 
     * @return array
     */
    private function _buildAttachments($element)
    {
        $attachmentData = $element->getAttachments();
        $this->_attachmentNumber = 0;
        $attachments = array();
		$this->_attachmentNumber = 0;
        foreach ($attachmentData as $attachment) {
            /* @var $attachment Epicor_Common_Model_File */
            $oldData = array();
            $link = $element->getAttachmentLink($attachment->getId());
            if ($link->getToBeDeleted() == 'Y') {
                $action = 'R';
            } else if ($attachment->getErpId()) {
                $action = 'U';
            } else {
                $action = 'A';
            }

            $attachments[] = $this->_buildAttachment($attachment, $action, $oldData);
        }

        return $attachments;
    }

    /**
     * builds an attachment array to be added to the message
     * 
     * @param \Epicor\Common\Model\File $attachment - attachment to be added to the data
     * @param string $action - action type for the action attribute
     * 
     * @return type
     */
    private function _buildAttachment($attachment, $action, $oldData = array())
    {
        $number = $this->_attachmentNumber;
        $this->_attachmentNumber = ($number >= $this->_attachmentNumber) ? $number + 1 : $this->_attachmentNumber;

        $url = $attachment->getUrl();

        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        if (empty($url) && $attachment->getSource() == 'web') {
            $url = $helper->getFileUrl($attachment->getId(), $attachment->getErpId(), $attachment->getFilename());
        }

        $attachmentData = array(
            '_attributes' => array(
                'num' => $number,
                'action' => $action
            ),
            'attachmentNumber' => $attachment->getErpId(),
            'erpFileId' => $attachment->getErpId(),
            'webFileId' => $attachment->getId(),
            'description' => $attachment->getDescription(),
            'filename' => $attachment->getFilename(),
            'url' => $url,
            'attachmentStatus' => '',
        );

        if (!empty($oldData)) {
            $oldData = $this->dataObjectFactory->create($oldData);
            if (empty($url) && $oldData->getSource() == 'web') {
                $url = $helper->getFileUrl($oldData->getId(), $oldData->getErpId(), $oldData->getFilename());
            }
            $attachmentData['oldAttachment'] = array(
                'attachmentNumber' => $oldData->getErpId(),
                'erpFileId' => $oldData->getErpId(),
                'webFileId' => $oldData->getId(),
                'description' => $oldData->getDescription(),
                'filename' => $oldData->getFilename(),
                'url' => $url,
                'attachmentStatus' => '',
            );
        }

        return $attachmentData;
    }

    /**
     * Loads the customer from the return data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $returnData
     */
    private function _loadCustomer($returnData)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $this->_erpAccount = $helper->getErpAccountInfo();

        $this->_customer = null;

        $contactCode = $returnData->getRmaContact();
        if (!empty($contactCode)) {
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addAttributeToFilter('ecc_contact_code', $returnData->getRmaContact());
            $collection->addAttributeToFilter('ecc_erpaccount_id', $this->_erpAccount->getId());
            $collection->addFieldToFilter('website_id', $this->storeManager->getWebsite()->getId());
            $this->_customer = $collection->getFirstItem();
        } else {
            $this->_customer = array(
                'name' => $returnData->getCustomerName(),
                'email' => $returnData->getDeliveryAddress() ? $returnData->getDeliveryAddress()->getEmailAddress() : '',
            );
        }
    }

}
