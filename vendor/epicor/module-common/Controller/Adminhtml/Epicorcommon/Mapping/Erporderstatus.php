<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

abstract class Erporderstatus extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {

     protected $_aclId = 'Epicor_Common::mapping_erp_order_status';

    private function _getStoreParams() {
        $storeId = (int) $this->getRequest()->getParam('store');
        return is_null($storeId) ? array() : array('store' => $storeId);
    }

}
