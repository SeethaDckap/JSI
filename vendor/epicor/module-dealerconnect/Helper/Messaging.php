<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Helper;


/**
 * Generic grid helper
 * 
 * used for propcessing rows displayed by the generic grid for the various message types
 */
class Messaging extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;
    
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $_claimStatusMapping;
    
    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping
    )
    {
        $this->commFileHelper = $commFileHelper;
        $this->localeFormat = $localeFormat;
        $this->_claimStatusMapping = $claimStatusMapping;
        parent::__construct($context);
    }
    /**
     * Processes files uploaded / updatded on the DCLD page, after a successful DCLU
     * 
     * @param array $files
     * @param \Epicor\Comm\Model\Xmlvarien $rfq
     */
    public function processDcluFilesSuccess($files, $claim)
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
        $this->processDcluAttachments($claim, $sortedData, $sendFsub);

        // process line attachments
        $linesGroup = $claim->getLines();
        if ($linesGroup) {
            $lines = $linesGroup->getasarrayLine();
            foreach ($lines as $line) {
                $this->processDcluAttachments($line, $sortedData, $sendFsub);
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
    private function processDcluAttachments($data, $files, $sendFsub)
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
     * Processes files uploaded / updatded on the CRQD page, after a failed CRQU
     * 
     * @param array $files
     */
    public function processDcluFilesFail($files)
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
    
    public function urlWithoutHttp()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrlArray = array();
        switch (true) {
            case (stristr($baseUrl, 'http://') !== false):
                $baseUrlArray = explode("http://", $baseUrl);
                break;
            case (stristr($baseUrl, 'https://') !== false):
                $baseUrlArray = explode("https://", $baseUrl);
                break;
            default:
                $baseUrlArray[1] = $baseUrl;
                break;
        }
        return $baseUrlArray[1];
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
     * Get the checkout config for RFQ
     * 
     * @return string
     */
    public function getCheckoutConfigForRfq()
    {
        $checkoutConfig = array(
            'quoteData' => '',
            'basePriceFormat' =>'',
            'priceFormat' =>$this->localeFormat->getPriceFormat(),
            'storeCode' =>'',
            'totalsData' =>'',
        );
        $checkoutConfig = \Zend_Json::encode($checkoutConfig);
        return $checkoutConfig;
    }

    public function getStatuses($type)
    {
        $_statuses = [];
        switch (true) {
            case 'claim':
                $_request = [
                    ['value' => 'Overdue', 'label' => "Overdue"],
                    ['value' => 'Today', 'label' => "Today"],
                    ['value' => 'Future', 'label' => "Future"]
                ];
                $_statuses = $this->_claimStatusMapping->getEccClaimStatus();
                $key = array_search('request', array_column($_statuses, 'value'));
                if ($key !== false) {
                    $result = array_merge(
                    array_slice($_statuses, 0, $key+1, true),
                    $_request,
                    array_slice($_statuses, $key+1, count($_statuses) - 1, true)
                    );
                    $keys = array_column($result, 'value');
                    $values = array_column($result, 'label');
                    $_statuses = array_combine($keys, $values);
                }
                break;
        }
        return $_statuses;
    }
}
