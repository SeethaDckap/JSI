<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Message;

class Crqu extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqu
     */
    protected $customerconnectMessageRequestCrqu;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu
    ) {
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->commonFileHelper = $commonFileHelper;
        $this->customerconnectMessageRequestCrqu = $customerconnectMessageRequestCrqu;
        parent::__construct(
            $context
        );
    }


    public function execute()
    {
        $helper = $this->customerconnectRfqHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Rfq */
        $fileHelper = $this->commonFileHelper;
        /* @var $fileHelper Epicor_Common_Helper_File */
        $data = $this->getRequest()->getParam('data');

        if ($data) {
            $newData = unserialize(base64_decode($data));
            $crqu = $this->customerconnectMessageRequestCrqu;
            /* @var $crqu Epicor_Customerconnect_Model_Message_Request_Crqu */
            $duplicate = isset($newData['is_duplicate']) ? true : false;
            if ($crqu->isActive() && $helper->getMessageType('CRQU')) {

                $aFiles = array();
                $lFiles = array();
                if (isset($newData['attachments'])) {
                    $aFiles = $fileHelper->processPageFiles('attachments', $newData, $duplicate, true);
                }

                if (isset($newData['lineattachments'])) {
                    $lFiles = $fileHelper->processPageFiles('lineattachments', $newData, $duplicate, true);
                }

                $files = array_merge($aFiles, $lFiles);

                $crqu->setAction('A');
                $crqu->setQuoteNumber('');
                $crqu->setQuoteSequence('');
                $crqu->setOldData(array());
                $crqu->setNewData($newData);
                $crqu->setAccountNumber($newData['account_number']);

                if ($crqu->sendMessage()) {
                    $rfq = $crqu->getResults();
                    $helper->processCrquFilesSuccess($files, $rfq);
                } else {
                    $helper->processCrquFilesFail($files);
                }
            }
        }
    }

}
