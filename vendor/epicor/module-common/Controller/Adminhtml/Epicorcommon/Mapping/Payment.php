<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

abstract class Payment extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {

    protected $_aclId = 'Epicor_Common::mapping_payment';

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }

}
