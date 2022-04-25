<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Export extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
     parent::__construct($context, $backendAuthSession);
    }

    /**
     * Generates a CSV that can be used for upload
     *
     * @return void
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type', "erpaccounts");
        if ($type == 'erpaccounts') {
            $fileName = "example_erp_account_import";
            $csvString = 'ERP Short Code' . "\n" . 'ExampleErpCode1';
        } elseif ($type == 'customer') {
            $fileName = "example_customer_account_import";
            $csvString = 'Customer Email Address' . "\n" . 'customer@example.com';
        }
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename= $fileName.csv");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

        $this->getResponse()->setBody($csvString);
    }

}
