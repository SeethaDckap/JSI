<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

/*
 * Controller for epicor_comm_erp_attributes grid  
 * in admin/epicorcomm_mapping_erpattributes
 */

abstract class Erpattributes extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {

    protected $_aclId = 'Epicor_Common::mapping_erp_attributes';

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }

}
