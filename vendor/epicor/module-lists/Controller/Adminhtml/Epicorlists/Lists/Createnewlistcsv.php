<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Createnewlistcsv extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Generates a CSV that can be used for create a new list
     *
     * @return void
     */
    public function execute()
    {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename= example_create_new_list.csv");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

        $csvString = '"Header",""' . "\n" .
            '"List Code","LISTCODE"' . "\n" .
            '"Title","New List Title"' . "\n" .
            '"Type","PG"' . "\n" .
            '"Active","Y"' . "\n" .
            '"Notes","List Notes"' . "\n" .
            '"Account Link Type","B"' . "\n" .
            '"Mandatory List","Y"' . "\n" .
            '"Default list","Y"' . "\n" .
            '"Auto load","Y"' . "\n" .
            '"",' . "\n" .
            '"Accounts",exclude="Y|N"' . "\n" .
            '"ERPCode1",' . "\n" .
            '"ERPCode2",' . "\n" .
            '"",""' . "\n" .
            '"Products",exclude="Y|N"' . "\n" .
            '"SKU","UOM","Currency","Price","Break Qty","Break Price","Description"' . "\n" .
            '"ExampleProduct1","EA","USD","3.99","5","3.8","Description for Qty 5 break"' . "\n" .
            '"ExampleProduct2","EA","USD","3.99","10","3.75","Description for Qty 10 break"';

        $this->getResponse()->setBody($csvString);
    }

    }
