<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model;

use Epicor\Customerconnect\Api\DownloadAttachmentManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;
use Epicor\Comm\Model\Message\Request\Freq;
use Epicor\Common\Helper\File as CommonHelperFile;
use Epicor\Customerconnect\Api\Data\DownloadAttachmentResponseInterface;

/**
 * Class DownloadAttachmentManagement
 * @package Epicor\Customerconnect\Model
 */
class DownloadAttachmentManagement implements DownloadAttachmentManagementInterface
{
    /**
     * constant prefix for order number
     */
    const PREFIX = 'order';
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var DocumentPrint
     */
    private $documentPrint;
    /**
     * @var Http
     */
    private $request;
    /**
     * @var Freq
     */
    private $messageRequestfreq;
    /**
     * @var CommonHelperFile
     */
    private $commonFileHelper;
    /**
     * @var DownloadAttachmentResponseInterface
     */
    private $downloadAttachmentData;

    /**
     * DownloadAttachmentManagement constructor.
     * @param CustomerSession $customerSession
     * @param DocumentPrint $documentPrint
     * @param Http $request
     * @param Freq $messageRequestFreq
     * @param CommonHelperFile $commonFileHelper
     * @param DownloadAttachmentResponseInterface $downloadAttachmentData
     */
    public function __construct(
        CustomerSession $customerSession,
        DocumentPrint $documentPrint,
        Http $request,
        Freq $messageRequestFreq,
        CommonHelperFile $commonFileHelper,
        DownloadAttachmentResponseInterface $downloadAttachmentData
    ) {
        $this->customerSession = $customerSession;
        $this->documentPrint = $documentPrint;
        $this->request = $request;
        $this->messageRequestfreq = $messageRequestFreq;
        $this->commonFileHelper = $commonFileHelper;
        $this->downloadAttachmentData = $downloadAttachmentData;
    }

    /**
     * @return Epicor\Customerconnect\Api\Data\DownloadAttachmentResponseInterface
     */
    public function downloadAttachment()
    {
        $data = $this->request->getParams();
        if (count($data) > 0) {
            $orderNumber = self::PREFIX . "_" . $data['order_number'];
            $message = $this->messageRequestfreq;
            $messageTypeCheck = $message->getHelper()->getMessageType('FREQ');
            if ($message->isActive() && $messageTypeCheck) {
                $message->setErpFileId($data['erp_file_id']);
                $message->setFilename($data['file_name']);
                $message->setAction($data['action']);
                $message->freqSentForOrderAttachment(true);
                if ($result = $message->sendMessage()) {
                    $rawData = $message->getRawFileData();
                    $ableToDownload = $this->prepareDocumentToDownload($rawData, $orderNumber);
                    if (is_array($ableToDownload) && count($ableToDownload) > 0) {
                        $this->downloadAttachmentData->setResponseData(
                            $this->downloadFile($ableToDownload['doc_type'], $data['filename_to_download'])
                        );
                    } else {
                        $this->downloadAttachmentData->setResponseData(array(
                            'message' => __('Document not available.'),
                            'type' => 'error'
                        ));
                    }
                } else {
                    $this->downloadAttachmentData->setResponseData(array(
                        'message' => __('Unable to download the document ' . $data['filename_to_download']),
                        'type' => 'error'
                    ));
                }
            } else {
                $this->downloadAttachmentData->setResponseData(array(
                    'message' => __('Document download option is not available.'),
                    'type' => 'error'
                ));
            }
        } else {
            $this->downloadAttachmentData->setResponseData(array(
                'message' => __('No Data requested.'),
                'type' => 'error'
            ));
        }
        return $this->downloadAttachmentData;
    }

    /**
     * @param $fileData
     * @param $orderNumber
     * @return array|bool
     */
    public function prepareDocumentToDownload($fileData, $orderNumber)
    {
        $success = false;
        $rawData = isset($fileData) ? $fileData : '';
        if (!empty($rawData)) {
            $fileData = $rawData->getData('data');
            $encType = $fileData->getData('_attributes')->getEncodeType();
            $fileDataToDownload = $encType == 'B' ? base64_decode($fileData->getValue()) : $fileData->getValue();
            $this->customerSession->setEncodedFreqData(base64_encode($fileDataToDownload));
            $this->customerSession->setCustomerOrderNumber($orderNumber);
            $downloadPathData = $this->commonFileHelper->getDownloadFilePathData($fileDataToDownload);
            $success = $downloadPathData;
        }
        return $success;
    }

    /**
     * @param $docType
     * @param $filenameToDownload
     * @return bool
     */
    public function downloadFile($docType, $filenameToDownload)
    {
        $encodedDocumentData = $this->customerSession->getEncodedFreqData();
        if (isset($encodedDocumentData) && !empty($encodedDocumentData)) {
            $fileName = $filenameToDownload;
            $createFile = fopen("$fileName", "w") or die("Unable to open file!");
            fwrite($createFile, base64_decode($encodedDocumentData));
            fclose($createFile);
            header("Content-Type: $docType");
            header("Content-Disposition: attachment; filename=" . urlencode($fileName));
            header("Content-Transfer-Encoding: binary");
            header("Content-Description: File Transfer");
            header("Content-Length: " . filesize($fileName));
            $this->customerSession->unsEncodedFreqData();
            $this->customerSession->unsCustomerOrderNumber();
            readfile($fileName);
            return $fileName;
        } else {
            return false;
        }
    }
}