<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Productimportcsv extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
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
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename= example_list_product_import.csv");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

        $csvString  = "SKU,UOM,Currency,Price,Break Qty,Break Price,Description";
        $csvString .= "\n";
        $csvString .= "ExampleProduct1,EA,USD,3.99,5,3.8,Description for Qty 5 break";
        $csvString .= "\n";
        $csvString .= "ExampleProduct2,EA,USD,3.99,10,3.75,Description for Qty 10 break";
        $this->getResponse()->setBody($csvString);
    }

}
