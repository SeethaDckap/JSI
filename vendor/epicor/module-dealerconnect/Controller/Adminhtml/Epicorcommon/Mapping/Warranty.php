<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping;

abstract class Warranty extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {

    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_Common::epicorcommon_mapping_warranty';

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, 
    \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }

    private function _getStoreParams() {
        $storeId = (int) $this->getRequest()->getParam('store');
        return is_null($storeId) ? array() : array('store' => $storeId);
    }

}
