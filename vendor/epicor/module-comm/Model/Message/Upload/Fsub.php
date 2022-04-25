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
class Fsub extends \Epicor\Comm\Model\Message\Upload
{

    /**
     *
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $_helper;
    private $_fileData;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

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
        $this->setMessageType('FSUB');
        $this->setLicenseType(array('Consumer', 'Customer', 'Supplier'));
        $this->setConfigBase('epicor_comm_enabled_messages/fsub_request/');

    }

    public function processAction()
    {
        $file = $this->getRequest()->getFile();

        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $url = '';

        $rawData = $file->getData('data');
        $encType = $rawData->getData('attributes')->getEncodeType();

        $content = $encType == 'B' ? base64_decode($rawData) : $rawData;

        try {
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
        } catch (\Exception $e) {
            throw new \Exception(
            $this->getErrorDescription(self::STATUS_ERROR_SAVING_FILE), self::STATUS_ERROR_SAVING_FILE
            );
        }

        if (!$this->_fileData) {
            throw new \Exception(
            $this->getErrorDescription(self::STATUS_ERROR_SAVING_FILE), self::STATUS_ERROR_SAVING_FILE
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

            $message['messages']['request']['body'] = array_merge(
                $message['messages']['request']['body'], array(
                'file' => array(
                    'erpFileId' => $this->_fileData['erp_file_id'],
                    'webFileId' => $this->_fileData['web_file_id'],
                ),
                )
            );
        }

        $this->setOutXml($message);
    }

}
