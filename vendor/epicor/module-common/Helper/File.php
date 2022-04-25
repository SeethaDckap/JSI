<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;

use \Magento\Framework\Exception\NotFoundException;
use Epicor\Customerconnect\Model\DocumentPrint;

class File extends \Epicor\Common\Helper\Data
{

    /**
     * @var \Epicor\Comm\Model\Customer
     */
    private $_customer;
    private $_erpAccountId;

    const DATATYPE_URL = 'U';
    const DATATYPE_DATA = 'D';
    const PREQ_TEMPLATE_ID = 'ecc_document_email_template';


    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory
     */
    protected $commResourceCustomerReturnModelAttachmentCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Epicor\Common\Model\Mail\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var  \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timeZone;

    protected $productMetadata;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory $commResourceCustomerReturnModelAttachmentCollectionFactory,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Epicor\Common\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ) {
        $this->commonFileFactory = $commonFileFactory;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->commResourceCustomerReturnModelAttachmentCollectionFactory = $commResourceCustomerReturnModelAttachmentCollectionFactory;
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_storeManager = $context->getStoreManager();
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_timeZone = $context->getTimezone();
        $this->productMetadata = $context->getProductMetaData();
        $this->driverFile = $driverFile;
        parent::__construct($context);
    }

    public function processFileUpload($fileData)
    {
        $file = $this->_loadFile($fileData);
        if (!$file->isObjectNew()) {
            $fileData['action'] = 'U';
            $file->setPreviousData(serialize($file->getData()));
        } else {
            $fileData['action'] = 'A';
        }

        if (!empty($fileData['name'])) {
            $file->setFilename($fileData['name']);
        }
        if (!empty($fileData['erp_file_id'])) {
            $file->setErpId($fileData['erp_file_id']);
        }

        if (!empty($fileData['description'])) {
            $file->setDescription($fileData['description']);
        }
        if (!empty($fileData['url'])) {
            $file->setUrl($fileData['url']);
        }

        if (!empty($fileData['customer_id'])) {
            $file->setCustomerId($fileData['customer_id']);
        }

        if (!empty($fileData['erp_account_id'])) {
            $file->setErpAccountId($fileData['erp_account_id']);
        }
        if (!empty($fileData['url'])) {
            $file->setUrl($fileData['url']);
        }

        if (!empty($fileData['source'])) {
            $file->setSource($fileData['source']);
        }
        $file->save();

        if (!empty($fileData['content'])) {
            $this->writeFile($file, $fileData);
        }

        $fileData['web_file_id'] = $file->getId();
        $fileData['filename'] = $file->getFilename();

        if (empty($fileData['url']) && $fileData['source'] == 'web') {
            $fileData['url'] = $this->getFileContent($file, self::DATATYPE_URL);
        }
        $fileData['file_model'] = $file;
        return $fileData;
    }

    private function writeFile($file, $fileData)
    {
        if ($this->checkFileDir()) {

            $filePath = $this->getFilePath($file);

            if (file_exists($filePath)) {
                file_put_contents($filePath . '.temp', file_get_contents($filePath));
            }

            file_put_contents($filePath, $fileData['content']);
        }
    }

    public function restoreTempFile($file)
    {
        $filePath = $this->getFilePath($file);
        $tempPath = $filePath . '.temp';

        if (file_exists($filePath) && file_exists($tempPath)) {
            unlink($filePath);
            file_put_contents($filePath, file_get_contents($tempPath));
            unlink($tempPath);
        }
    }

    public function removeTempFile($file)
    {
        $filePath = $this->getFilePath($file);
        $filePath .= '.temp';

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function removeFile($file)
    {
        $filePath = $this->getFilePath($file);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function checkFileDir()
    {
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$baseDir = Mage::getBaseDir('base');
        $baseDir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        //M1 > M2 Translation End
        $filePath = $baseDir . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('epicor_common/files/base_dir', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . DIRECTORY_SEPARATOR;

        if (!file_exists($filePath)) {
            mkdir($filePath);
            // possibly here create a .htaccess file that prevents access
        }

        return file_exists($filePath);
    }

    /**
     *
     * @param \Epicor\Common\Model\File $file
     * @param string $dataType
     *
     * @return mixed
     */
    public function getFileContent($file, $dataType)
    {
        $content = false;
        $filePath = $this->getFilePath($file);

        if (file_exists($filePath)) {
            if ($dataType == self::DATATYPE_DATA) {
                $content = file_get_contents($filePath);
            } else if ($dataType == self::DATATYPE_URL) {
                $content = $this->getFileUrl($file->getId(), $file->getErpId(), $file->getFilename());
            }
        }
        return $content;
    }

    /**
     *
     * @param \Epicor\Common\Model\File $file
     * @param string $dataType
     *
     * @return mixed
     */
    public function hasFileContent($file)
    {
        $hasContent = false;
        $content = $this->getFileContent($file, self::DATATYPE_DATA);

        if ($content !== false) {
            $hasContent = true;
        }
        unset($content);

        return $hasContent;
    }

    /**
     * Loads a file from file data
     *
     * @param array $fileData
     *
     * @return \Epicor\Common\Model\File
     */
    private function _loadFile($fileData)
    {
        $file = $this->commonFileFactory->create();
        /* @var $file Epicor_Common_Model_File */
        if (isset($fileData['web_file_id']) && !empty($fileData['web_file_id'])) {
            $file->load($fileData['web_file_id']);
        }

        if ($file->isObjectNew() && isset($fileData['erp_file_id']) && !empty($fileData['erp_file_id'])) {
            $file->load($fileData['erp_file_id'], 'erp_id');
        }

        return $file;
    }

    public function canCustomerAccessFile($fileData)
    {
        $file = $this->_loadFile($fileData);

        if (!$file->isObjectNew()) {
            $access = false;
            $customerSession = $this->customerSession;
            /* @var $customerSession Mage_Customer_Model_Session */

            if ($customerSession->isLoggedIn()) {
                $customer = $customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */

                $commHelper = $this->commHelper;
                /* @var $commHelper Epicor_Comm_Helper_Data */
                $erpAccount = $commHelper->getErpAccountInfo();
                /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                $defaults = $erpAccount->getDefaultForStores();
                $b2b = (!$defaults) ? true : false;

                if ($b2b) {
                    if ($erpAccount->getId() == $file->getErpAccountId()) {
                        $access = true;
                    }
                } else if ($file->getCustomerId() && $customer->getId() == $file->getCustomerId()) {
                    $access = true;
                }
            } else {
                // not logged in guest, so need to see if this is a return file
                if (!$file->getCustomerId()) {
                    $erpAccount = $this->commCustomerErpaccountFactory->create()->load($file->getErpAccountId());
                    /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                    $b2b = false;

                    if (!$erpAccount->isObjectNew()) {
                        $defaults = $erpAccount->getDefaultForStores();
                        $b2b = (!$defaults) ? true : false;
                    }

                    if (!$b2b) {

                        // only thing to do here is to check to see if it's a return attachment,
                        // as that's the only thing that guests can upload files for
                        $collection = $this->commResourceCustomerReturnModelAttachmentCollectionFactory->create();
                        /* @var $collection Epicor_Comm_Model_Resource_Customer_Return_Attachment_Collection */
                        $collection->addFieldToFilter('attachment_id', $file->getId());
                        $returnAttachment = $collection->getFirstItem();
                        /* @var $returnAttachment Epicor_Comm_Model_Customer_ReturnModel_Attachment */

                        if (!$returnAttachment->isObjectNew()) {
                            $return = $this->commCustomerReturnModelFactory->create()->load($returnAttachment->getReturnId());
                            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
                            if (!$return->isObjectNew() && $return->canBeAccessedByCustomer()) {
                                $access = true;
                            }
                        }
                    }
                }
            }
        } else {
            $access = true;
        }

        return $access;
    }

    public function serveFile($fileData)
    {
        $file = $this->_loadFile($fileData);

        $filePath = $this->getFilePath($file);
        $fileErpId = $file->getErpId();

        if ($file->isObjectNew() || (!empty($fileErpId) && !file_exists($filePath))) {
            // do something here with FREQ

            $this->_eventManager->dispatch('epicor_common_file_not_found', array('file_data' => $fileData, 'file_model' => $file));

            if ($file->isObjectNew()) {
                throw new NotFoundException(__('File not found.'));
            }
        }

        $filePath = $this->getFilePath($file);

        //$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        if (file_exists($filePath)) {
            header('Content-type: ' . mime_content_type($filePath));
            header('Content-Disposition: attachment; filename="' . $file->getFilename() . '"');
            readfile($filePath);
        } else {
            throw new NotFoundException(__('File not found.'));
        }
    }

    private function getFilePath($file)
    {
        $key = $file->makeFileKey();
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$baseDir = Mage::getBaseDir('base');
        $baseDir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        //M1 > M2 Translation End
        return $baseDir . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('epicor_common/files/base_dir', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . DIRECTORY_SEPARATOR . $key;
    }

    /**
     * @param string $dataKey - key to denote what type of attachments (lineattachments/attachments)
     * @param array $data - $_POST data to be sent on in relevant message
     */
    public function processPageFiles($dataKey, &$data, $isDuplicate = false, $copyErpFiles = false)
    {
        $files = array();

        $fileData = (isset($_FILES[$dataKey])) ? $_FILES[$dataKey] : false;

        if (!empty($fileData) || $isDuplicate) {
            $customerSession = $this->customerSessionFactory->create();
            $this->_customer = $customerSession->getCustomer();
            $helper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */

            if ($this->_customer->isCustomer()) {
                $erpAccount = $helper->getErpAccountInfo();
                $this->_erpAccountId = $erpAccount->getId();
            } else if ($this->_customer->isSupplier()) {
                $erpAccount = $helper->getErpAccountInfo(null, 'supplier');
                $this->_erpAccountId = $erpAccount->getId();
            }

            $eFiles = $this->processFileData($fileData, $data, $dataKey, 'existing', $isDuplicate, $copyErpFiles);
            $nFiles = $this->processFileData($fileData, $data, $dataKey, 'new', $isDuplicate, $copyErpFiles);
            $files = array_merge($eFiles, $nFiles);
        }
        return $files;
    }

    /**
     * Processes $_FILES and $_POST data to upload files & build message data
     *
     * @param array $fileData - $_FILES data for the provided $dataKey
     * @param array $data - $_POST data from the CRQD/ new RFQ page to be sent on in CRQU
     * @param string $dataKey - key to denote what type of attachments (lineattachments/attachments)
     * @param string $subKey - existing/new attachments
     */
    private function processFileData($fileData, &$data, $dataKey, $subKey, $isDuplicate, $copyErpFiles)
    {
        $files = array();
        $empty = array();
        $dataTemp = array();
        if (isset($data[$dataKey][$subKey]) & !empty($data[$dataKey][$subKey])) {
            foreach ($data[$dataKey][$subKey] as $rowId => $row) {

                if ($dataKey == 'attachments' || $dataKey == 'claimattachments') {

                    if (isset($fileData['name'][$subKey][$rowId]['filename']) && !empty($fileData['name'][$subKey][$rowId]['filename'])) {

                        $fileInfo = array(
                            'name' => $fileData['name'][$subKey][$rowId]['filename'],
                            'tmp_name' => $fileData['tmp_name'][$subKey][$rowId]['filename'],
                            'type' => $fileData['type'][$subKey][$rowId]['filename']
                        );

                    } else {
                        $fileInfo = array();
                    }

                    if (isset($row['delete']) || isset($row[0]['delete'])) {
                        $file = $row;
                    } else {
                        $file = $this->processAttachmentUpload($subKey, $fileInfo, $row, $isDuplicate, $copyErpFiles);
                        $dataTemp[$dataKey][$subKey][$rowId] = $file;
                    }

                    $files[] = $file;
                } else {
                    foreach ($row as $childId => $child) {
                        if (isset($fileData['name'][$subKey][$rowId][$childId]['filename']) && !empty($fileData['name'][$subKey][$rowId][$childId]['filename'])) {
                            $fileInfo = array(
                                'name' => $fileData['name'][$subKey][$rowId][$childId]['filename'],
                                'tmp_name' => $fileData['tmp_name'][$subKey][$rowId][$childId]['filename'],
                                'type' => $fileData['type'][$subKey][$rowId][$childId]['filename'],
                            );
                        } else {
                            $fileInfo = array();
                        }

                        if (isset($child['delete'])) {
                            $file = $child;
                        } else {
                            $file = $this->processAttachmentUpload($subKey, $fileInfo, $child, $isDuplicate, $copyErpFiles);
                            $file['line_number'] = $rowId;
                            $dataTemp[$dataKey][$subKey][$rowId][$childId] = $file;
                        }

                        $file['line_number'] = $rowId;
                        $files[] = $file;
                    }
                }
            }
            if(isset($dataTemp[$dataKey])){
                $data[$dataKey] = array_merge($data[$dataKey],$dataTemp[$dataKey]);
            }
        }
        return $files;
    }

    private function processAttachmentUpload($subKey, $fileInfo, $attachment, $isDuplicate, $copyErpFiles)
    {
        if ($isDuplicate && empty($fileInfo)) {
            if (isset($attachment['old_data'])) {
                $oldData = unserialize(base64_decode($attachment['old_data']));
                $oldFile = $this->_loadFile($oldData);
                $hasContent = $this->hasFileContent($oldFile);
                $newDesc = isset($attachment['description']) ? $attachment['description'] : '';
                if ($hasContent) {
                    $oldFile->setDescription($newDesc);
                    $file = $this->duplicateFile($oldFile, $copyErpFiles);
                } else if ($copyErpFiles && $oldData) {
                    $oldData['description'] = $newDesc;
                    $file = $this->duplicateFile($oldData, $copyErpFiles);
                } else {
                    if (!$this->registry->registry('download_erp_files')) {
                        $this->registry->register('download_erp_files', true);
                    }
                    $file = $attachment;
                }
            } else {
                $file = $attachment;
            }
        } else {
            $oldData = array();
            if ($subKey == 'existing') {
                $oldData = unserialize(base64_decode($attachment['old_data']));
            }
            $upload = array(
                'name' => isset($oldData['name']) ? $oldData['name'] : '',
                'type' => '',
                'content' => '',
                'description' => $attachment['description'],
                'source' => '',
                'customer_id' => $this->_customer->getId(),
                'erp_account_id' => $this->_erpAccountId,
                'old_data' => ''
            );

            if (!empty($fileInfo)) {
                $upload['name'] = $fileInfo['name'];
                $upload['type'] = $fileInfo['type'];
                $tmpFile = $fileInfo['tmp_name'];
                $content = file_get_contents($tmpFile);
                $upload['content'] = $content;
                $upload['source'] = 'web';
            }

            if ($subKey == 'existing') {
                $upload['upload_data'] = $attachment['old_data'];
            }
            if ($subKey == 'existing') {
                $upload['erp_file_id'] = $oldData['erp_file_id'];
                $upload['web_file_id'] = $oldData['web_file_id'];
                $upload['url'] = $oldData['url'];
            }

            $file = $this->processFileUpload($upload);
        }

        if (isset($file['content'])) {
            unset($file['content']);
        }

        return $file;
    }

    public function getFileUrl($webId, $erpId, $filename, $altUrl = '', $attachmentStatus = '', $isAdmin = false)
    {
        $url = "";
        if (empty($altUrl)) {
            $keyParams = array(
                'web_file_id' => $webId,
                'erp_file_id' => $erpId,
                'filename' => $filename,
            );

            $key = base64_encode($this->urlEncoder->encode(serialize($keyParams)));
            //M1 > M2 Translation Begin (Rule p2-4)
            //$url = Mage::getUrl('epicor/file/request', array('file' => $key));
            if($isAdmin){
                $url = $this->_urlBuilder->getDirectUrl('epicor/file/request/file/'.$key);
            }else{
                $url = $this->_getUrl('epicor/file/request', array('file' => $key));
            }
            //M1 > M2 Translation End
        } else {
            $url = $altUrl;
        }

        return $url;
    }

    /**
     * Duplicates a file
     *
     * @param array $fileData
     * @return array
     */
    public function duplicateFile($file, $getRemote = false)
    {
        $newFile = array();

        /* @var $file Epicor_Common_Model_File */
        if (is_array($file) && $getRemote) {
            $content = $this->getRemoteContent($file['web_file_id'], $file['erp_file_id'], $file['filename'], $file['url']);

            $newFile = $this->processFileUpload(array(
                'name' => $file['filename'],
                'content' => $content,
                'description' => $file['description'],
                'erp_file_id' => '',
                'web_file_id' => '',
                'url' => '',
                'source' => 'web',
                'customer_id' => '',
                'erp_account_id' => ''
            ));

            unset($content);
            unset($newFile['content']);
        } else if (!$file->isObjectNew()) {
            $newFile = $file->duplicate($getRemote);
        }

        return $newFile;
    }

    /**
     * Gets remote file content
     *
     * @return string $content
     */
    public function getRemoteContent($webFileId, $erpFileId, $fileName, $fileUrl)
    {
        try {
            $url = $this->getFileUrl($webFileId, $erpFileId, $fileName, $fileUrl);

            $connection = new \Zend_Http_Client();
            $adapter = new \Zend_Http_Client_Adapter_Curl();
            $connection->setUri($url);
            $adapter->setCurlOption(CURLOPT_HEADER, FALSE);
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, FALSE);
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, FALSE);
            $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
            $adapter->setCurlOption(CURLOPT_TIMEOUT, 999);

            $connection->setAdapter($adapter);

            $response = $connection->request(\Zend_Http_Client::GET);

            $data = $response->getBody();
            $status = $response->getStatus();

            if (!empty($data) && $status == 200) {
                return $data;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sends email from PREQ response
     *
     * @return boolean
     */
    public function _processEmail($item, $failedIds = [])
    {
        $success = false;
        $item = $this->processPreqItem($item, $failedIds);
        $mailBody = $item['params']['message'];
        if(!empty($failedIds)){
            $mailBody = $mailBody. ".\r\n";
            $mailBody .= "Unable to process Id's ". implode(', ',$failedIds);
        }
        $setting = 'general';
        $from = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailTemplateVariables = array(
            'message' => $mailBody,
            'subject' => $item['params']['subject']
            //'subject' => 'Document Requests For '.ucfirst($item['params']['entity_document']).', '.$this->_timeZone->formatDate(null, \IntlDateFormatter::MEDIUM)
        );
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier(self::PREQ_TEMPLATE_ID)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom([
                'name' => $name,
                'email' => $from,
            ])
            ->addTo($this->sanitizeEmailAddr($item['params']['to']));

        if(!empty($item['params']['cc'])){
            $this->_transportBuilder->addCc($this->sanitizeEmailAddr($item['params']['cc']));
        }

        if(!empty($item['params']['bcc'])){
            $this->_transportBuilder->addBcc($this->sanitizeEmailAddr($item['params']['bcc']));
        }

        try {
            foreach($item['fileData'] as $key => $fileData){
                $content = base64_decode($fileData['content']);
                $mimeType = DocumentPrint::getDocumentMimeType(base64_decode($fileData['content']));
                $extension = DocumentPrint::getFileExtension($mimeType);
                $fileName = $fileData['fileName'] . $extension;
                $this->_transportBuilder->addAttachment($content, $fileName, $mimeType);
            }
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            $success = true;
        } catch (\Exception $e) {
            $error[] = $e->getMessage();
            $success = array("success" => false, "message" => $e->getMessage());
        }

        return $success;
    }

    public function processPreqItem($item, $failedIds = [])
    {
        $resultArr = array("fileData" => [], 'params' => []);
        $preqFolder = $this->getPreqFolderPath();
        $pathPrefix = $item->getEntityId();
        $requestConfig = unserialize($item->getRequestConfig());
        $directoryExist = $preqFolder . DIRECTORY_SEPARATOR . 'entity-'.$pathPrefix;
        if ($this->driverFile->isExists($directoryExist)) {
            $ids = array_map(
                function ($arr) {
                    return $arr['entityKey'];
                },
                !empty($failedIds) ? array_filter($requestConfig, function ($arrayValue) use($failedIds) {
                    return !in_array($arrayValue['entityKey'], $failedIds);
                }) : $requestConfig
            );

            foreach ($ids as $id){
                $filePath = glob($directoryExist . DIRECTORY_SEPARATOR . $id . '*', GLOB_NOSORT);
                $fileInfo = array(
                    'content' => base64_encode(file_get_contents($filePath[0])),
                    'fileName' => $id
                );
                array_push($resultArr['fileData'], $fileInfo);
            }
        }
        $resultArr['params'] = unserialize($item->getEmailParams());
        $resultArr['params']['entity_document'] = $item->getEntityDocument();

        return $resultArr;
    }

    public function getPreqFolderPath()
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) .
            DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('epicor_common/files/base_dir', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . DIRECTORY_SEPARATOR . 'preq' . DIRECTORY_SEPARATOR . 'documents';
    }

    /**
     * @param null $binaryFile
     * @return array
     */
    public function getPrintFilePathData($binaryFile = null)
    {
        $mimeType = DocumentPrint::getDocumentMimeType($binaryFile);

        if ($mimeType === 'application/pdf') {
            return  ['success' => true, 'url' => DocumentPrint::PDF_PRINT_PATH, 'doc_type' => $mimeType];
        }

        if ($mimeType === 'image/gif' || $mimeType === 'image/jpg' || $mimeType === 'image/jpeg') {
            return  ['success' => true, 'url' => DocumentPrint::IMAGE_PRINT_PATH, 'doc_type' => $mimeType];
        }
    }

    /**
     * @param $addr
     * @return array
     */
    public function sanitizeEmailAddr($addr)
    {
        $processAddr = [];
        $addr = explode(';',$addr);
        if ($this->productMetadata->getVersion() < '2.3.0') {
            foreach ($addr as $add){
                array_push($processAddr, trim($add));
            }
        }else{
            foreach ($addr as $add){
                $processAddr[trim($add)] = null;
            }
        }
        return $processAddr;

    }
    /**
     * @param null $binaryFile
     * @return array
     */
    public function getDownloadFilePathData($binaryFile = null)
    {
        $mimeType = DocumentPrint::getDocumentMimeType($binaryFile);

        if ($mimeType === 'application/pdf') {
            return  ['success' => true, 'url' => DocumentPrint::DOWNLOAD_DOC_PATH, 'doc_type' => $mimeType];
        }

        if ($mimeType === 'image/gif' || $mimeType === 'image/jpg' || $mimeType === 'image/jpeg') {
            return  ['success' => true, 'url' => DocumentPrint::DOWNLOAD_DOC_PATH, 'doc_type' => $mimeType];
        }
    }
}
