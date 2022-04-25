<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Helper;

use Epicor\Comm\Helper\ConfiguratorFactory;
use Epicor\Comm\Helper\File;
use Epicor\Comm\Helper\Messaging\Context;
use Epicor\Comm\Helper\ProductFactory;
use Epicor\Comm\Model\Message\Log;
use Epicor\Common\Helper\Locale\Format\Date;
use Epicor\Common\Model\FileFactory;
use Epicor\Common\Model\XmlvarienFactory;
use Epicor\Customerconnect\Model\Message\Request\Crqc;
use Epicor\Customerconnect\Model\Message\Request\Cuod;
use Magento\Framework\Escaper;
use Magento\SalesSequence\Model\Manager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Rfq
 * @package Epicor\Customerconnect\Helper
 */
class Rfq extends Data
{
    /**
     * Configuration path for CRQC Error handling Send User Notification
     */
    const XML_PATH_CRQC_SUN = 'customerconnect_enabled_messages/CRQC_request/error_user_notification';

    /**
     * Configuration path for CRQC Error handling Show ERP Error Description
     */
    const XML_PATH_CRQC_SEED = 'customerconnect_enabled_messages/CRQC_request/error_user_notification_erp';

    /**
     * Configuration path for CRQC Warning actions Send User Notification
     */
    const XML_PATH_CRQC_WUN = 'customerconnect_enabled_messages/CRQC_request/warning_user_notification';

    /**
     * Configuration path for CRQC Warning actions Show ERP Error Description
     */
    const XML_PATH_CRQC_WUNE = 'customerconnect_enabled_messages/CRQC_request/warning_user_notification_erp';

    /**
     * @var Crqc
     */
    protected $customerconnectMessageRequestCrqc;

    /**
     * @var File
     */
    protected $commFileHelper;

    /**
     * @var FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var ConfiguratorFactory
     */
    protected $commConfiguratorHelperFactory;

    /**
     * @var ProductFactory
     */
    protected $commProductHelperFactory;

    /**
     * @var XmlvarienFactory
     */
    protected $commonXmlvarienFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var Manager
     */
    protected $sequenceManager;

    /**
     * Rfq constructor.
     * @param Context $context
     * @param Date $commonLocaleFormatDateHelper
     * @param Cuod $customerconnectMessageRequestCuod
     * @param XmlvarienFactory $commonXmlvarienFactory
     * @param File $commFileHelper
     * @param FileFactory $commonFileFactory
     * @param ConfiguratorFactory $commConfiguratorHelperFactory
     * @param ProductFactory $commProductHelperFactory
     * @param Crqc $customerconnectMessageRequestCrqc
     * @param Escaper $escaper
     * @param Manager $sequenceManager
     */
    public function __construct(
        Context $context,
        Date $commonLocaleFormatDateHelper,
        Cuod $customerconnectMessageRequestCuod,
        XmlvarienFactory $commonXmlvarienFactory,
        File $commFileHelper,
        FileFactory $commonFileFactory,
        ConfiguratorFactory $commConfiguratorHelperFactory,
        ProductFactory $commProductHelperFactory,
        Crqc $customerconnectMessageRequestCrqc,
        Escaper $escaper,
        Manager $sequenceManager
    ) {
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
        $this->commFileHelper = $commFileHelper;
        $this->commonFileFactory = $commonFileFactory;
        $this->commConfiguratorHelperFactory = $commConfiguratorHelperFactory;
        $this->commProductHelperFactory = $commProductHelperFactory;
        $this->customerconnectMessageRequestCrqc = $customerconnectMessageRequestCrqc;
        $this->escaper = $escaper;
        $this->messageManager = $context->getMessageManager();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->encryptor = $context->getEncryptor();
        $this->sequenceManager = $sequenceManager;
        parent::__construct($context, $commonLocaleFormatDateHelper, $customerconnectMessageRequestCuod);
    }

