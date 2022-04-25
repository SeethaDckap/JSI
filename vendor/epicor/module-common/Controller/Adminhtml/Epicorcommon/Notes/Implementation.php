<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Notes;

class Implementation extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Notes
{

   public function __construct(
       \Epicor\Comm\Controller\Adminhtml\Context $context,
       \Magento\Backend\Model\Auth\Session $backendAuthSession)
   {
       parent::__construct($context, $backendAuthSession);
   }

    public function execute()
    {
        $version = $this->getRequest()->getParam('version');
        $url = $this->_baseUrl . 'EpicorCommerceConnect_ImplementationGuide_' . $version . '.pdf';
        $this->getResponse()->setRedirect($url);
    }

    }
