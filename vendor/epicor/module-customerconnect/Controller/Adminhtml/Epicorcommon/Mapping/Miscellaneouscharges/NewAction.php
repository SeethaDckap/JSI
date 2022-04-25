<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges;

class NewAction extends \Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges
{

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $this->_forward('edit');
    }

}
