<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 */
class Freq extends \Epicor\Comm\Model\Message\Upload
{

    /**
     *
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $_helper;
    protected $_dataType;
    protected $_encodeType;
    protected $_file;
    protected $_fileContent;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commonFileHelper = $context->getCommonFileHelper();
        $this->commonFileFactory = $commonFileFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('FREQ');
        $this->setLicenseType(array('Consumer', 'Customer', 'Supplier'));
        $this->setConfigBase('epicor_comm_enabled_messages/freq_request/');

    }

    public function processAction()
    {
        $file = $this->getRequest()->getFile();

        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $fileModel = $this->commonFileFactory->create();
        /* @var $fileModel Epicor_Common_Model_File */

        if ($file->getWebFileId()) {
            $fileModel->load($file->getWebFileId());
            if ($fileModel->isObjectNew()) {
                throw new \Exception(
                $this->getErrorDescription(self::STATUS_UNKNOWN_FILEID, 'Web File ID'), self::STATUS_FILE_NOT_FOUND
                );
            }
        } else if ($file->getErpFileId()) {
            $fileModel->load($file->geterpFileId(), 'erp_id');
            if ($fileModel->isObjectNew()) {
                throw new \Exception(
                $this->getErrorDescription(self::STATUS_UNKNOWN_FILEID, 'ERP File ID'), self::STATUS_FILE_NOT_FOUND
                );
            }
        }

        if ($fileModel->isObjectNew()) {
            throw new \Exception(
            $this->getErrorDescription(self::STATUS_FILE_NOT_FOUND), self::STATUS_FILE_NOT_FOUND
            );
        }

        $this->_dataType = $file->getData('attributes')->getDataType();
        $this->_fileContent = $helper->getFileContent($file, $this->_dataType);
        $this->_file = $fileModel;

        if (!$this->_fileContent) {
            throw new \Exception(
            $this->getErrorDescription(self::STATUS_ERROR_READING_FILE), self::STATUS_FILE_NOT_FOUND
            );
        }

        $this->buildResponse();
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

            $encodeType = $this->_dataType == 'U' ? 'P' : 'B';
            $content = $this->_dataType == 'U' ? $this->_fileContent : base64_encode($this->_fileContent);

            $message['messages']['request']['body'] = array_merge(
                $message['messages']['request']['body'], array(
                'file' => array(
                    'erpFileId' => $this->_file->getErpId(),
                    'webFileId' => $this->_file->getId(),
                    'filename' => $this->_file->getFilename(),
                    'description' => $this->_file->getDescription(),
                    'url' => $this->_file->getUrl(),
                    'data' => array(
                        '_attributes' => array(
                            'encodeType' => $encodeType
                        ),
                        $content
                    ),
                ),
                )
            );
        }

        $this->setOutXml($message);
    }

}
