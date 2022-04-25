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
 * @method Epicor_Common_Model_File getFile()
 * @method string getAction()
 * @method setFile(Epicor_Common_Model_File $file)
 * @method setAction(string $action)
 * 
 */
class Fsub extends \Epicor\Comm\Model\Message\Request
{
    /**
     * @var string
     */
    protected $_dataType;
    
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
        $this->setLicenseType('Consumer', 'Customer', 'Supplier');
        $this->setConfigBase('epicor_comm_enabled_messages/fsub_request/');

    }

    /**
     * Bulds the XML request from the set data on this message.
     * @return bool successful message.
     */
    public function buildRequest()
    {

        $message = $this->getMessageTemplate();

        $file = $this->getFile();
        /* @var $file Epicor_Common_Model_File */

        $this->setMessageSecondarySubject('File ID: ' . $file->getId());

        $dataType = $this->getConfig('data_type');

        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $url = $helper->getFileContent($file, 'U');
        $fileContent = $helper->getFileContent($file, $this->_dataType);

        $encodeType = $dataType == 'U' ? 'P' : 'B';
        $content = $dataType == 'D' ? base64_encode($fileContent) : '';

        $message['messages']['request']['body'] = array_merge(
            $message['messages']['request']['body'], array(
            'file' => array(
                '_attributes' => array(
                    'dataType' => $dataType,
                    'action' => $file->getAction()
                ),
                'erpFileId' => $file->getErpId(),
                'webFileId' => $file->getId(),
                'filename' => $file->getFilename(),
                'description' => $file->getDescription(),
                'url' => $url,
                'data' => array(
                    '_attributes' => array(
                        'encodeType' => $encodeType
                    ),
                    $content
                ),
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

            $fileResponse = $this->getResponse()->getFile();

            $file = $this->getFile();
            /* @var $file Epicor_Common_Model_File */

            if ($file->getAction() == 'R') {
                $file->delete();
            } else {
                $file->setErpId($fileResponse->getErpFileId());
                $file->setAction('');
                $file->save();
            }
        }

        return $success;
    }

}
