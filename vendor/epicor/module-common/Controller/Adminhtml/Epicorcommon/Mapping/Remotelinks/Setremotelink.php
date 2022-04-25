<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Remotelinks;

class Setremotelink extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Remotelinks
{


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $name = $this->getRequest()->getParam('name');
        $this->_session->unsRemoteLink($name);
        $this->_session->setRemoteLink($name);
        //$this->_redirect('*/adminhtml_system_variable/wysiwygPlugin');
    }

}