    /**
     * Sends a CRQC for the given RFQ data
     *
     * @param string $type - is this a reject / confrim CRQC
     * @param array $data
     * @param string $response - default response
     * @return string
     */
    public function processRfqCrqc($type, $data, $response)
    {
        $message = $this->customerconnectMessageRequestCrqc;
        /* @var $message Epicor_Customerconnect_Model_Message_Request_Crqc */

        $error = '';

        $messageTypeCheck = $message->getHelper('customerconnect/messaging')->getMessageType('CRQC');

        if ($message->isActive() && $messageTypeCheck) {

            $rfqData = array($data['quote_number'] => $data);
            $rfqAction = array($data['quote_number']);

            $message->setRfqData($rfqData);

            if ($type == 'confirm') {
                $message->setConfirmed($rfqAction);
            } else {
                $message->setRejected($rfqAction);
            }

            if ($message->sendMessage()) {
                $success = ($type == 'confirm') ?
                    __('RFQ confirmed successfully') : __('RFQ rejected successfully');
                $this->messageManager->addSuccessMessage($success);
            } elseif (
                $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_SUN, ScopeInterface::SCOPE_STORE) &&
                $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_SEED, ScopeInterface::SCOPE_STORES) &&
                ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_ERROR)
            ) {
                $error = $message->getStatusDescription();
            } elseif (
                $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_WUN, ScopeInterface::SCOPE_STORE) &&
                $this->scopeConfig->isSetFlag(self::XML_PATH_CRQC_WUNE, ScopeInterface::SCOPE_STORES) &&
                ($message->getLog()->getMessageStatus() == Log::MESSAGE_STATUS_WARNING)
            ) {
                $error = $message->getStatusDescription();
            } else {
                $error = ($type == 'confirm') ?
                    __('Failed to process RFQ confirm') : __('Failed to process RFQ reject');
            }
        } else {
            $error = ($type == 'confirm') ?
                __('RFQ confirm not available') : __('RFQ reject not available');
        }

        if ($error) {
            $response = json_encode(array('message' => $error, 'type' => 'error'));
        } else {
            $erpAccountNum = $this->getErpAccountNumber();

            $quoteDetails = array(
                'erp_account' => $erpAccountNum,
                'quote_number' => $data['quote_number'],
                'quote_sequence' => $data['quote_sequence']
            );

            $requested = $this->urlEncoder->encode($this->encryptor->encrypt((serialize($quoteDetails))));
            $url = $this->_getUrl('*/*/details', array('quote' => $requested));
            $response = json_encode(array('redirect' => $url, 'type' => 'success'));
        }

        return $response;
    }

    /**
     * Processes files uploaded / updatded on the CRQD page, after a successful CRQU
     *
     * @param array $files
     * @param \Epicor\Comm\Model\Xmlvarien $rfq
     */
    public function processCrquFilesSuccess($files, $rfq)
    {
        $sortedData = array();
        foreach ($files as $fileData) {
            if (isset($fileData['delete'])) {
                $file = $this->getFileFromFileData(unserialize(base64_decode($fileData['old_data'])));
                $file->delete();
            } else {
                $sortedData[$fileData['web_file_id']] = $fileData;
            }
        }

        $frequency = $this->scopeConfig->getValue('epicor_comm_enabled_messages/fsub_request/frequency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sendFsub = ($frequency == 'instant') ? true : false;

        // process attachments
        $this->processCrquAttachments($rfq, $sortedData, $sendFsub);

        // process line attachments
        $linesGroup = $rfq->getLines();
        if ($linesGroup) {
            $lines = $linesGroup->getasarrayLine();
            foreach ($lines as $line) {
                $this->processCrquAttachments($line, $sortedData, $sendFsub);
            }
        }
    }

    /**
     * Processes files uploaded / updated on the CRQD page
     * for a specific section of the data (lines / overall attachments)
     *
     * @param \Epicor\Comm\Model\Xmlvarien $data
     * @param array $files
     * @param boolean $sendFsub
     */
    private function processCrquAttachments($data, $files, $sendFsub)
    {
        $commFileHelper = $this->commFileHelper;
        /* @var $commFileHelper Epicor_Comm_Helper_File */

        $attachments = $this->getAttachmentsFromData($data);

        foreach ($attachments as $attachment) {
            if (isset($files[$attachment->getWebFileId()])) {
                $fileData = $files[$attachment->getWebFileId()];
                $file = $this->getFileFromFileData($fileData);
                $file->setErpId($attachment->getErpFileId());

                if (!$sendFsub) {
                    if ($file->getAction() != 'A') {
                        $file->setAction($fileData['action']);
                    }
                }

                if ($file->getAction() == 'U') {
                    $commFileHelper->removeTempFile($file);
                }

                $file->save();

                if ($sendFsub) {
                    $commFileHelper->submitFile($file, $fileData['action']);
                }
            }
        }
    }

    /**
     * Gets attachments from the data point provided
     *
     * @param \Epicor\Comm\Model\Xmlvarien $data
     *
     * @return array
     */
    private function getAttachmentsFromData($data)
    {
        $attachments = array();

        $attachmentGroup = $data->getAttachments();

        if ($attachmentGroup) {
            $attachments = $attachmentGroup->getasarrayAttachment();
        }

        return $attachments;
    }

    /**
     * Processes files uploaded / updatded on the CRQD page, after a failed CRQU
     *
     * @param array $files
     */
    public function processCrquFilesFail($files)
    {
        $fileHelper = $this->commFileHelper;
        /* @var $fileHelper Epicor_Common_Helper_File */

        foreach ($files as $fileData) {
            /* @var $file Epicor_Common_Model_File */

            $file = $this->getFileFromFileData($fileData);

            if ($fileData['action'] == 'U') {
                // Delete temporary file & restore data
                $file->restorePreviousData();
                $file->save();
                $fileHelper->restoreTempFile($file);
            } else if ($fileData['action'] == 'A') {
                // Delete file (physical & db)
                $file->delete();
            }
        }
    }

    /**
     * Gets file model from the data provided
     *
     * @param array $fileData
     *
     * @return \Epicor\Common\Model\File
     */
    private function getFileFromFileData($fileData)
    {
        if (isset($fileData['file_model'])) {
            $file = $fileData['file_model'];
        } else {
            $file = $this->commonFileFactory->create();
            if (isset($fileData['web_file_id']) && !empty($fileData['web_file_id'])) {
                $file->load($fileData['web_file_id']);
            } else if (isset($fileData['erp_file_id']) && !empty($fileData['erp_file_id'])) {
                $file->load($fileData['erp_file_id'], 'erp_id');
            }
        }

        return $file;
    }

    /**
     * Checks if the RFQ has one or more files not held locally
     *
     * @param array $data
     *
     * @return array
     */
    public function getRfqRemoteFiles($data)
    {
        $remoteFiles = array();

        $lines = $this->_getArrayData($data, 'lines', 'line', 'product_code');

        foreach ($lines as $lineData) {
            $lineFiles = $this->_checkAttachmentsForRemoteContent($this->_getArrayData($lineData, 'attachments', 'attachment', 'filename'));
            $remoteFiles = array_merge($remoteFiles, $lineFiles);
        }

        $rfqFiles = $this->_checkAttachmentsForRemoteContent($this->_getArrayData($data, 'attachments', 'attachment', 'filename'));
        $remoteFiles = array_merge($remoteFiles, $rfqFiles);

        return $remoteFiles;
    }

    private function _checkAttachmentsForRemoteContent($attachments)
    {
        $remoteFiles = array();
        $fileHelper = $this->commFileHelper;
        /* @var $fileHelper Epicor_Common_Helper_File */
        foreach ($attachments as $fileData) {
            if (isset($fileData['erp_file_id']) && !empty($fileData['erp_file_id'])) {
                $file = $this->getFileFromFileData($fileData);
                if (!$file->isObjectNew()) {
                    $content = $fileHelper->getFileContent($file, \Epicor\Common\Helper\File::DATATYPE_DATA);
                    if (!$content) {
                        $remoteFiles[] = $file->getFilename();
                    }
                    unset($content);
                } else {
                    $remoteFiles[] = $fileData['filename'];
                }
                unset($file);
            }
        }

        return $remoteFiles;
    }

    private function _getArrayData($data, $key, $childKey, $testKey)
    {
        $childData = array();
        if (isset($data[$key]) && !empty($data[$key]) && isset($data[$key][$childKey])) {
            $childData = $data[$key][$childKey];
            if (isset($childData[$testKey])) {
                $childData = array($childData);
            }
        }

        return $childData;
    }

    /**
     * Gets the next available web ref for RFQs and incrments the counter
     *
     * @return integer;
     */
    public function getNextRfqWebRef()
    {
        $webRef = $this->registry->registry('customerconnect_rfq_webref');
        if (!$webRef) {
            $webRef = $this->sequenceManager->getSequence('eccquote', 0)->getNextValue();
            $webRef = ltrim($webRef, 0);
            $this->registry->register('customerconnect_rfq_webref', $webRef, true);
        }

        return $webRef;
    }

    /**
     * uplictaes the lines of a quote and sends CIM / MSQ as needed
     *
     * ONLY COPY the following per line
     *
     * <productCode type=”S/N”></productCode> -- YES
     * <groupSequence></groupSequence> -- YES
     * <isKit></isKit> -- YES
     * <unitOfMeasureDescription></unitOfMeasureDescription> -- YES
     * <quantity></quantity> -- YES
     * <description></description> -- YES
     * <detail></detail> -- YES
     * <price></price> -- NO
     * <lineValue></lineValue> -- NO
     * <taxCode></taxCode> -- YES
     * <additionalText></additionalText> -- NO
     * <requestDate></requestDate> -- NO
     * <attachments></attachments> -- NO
     * <attributes></attributes> -- YES
     *
     * @return \Epicor\Common\Model\Xmlvarien
     */
    public function duplicateLines($origRfq, &$newRfq)
    {
        $linesGroup = $origRfq->getLines();
        $oldLines = ($linesGroup) ? $linesGroup->getasarrayLine() : array();
        $newLines = array();
        $errors = array();
        $products = array();
        $lineNum = 1;
        foreach ($oldLines as $line) {
            if (strtoupper($line->getIsKit()) == 'C') {
                continue;
            }
            $line->addData(array(
                'price' => 0,
                'line_value' => 0,
                'miscellaneous_charges_total' => 0,
                'additional_text' => false,
                'request_date' => false,
                'attachments' => false,
                '_attributes_number' => $lineNum
            ));

            $rowProduct = $this->getRfqLineProduct($line);
            $groupSequence = $this->getRfqLineGroupSequence($line);

            if (!empty($groupSequence)) {
                $this->sendCimForRfqLine($groupSequence, $line, $rowProduct, $lineNum);
            }

            $type = $line->getData('product_code')->getData('_attributes')->getType();
            $line->setProduct($rowProduct);
            if (empty($type) || $type == 'S') {

                $products[] = $rowProduct;
            } else {
                $line->addData(array(
                    'price' => 'TBC',
                    'line_value' => 'TBC'
                ));
            }

            $newLines[] = $line;
            $lineNum++;
        }

        if (!empty($products)) {
            $this->sendMsqForRfqProducts($products, $newLines, $newRfq);

            $finalLines = array();
            foreach ($newLines as $line) {
                if ($line->getRemove()) {
                    $errors[] = $line->getData('product_code');
                } else {
                    $finalLines[] = $line;
                }
            }
        } else {
            $finalLines = $newLines;
        }

        $newRfq->setLines($this->commonXmlvarienFactory->create(['data' => array('line' => $finalLines)]));
        if($origRfq->getDealer()){
            $newRfq->setDealer($this->commonXmlvarienFactory->create(['data' => (array)$origRfq->getDealer()->getData()]));
        }
        return $errors;
    }

    /**
     * Gets the product for an RFQ a line
     *
     * @param \Epicor\Common\Model\Xmlvarien $line
     *
     * @return \Epicor\Comm\Model\Product
     */
    public function getRfqLineProduct($line)
    {
        $productCode = (string) $line->getData('product_code');
        $productUom = $line->getData('unit_of_measure_code');
        $rowProduct = $this->findProductBySku($productCode, $productUom, false);
        if (empty($rowProduct) || !$rowProduct instanceof \Epicor\Comm\Model\Product) {
            $rowProduct = $this->catalogProductFactory->create();
            $rowProduct->setSku($productCode);
            $rowProduct->setEccUom($productUom);
        }
        $qty = $line->getQuantity();
        $decimalPlaces = $this->getDecimalPlaces($rowProduct);
        $qty = $this->qtyRounding($qty, $decimalPlaces);

        $rowProduct->setQty($qty);
        $rowProduct->setMsqQuantity($qty);

        return $rowProduct;
    }

    /**
     * Gets the group sequence for an RFQ a line
     *
     * @param \Epicor\Common\Model\Xmlvarien $line
     *
     * @return string
     */
    public function getRfqLineGroupSequence($line)
    {
        $attributes = $line->getAttributes();
        $groupSequence = $line->getGroupSequence();
        if ($attributes) {
            $attributeData = $attributes->getasarrayAttribute();
            foreach ($attributeData as $attribute) {
                if ($attribute['description'] == 'groupSequence') {
                    $groupSequence = $attribute['value'];
                }
            }
        }

        return $groupSequence;
    }

    /**
     * Gets the product for an RFQ a line
     *
     * @param \Epicor\Common\Model\Xmlvarien $line
     * @param \Epicor\Comm\Model\Product $rowProduct
     */
    public function sendCimForRfqLine($groupSequence, &$line, &$rowProduct, $lineNumber = 1)
    {
        $helper = $this->commConfiguratorHelperFactory->create();

        $cimData = array(
            'ewa_code' => '',
            'group_sequence' => $groupSequence,
            'quote_id' => $this->getNextRfqWebRef(),
            'line_number' => $lineNumber,
            'action' => 'C',
        );

        $productId = $rowProduct->getId();
        $cim = $helper->sendCim($productId, $cimData);

        if ($cim->isSuccessfulStatusCode()) {
            $configurator = $cim->getResponse()->getConfigurator();
            $line->setGroupSequence($configurator->getGroupSequence());
            $line->setEwaCode($configurator->getRelatedToRowId());

            $rowProduct->setMsqAttributes(array(
                'Ewa Code' => $configurator->getRelatedToRowId(),
                'groupSequence' => $configurator->getGroupSequence()
            ));

            $attributes = $line->getAttributes();
            $updatedAtts = array();
            if ($attributes) {
                $attributeData = $attributes->getasarrayAttribute();
                $line->unsAttributes();

                foreach ($attributeData as $attribute) {
                    $value = $attribute['value'];

                    if ($attribute['description'] == 'groupSequence') {
                        $value = $configurator->getGroupSequence();
                    }
                    $updatedAtts[] = $this->commonXmlvarienFactory->create(['data' => array(
                            'description' => $attribute['description'],
                            'value' => $value
                    )]);
                }
                $updatedAtts[] = $this->commonXmlvarienFactory->create(['data' => array(
                        'description' => 'Ewa Code',
                        'value' => $configurator->getRelatedToRowId()
                )]);
            } else {
                $updatedAtts[] = $this->commonXmlvarienFactory->create(['data' => array(
                        'description' => 'Ewa Code',
                        'value' => $configurator->getRelatedToRowId()
                )]);
            }

            $line->setAttributes($this->commonXmlvarienFactory->create());
            $line->getAttributes()->setAttribute($updatedAtts);
        }
    }

    /**
     * Sends an MSQ for the array of products provided
     *
     * If send a lines array and RFQ object then it will update those with new prices.
     *
     * @param array $products
     * @param array $lines
     * @param \Epicor\Common\Model\Xmlvarien $rfq
     *
     */
    public function sendMsqForRfqProducts($products, $lines = null, $rfq = null)
    {
        $helper = $this->commMessagingHelper;
        $productHelper = $this->commProductHelperFactory->create();

        $functions = array(
            'setTrigger' => array('crq_line_data_load'),
            'addProducts' => array($products),
        );

        $helper->sendErpMessage('epicor_comm', 'msq', array(), array(), $functions);

        if ($lines && $rfq) {
            $subtotal = 0;

            foreach ($lines as $line) {
                $product = $line->getProduct();

                if ($product->getIsSalable()) {
                    $type = $line->getData('product_code')->getData('_attributes')->getType();
                    if ($type == 'S') {
                        $price = $productHelper->getProductPrice($product, $line->getQuantity());
                        $lineValue = $price * $line->getQuantity();
                        $subtotal += $lineValue;
                        $line->addData(array(
                            'price' => $price,
                            'line_value' => $lineValue,
                        ));
                    }
                } else {
                    $line->setRemove(true);
                }
            }

            $rfq->setGoodsTotal($subtotal);
        }
    }

    /**
     * Obtains the Attribute From an RFQ Line by description
     * @param \Magento\Framework\DataObject $row
     * @param string/array $description
     * @return string $value
     */
    public function getAttributeValueFromLineByDescription($line, $description)
    {
        $value = false;

        $attributes = $line->getAttributesAttribute();
        if (is_array($attributes)) {
            foreach ($attributes as $attribute) {
                if ($attribute instanceof \Magento\Framework\DataObject && in_array($attribute->getDescription(), (array) $description)) {
                    $value = $this->escaper->escapeHtml($attribute->getValue());
                    break;
                }
            }
        }

        return $value;
    }

}
