<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales;

abstract class Returns extends \Epicor\Comm\Controller\Adminhtml\Generic {

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }

    protected function _isAllowed() {
        return $this->backendAuthSession
                        ->isAllowed('Epicor_Comm::sales_returns');
    }

    protected function _initPage() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::returns');

        return $resultPage;
    }

}
