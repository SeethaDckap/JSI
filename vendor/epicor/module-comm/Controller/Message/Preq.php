<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Message;

use \Epicor\Customerconnect\Model\DocumentPrint;

class Preq extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\PreqQueue\CollectionFactory
     */
    protected $preqCollectionFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Preq
     */
    protected $customerconnectMessageRequestPreq;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Epicor\Customerconnect\Model\ResourceModel\PreqQueue\CollectionFactory $preqCollectionFactory,
        \Epicor\Customerconnect\Model\Message\Request\Preq $customerconnectMessageRequestPreq,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->preqCollectionFactory = $preqCollectionFactory;
        $this->customerconnectMessageRequestPreq = $customerconnectMessageRequestPreq;
        $this->eventManager = $eventManager;
        $this->commonHelper = $commonHelper;
        $this->commonFileHelper = $commonFileHelper;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->ioFile = $ioFile;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        $return = true;
        //Processing starts here
        $id = $this->getRequest()->getParam('id');
        $message = $this->customerconnectMessageRequestPreq;
        $messageTypeCheck = $message->getHelper()->getMessageType('PREQ');
        if ($message->isActive() && $messageTypeCheck) {
            $helper = $this->commonHelper;
            //$modelId = explode("=", $schedule->getMessages());
            //$modelId = $modelId[1];
            $helper->setPhpTimeLimits();
            $helper->setPhpMemoryLimits();
            $item = $this->preqCollectionFactory->create()
                ->addFieldToFilter('entity_id', array('eq' => $id))
                ->addFieldToFilter('ready_status', array('eq' => 0))
                ->getFirstItem();

            if ($item->getId()) {
                $return = $this->_sendAndProcessPreq($item, $message);
            }
        }
        $response = json_encode(array('message' => __('Email request processed'), 'type' => 'success'));
        $this->getResponse()->setBody( $response);
    }

    public function _sendAndProcessPreq($item, $message)
    {
        $error = 0;
        $responseConfig = [];
        $requestIds = array_map(
            function ($arr) {
                return $arr['entityKey'];
            },
            unserialize($item->getRequestConfig())
        );
        foreach ($requestIds as $id) {
            $message = $this->constructPreq($item, $id);
            if ($message->sendMessage()) {
                $file = $message->getResponse()->getPrint();
                $rawData = $file ? $file->getData('data') : '';
                if (!empty($rawData)) {
                    $this->processFile($rawData, $item->getEntityId(), $id);
                    $responseConfig[] = array("entityKey" => $id, "success" => true);
                } else {
                    $responseConfig[] = array("entityKey" => $id, "success" => false);
                    $error++;
                }
            } else {
                $responseConfig[] = array("entityKey" => $id, "success" => false);
                $error++;
            }
        }
        $status = $error > 0 ? 2 : 1;
        if ($status === 1) {
            try {
                $success = $this->commonFileHelper->_processEmail($item);
                $this->deleteProcessedFiles($item->getEntityId());

                if (is_array($success)) {
                    $status = 2;
                    $this->logger->debug($success['message']);
                }
            } catch (\Exception $e) {
                $status = 2;
                $this->logger->debug($e->getMessage());
            }
        }
        $item->setResponseConfig(serialize($responseConfig))
            ->setReadyStatus($status)
            ->save();

        if ($status === 2) {
            $this->_reprocessPreq($item);
        }

        return true;
    }

    public function _reprocessPreq($item)
    {
        $error = 0;
        $failedRetries = [];
        $retryCountLimit = $this->scopeConfig->getValue('customerconnect_enabled_messages/preq_request/retry_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 3;
        $requestConfig = unserialize($item->getRequestConfig());
        $responseConfig = unserialize($item->getResponseConfig());
        $failedIds = array_map(
            function ($arr) {
                return $arr['entityKey'];
            },
            array_filter($responseConfig, function ($arrayValue) {
                return $arrayValue['success'] == false;
            })
        );
        $count = 0;
        foreach ($failedIds as $id) {
            $requestId = array_filter($requestConfig, function ($arrayValue) use ($id) {
                return $arrayValue['entityKey'] == $id;
            });
            if (isset($requestId[$count]) && $requestId[$count]['retryCount'] < $retryCountLimit) {
                $selectedReqId = array_keys(array_filter($requestConfig, function ($item) use ($id) {
                    return $item['entityKey'] === $id;
                }))[0];
                $selectedRespId = array_keys(array_filter($responseConfig, function ($item) use ($id) {
                    return $item['entityKey'] === $id;
                }))[0];
                $message = $this->constructPreq($item, $id);
                if ($message->sendMessage()) {
                    $file = $message->getResponse()->getPrint();
                    $rawData = $file ? $file->getData('data') : '';

                    if (!empty($rawData)) {
                        $this->processFile($rawData, $item->getEntityId(), $id);
                        $responseConfig[$selectedRespId]["success"] = true;
                    } else {
                        $responseConfig[$selectedRespId]["success"] = false;
                        $error++;
                    }
                } else {
                    $responseConfig[$selectedRespId]["success"] = false;
                    $error++;
                }
                $requestConfig[$selectedReqId]['retryCount'] = $requestId[$count]['retryCount'] + 1;
            } else {
                $error++;
                $failedRetries[] = $id;
            }
            $count++;
        }
        $status = $error > 0 ? 2 : 1;
        if ($status === 1) {
            try {
                //send success email
                $success = $this->commonFileHelper->_processEmail($item);
                $this->deleteProcessedFiles($item->getEntityId());

                if (is_array($success)) {
                    $status = 2;
                    $this->logger->debug($success['message']);
                }
            } catch (\Exception $e) {
                $status = 2;
                $this->logger->debug($e->getMessage());
            }
        } elseif (count($failedRetries) === count($failedIds)) {
            $status = 3; //processed with errors
            try {
                //send email without the faulty id's
                $success = $this->commonFileHelper->_processEmail($item, $failedIds);
                $this->deleteProcessedFiles($item->getEntityId());

                if (is_array($success)) {
                    $status = 2;
                    $this->logger->debug($success['message']);
                }
            } catch (\Exception $e) {
                $status = 2;
                $this->logger->debug($e->getMessage());
            }
        }
        $item->setRequestConfig(serialize($requestConfig))
            ->setResponseConfig(serialize($responseConfig))
            ->setReadyStatus($status)
            ->save();

        if ($status === 2) {
            $this->_reprocessPreq($item);
        }
        return true;
    }

    public function constructPreq($item, $id)
    {
        $message = $this->customerconnectMessageRequestPreq;
        $message->setAccountNumber($item->getAccountNumber());
        $message->setEntityDocument($item->getEntityDocument());
        $message->setEntityKey($id);
        $message->setAction('E');
        $message->setIsMassAction(true);

        return $message;

    }

    public function processFile($rawData, $entityId, $id)
    {
        $encType = $rawData->getData('_attributes')->getEncodeType();
        $fileData = $encType == 'B' ? base64_decode($rawData->getValue()) : $rawData->getValue();
        $mimeType = DocumentPrint::getDocumentMimeType($fileData);
        $extension = DocumentPrint::getFileExtension($mimeType);
        $fileName = $id . $extension;
        $this->serveFile($fileData, $entityId, $fileName);
    }

    public function serveFile($fileData, $pathPrefix, $fileName)
    {
        $preqFolder = $this->commonFileHelper->getPreqFolderPath() . DIRECTORY_SEPARATOR . 'entity-' . $pathPrefix;
        $fileExist = $preqFolder . DIRECTORY_SEPARATOR . $fileName;
        if (!$this->driverFile->isExists($fileExist)) {
            $this->ioFile->mkdir($preqFolder, 0775);
            $this->ioFile->open(array('path' => $preqFolder));
            $this->ioFile->write($fileExist, $fileData, 0777);
        }
    }

    public function deleteProcessedFiles($entityId)
    {
        $preqFolder = $this->commonFileHelper->getPreqFolderPath();
        $pathPrefix = 'entity-' . $entityId;
        $directoryExist = $preqFolder . DIRECTORY_SEPARATOR . $pathPrefix;
        if ($this->driverFile->isExists($directoryExist)) {
            $this->driverFile->deleteDirectory($directoryExist);
        }
    }

}