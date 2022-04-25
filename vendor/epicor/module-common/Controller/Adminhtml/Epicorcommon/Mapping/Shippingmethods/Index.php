<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingmethods;



class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingmethods
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
