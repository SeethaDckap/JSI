<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * @method string getErpReturnsNumber()
 * @method string getWebReturnsNumber()
 * @method string getLanguageCode()
 * @method string getAccountNumber()
 * @method Epicor_Comm_Model_Customer_ReturnModel getUpdatedReturn()
 * 
 * @method setErpReturnsNumber(string $val)
 * @method setWebReturnsNumber(string $val)
 * @method setLanguageCode(string $val)
 * @method setAccountNumber(string $val)
 * @method setUpdatedReturn(Epicor_Comm_Model_Customer_ReturnModel $val)
 * 
 * 
 * Customer Returns Details Message
 *
 * @author Paul.Ketelle
 */
class Crrd extends \Epicor\Comm\Model\Message\Request
{

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel
     */
    private $_return;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    private $_erpAccount;

    /**
     * @var \Epicor\Comm\Model\Customer | array
     */
    private $_customer;
    private $_isNew;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel\LineFactory
     */
    protected $commCustomerReturnModelLineFactory;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Epicor\Comm\Model\Customer\ReturnModel\LineFactory $commCustomerReturnModelLineFactory,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commReturnsHelper = $commReturnsHelper;
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        $this->commCustomerReturnModelLineFactory = $commCustomerReturnModelLineFactory;
        $this->commonFileFactory = $commonFileFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('CRRD');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setConfigBase('epicor_comm_enabled_messages/crrd_request/');
        $this->setResultsPath('return');

    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();

        $subject = 'ERP Return: ' . $this->getErpReturnsNumber();
        $this->setMessageSecondarySubject($subject);

        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['erpReturnsNumber'] = $this->getErpReturnsNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();

        $this->setOutXml($data);

        return true;
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            // getVarienDataFromPath converts xml into a varien object, which can be referenced from controller
            $returnData = $this->getResponse()->getVarienDataFromPath($this->getResultsPath());
            $this->setResults($returnData);

            $returnsHelper = $this->commReturnsHelper;
            /* @var $returnsHelper Epicor_Comm_Helper_Returns */

            $this->_loadCustomer($returnData);

            if (!$this->getUpdatedReturn()) {
                $returnSearch = $returnsHelper->findReturn(
                    'return', $returnData->getErpReturnsNumber(), false, false, false
                );

                if ($returnSearch['found']) {
                    $this->_return = $returnSearch['return'];
                    $this->_isNew = false;
                } else {
                    $this->_return = $this->commCustomerReturnModelFactory->create();
                    $this->_isNew = true;
                }
            } else {
                $this->_return = $this->getUpdatedReturn();
                $this->_isNew = false;
            }

            if ($this->_isNew) {
                $this->_return->setErpReturnsNumber($returnData->getErpReturnsNumber());                 
                $this->_return->setStoreId($this->storeManager->getStore()->getId());
            } else if (!$this->_return->getStoreId()) {
                $this->_return->setStoreId($this->storeManager->getStore()->getId());
            }
            $this->_return->setWebReturnsNumber($returnData->getWebReturnsNumber());              
            $this->_return->setRmaDate($returnData->getRmaDate());
            $this->_return->setReturnsStatus($returnData->getReturnsStatus());
            $this->_return->setCustomerReference($returnData->getCustomerReference());
            $this->_return->setRmaCaseNumber($returnData->getRmaCaseNumber());
            $this->_return->setNoteText($returnData->getNoteText());
            $this->_return->setCreditInvoiceNumber($returnData->getCreditInvoiceNumber());

            if ($returnData->getDeliveryAddress()) {
                $this->_return->setAddressCode($returnData->getDeliveryAddress()->getAddressCode());
            }

            if (!$this->_return->getErpAccountId()) {
                $this->_return->setErpAccountId($this->_erpAccount->getId());
            }
            if (!$this->_customer || is_array($this->_customer) || $this->_customer->isObjectNew()) {
                if ($this->_return->isObjectNew()) {
                    $this->_return->setCustomerName($returnData->getCustomerName());
                    if ($returnData->getDeliveryAddress()) {
                        $this->_return->setEmailAddress($returnData->getDeliveryAddress()->getEmailAddress());
                    }
                }
            } else {
                if ($this->_return->isObjectNew()) {
                    $this->_return->setCustomerName($returnData->getCustomerName());
                    if ($returnData->getDeliveryAddress()) {
                        $this->_return->setEmailAddress($returnData->getDeliveryAddress()->getEmailAddress());
                    }
                }
                $this->_return->setRmaContact($returnData->getRmaContact());
                $this->_return->setIsGlobal(0);
                $this->_return->setCustomerId($this->_customer->getId());
            }

