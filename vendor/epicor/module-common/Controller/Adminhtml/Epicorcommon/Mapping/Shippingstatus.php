<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

use Magento\Backend\App\Action;

abstract class Shippingstatus extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }

    protected function _isAllowed() {
        return $this->backendAuthSession
                        ->isAllowed('Epicor_Common::mapping_shipping_status'); 
    }

}
