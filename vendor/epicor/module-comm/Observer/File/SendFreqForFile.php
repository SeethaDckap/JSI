<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\File;

class SendFreqForFile extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Updates a file model to send an FREQ if the file is not found locally
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer_File
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commFileHelper;
        /* @var $helper Epicor_Comm_Helper_File */

        $fileData = $observer->getEvent()->getFileData();
        $fileModel = $observer->getEvent()->getFileModel();
        $result = $helper->requestFile($fileData['web_file_id'], $fileData['erp_file_id'], $fileData['filename']);

        if (!empty($result)) {
            $updatedModel = $result['file_model'];
            $fileModel->setData($updatedModel->getData());
        }

        return $this;
    }

}