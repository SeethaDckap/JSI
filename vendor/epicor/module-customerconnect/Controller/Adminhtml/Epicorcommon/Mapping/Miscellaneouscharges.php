<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping;

abstract class Miscellaneouscharges extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {


    protected $_aclId = 'Epicor_Customerconnect::epicorcommon_mapping_miscellaneouscharges';

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }

}
