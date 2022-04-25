<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method string getErpFileId(type $paramName) Gets the ERP File id for this request
 * @method string getWebFileId(type $paramName) Gets the Web File id for this request
 * @method string getFilename(type $paramName) Gets the File name for this request
 * 
 * @method Epicor_Comm_Model_Message_Request_Freq setErpFileId(string $erpFileId) Sets the ERP File id for this request
 * @method Epicor_Comm_Model_Message_Request_Freq setWebFileId(string $webFileId) Sets the Web File id for this request
 * @method Epicor_Comm_Model_Message_Request_Freq setFilename(string $filename) Sets the File name for this request
 * 
 */
class Freq extends \Epicor\Comm\Model\Message\Request
{

    private $_fileData;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;
    /**
     * @var $rawFileData
     */
    private $rawFileData;
    /**
     * @var $freqSentForOrderAttachment
     */
    private $freqSentForOrderAttachment;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commonFileHelper = $context->getCommonFileHelper();
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('FREQ');
        $this->setLicenseType(['Consumer', 'Customer', 'Supplier']);
        $this->setConfigBase('epicor_comm_enabled_messages/freq_request/');

    }
    /**
     * Bulds the XML request from the set data on this message.
     * @return bool successful message.
     */
    public function buildRequest()
    {
        $this->setMessageSecondarySubject('File: ');
        $message = $this->getMessageTemplate();

        $message['messages']['request']['body'] = array_merge(
            $message['messages']['request']['body'], array(
            'file' => array(
                '_attributes' => array(
                    'dataType' => $this->getConfig('data_type')
                ),
                'erpFileId' => $this->getErpFileId(),
                'webFileId' => $this->getWebFileId(),
                'filename' => $this->getFilename(),
            ),
            )
        );

        $this->setOutXml($message);
        return true;
    }

    /**
     * Process the message response.
     * @return bool successful
     */
    public function processResponse()
    {
        $success = false;
        if ($this->isSuccessfulStatusCode()) {
            $success = true;
            $file = $this->getResponse()->getFile();
            $this->rawFileData =$file;
            //if freq request sent from order attachment then skip the file creation and save steps ,just return
            if($this->freqSentForOrderAttachment){
                return $success;
            }
            $helper = $this->commonFileHelper;
            /* @var $helper Epicor_Common_Helper_File */

            $content = '';
            $url = '';

            $dataType = $this->getConfig('data_type');

            $rawData = $file->getData('data');
            $encType = $rawData->getData('_attributes')->getEncodeType();

            $fileData = $encType == 'B' ? base64_decode($rawData->getValue()) : $rawData->getValue();

            if ($dataType == 'D') {
                $content = $fileData;
            }
            $this->_fileData = $helper->processFileUpload(
                array(
                    'name' => $file->getFilename(),
                    'content' => $content,
                    'description' => $file->getDescription(),
                    'erp_file_id' => $file->getErpFileId(),
                    'web_file_id' => $file->getWebFileId(),
                    'url' => $file->getUrl(),
                    'source' => 'erp',
                    'customer_id' => '',
                    'erp_account_id' => '',
                )
            );
        }

        return $success;
    }

    public function getFileData()
    {
        return $this->_fileData;
    }

    /**
     * @return mixed
     */
    public function getRawFileData (){
        return $this->rawFileData;
    }

    /**
     * @param $orderAttachmentAvailable
     * @return bool
     */
    public function freqSentForOrderAttachment($orderAttachmentAvailable){
       $this->freqSentForOrderAttachment = ($orderAttachmentAvailable) ? $orderAttachmentAvailable : false;
    }
}