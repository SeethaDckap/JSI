<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Test;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Groupdeleteproduct extends \Magento\Framework\App\Action\Action {

    protected $_resultPageFactory;
    protected $product;

    public function __construct(Context $context, \Epicor\Comm\Helper\Product $product, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->product = $product;
        parent::__construct($context);
    }

    public function execute() {
        $this->product->getAllGroupedProductListToDelete();
        echo 'success';
    }

}
