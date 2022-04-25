<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Test;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Productcheck extends \Magento\Framework\App\Action\Action {

    protected $_resultPageFactory;
    protected $directorylist;

    public function __construct(Context $context, \Magento\Framework\App\Filesystem\DirectoryList $directorylist, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->directorylist = $directorylist;
        parent::__construct($context);
    }

    public function execute() {
        $productchecklog = $this->directorylist->getPath('var') . '/log/productcheck.log';
        if (file_exists($productchecklog)) {
            unlink($productchecklog);
            echo 'success';
        } else {
            echo "SORRY!!!! File doesn't exist...";
        }
    }

}
