<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Warranty;

class Index extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Warranty
{

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {

        return $this->_initPage();
    }

    }
