<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon;

use Magento\Backend\App\Action;

/**
 * Response SUCO - Upload Supplier Connect Users 
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
abstract class AbstractMapping extends \Epicor\Comm\Controller\Adminhtml\Generic {

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }

    protected function _initPage() {

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');

        $resultPage->getConfig()->getTitle()->prepend(__('Mapping'));

        return $resultPage;
    }

}
