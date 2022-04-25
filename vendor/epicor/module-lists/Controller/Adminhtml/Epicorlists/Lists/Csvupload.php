<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

use Epicor\Comm\Helper\Data;
use Epicor\Lists\Controller\Adminhtml\Context;
use Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;
use Magento\Backend\Model\Auth\Session;
use Epicor\Lists\Helper\Data as ListHelper;
use Epicor\Lists\Model\ImportManagement;

/**
 * Class Csvupload
 * @package Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
 */
class Csvupload extends Lists
{
    /**
     * Redirect upload path.
     */
    const ADD_BY_CSV_PATH = "*/*/addbycsv";

    /**
     * @var ListHelper
     */
    protected $listsHelper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var ImportManagement
     */
    protected $importManagement;

    /**
     * Csvupload constructor.
     * @param Context $context
     * @param Session $backendSession
     * @param ListHelper $listsHelper
     * @param ImportManagement $importManagement
     */
    public function __construct(
        Context $context,
        Session $backendSession,
        ListHelper $listsHelper,
        ImportManagement $importManagement
    ) {
        $this->listsHelper = $listsHelper;
        $this->backendSession = $backendSession;
        $this->importManagement = $importManagement;
        parent::__construct($context, $backendSession);
    }

    /**
     * Uploads CSV file
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            if (count($_FILES['csv_file']['tmp_name']) > 1) {
                //Mass upload using Consumer Cron
                $this->importMass($_FILES['csv_file']);
            } else {
                //Single Import
                $this->importSingle();
            }
        } else {
            $this->_redirect(
                self::ADD_BY_CSV_PATH
            );
        }
    }

    /**
     * Import Single File.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function importSingle()
    {
        if (!in_array($_FILES['csv_file']['type'][0], Data::CSV_APPLIED_FORMAT)) {
            $this->messageManager->addErrorMessage('Wrong File Type. Only CSV files are allowed.');
            return $this->_redirect(
                self::ADD_BY_CSV_PATH
            );
        }

        /* @var $helper ListHelper */
        $helper = $this->listsHelper;

        $result = $helper->importListFromCsv(
            $_FILES['csv_file']['tmp_name'][0]
        );

        if (isset($result['errors']['errors'])
            && is_array($result['errors']['errors'])
        ) {
            foreach ($result['errors']['errors'] as $error) {
                $this->messageManager->addErrorMessage($error);
            }
        }

        if (isset($result['errors']['warnings'])
            && is_array($result['errors']['warnings'])
        ) {
            foreach ($result['errors']['warnings'] as $error) {
                $this->messageManager->addWarningMessage($error);
            }
        }

        if (isset($result['list']) && $result['list']->getId()) {
            $this->_redirect(
                '*/*/edit',
                array('id' => $result['list']->getId())
            );
        } else {
            $this->_redirect(self::ADD_BY_CSV_PATH);
        }
    }

    /**
     * Import Mass.
     *
     * @param $files
     */
    private function importMass($files)
    {
        $nonCsvs = array();
        $csvs = array();
        for ($i = 0; $i < count($files['tmp_name']); $i++) {
            if (!in_array($files['type'][$i], Data::CSV_APPLIED_FORMAT)) {
                array_push($nonCsvs, $files['name'][$i]);
            } else {
                if ($this->importManagement->checkFileDir()) {
                    $newFilePath = $this->importManagement->getFilePath(
                        $files['name'][$i]
                    );

                    if (move_uploaded_file($files['tmp_name'][$i], $newFilePath)) {
                        $this->importManagement->addNewFile($files['name'][$i]);
                    }

                    array_push($csvs, $files['name'][$i]);
                }
            }
        }

        if (!empty($csvs)) {
            $this->messageManager->addSuccessMessage(__('Your Request is on Queue.'));
        }

        if (!empty($nonCsvs)) {
            $nonCsvsName = implode(',', $nonCsvs);
            $errorMessage = $nonCsvsName . ' has wrong file type. Only CSV files are allowed.';
            $this->messageManager->addErrorMessage($errorMessage);
        }

        $this->_redirect(self::ADD_BY_CSV_PATH);
    }
}