            $this->_processLines($returnData);
            $this->_processActions($this->_return, $returnData);
            $this->_processAttachments($this->_return, $returnData);
            $this->setReturn($this->_return);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Processes the lines for the ERP return
     * 
     * @param \Epicor\Common\Model\Xmlvarien $returnData
     */
    private function _processLines($returnData)
    {
        $linesData = array();

        if ($returnData->getLines()) {
            $linesData = $returnData->getLines()->getasarrayLine();
        }

        $processedLines = array();

        foreach ($linesData as $lineData) {
            $attributes = $lineData->getData('_attributes');

            $line = false;

            if ($attributes) {
                $webLine = $attributes->getWebLineNumber();
                if ($webLine == 0) {
                    $webLine = '';
                }

                $erpLine = $attributes->getErpLineNumber();
                if ($erpLine == 0) {
                    $erpLine = '';
                }

                $line = $this->_return->getLine($webLine, $erpLine);
            } else {
                $erpLine = '';
            }

            if (!$line) {
                $line = $this->commCustomerReturnModelLineFactory->create();
                /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */
            }

            if (!$line->isObjectNew()) {
                $line->setPreviousData($lineData->getData());
            }

            $processedLines[] = $erpLine;

            $line->setErpLineNumber($erpLine);
            $line->setProductCode($lineData->getProductCode());
            $line->setRevisionLevel($lineData->getRevisionLevel());
            $line->setUnitOfMeasureCode($lineData->getUnitOfMeasureCode());
            $line->setQtyOrdered($lineData->getQuantities()->getOrdered());
            $line->setQtyReturned($lineData->getQuantities()->getReturned());
            $line->setReturnsStatus($lineData->getReturnsStatus());
            $line->setOrderNumber($lineData->getOrderNumber());
            $line->setOrderLine($lineData->getOrderLine());
            $line->setOrderRelease($lineData->getOrderRelease());
            $line->setInvoiceNumber($lineData->getInvoiceNumber());
            $line->setInvoiceLine($lineData->getInvoiceLine());
            $line->setSerialNumber($lineData->getSerialNumber());
            $line->setReasonCode($lineData->getReasonCode());
            $line->setNoteText($lineData->getNoteText());

            $this->_processAttachments($line, $lineData);
            $this->_processActions($line, $lineData);

            $this->_return->addLine($line);
        }

        foreach ($this->_return->getLines() as $line) {
            /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */
            if (!in_array($line->getErpLineNumber(), $processedLines)) {
                $this->_return->deleteLine($line->getId(), true);
            }
        }
    }

    /**
     * Processes attachments from message data to the supplied object
     * 
     * (same code used for return & lines attachments)
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel | \Epicor\Comm\Model\Customer\ReturnModel\Line $object
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    private function _processAttachments($object, $erpData)
    {
        $attachments = array();
        if ($erpData->getAttachments()) {
            $attachments = $erpData->getAttachments()->getasarrayAttachment();
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachmentData) {

                $attachment = $object->getAttachment($attachmentData->getWebFileId(), $attachmentData->getErpFileId());

                if (!$attachment) {
                    $attachment = $this->commonFileFactory->create();
                }

                /* @var $attachment Epicor_Common_Model_File */

                $attachment->setPreviousData(serialize($attachmentData->getData()));

                $attachment->setErpId($attachmentData->getErpFileId());
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
    }

    /**
     * Processes actions from message data to the supplied object
     * 
     * (same code used for return & lines attachments)
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel | \Epicor\Comm\Model\Customer\ReturnModel\Line $object
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    private function _processActions($object, $erpData)
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
     * Loads the customer from the return data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $returnData
     */
    private function _loadCustomer($returnData)
    {
        $helper = $this->commMessagingHelper->create();
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
