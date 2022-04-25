<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Test;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Groupdeletedownloadcsv extends \Magento\Framework\App\Action\Action {

    protected $_resultPageFactory;
    protected $product;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Epicor\Comm\Helper\Product $product
    ) {
        $this->product = $product;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
        $adminsession = $this->product->getAdminSession();
        if (isset($adminsession['status']) && $adminsession['status'] == 1) {
            $resultPage = $this->_resultPageFactory->create();
            return $resultPage;
        } else {
            $customMessage = __("Sorry!! You don't have enough privileges to access the requested page.");
            $this->messageManager->addErrorMessage($customMessage);
            $this->_redirect('/');
            return false;
        }
    }

}
