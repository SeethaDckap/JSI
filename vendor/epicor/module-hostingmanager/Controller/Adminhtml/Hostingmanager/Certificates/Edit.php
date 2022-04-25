<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates;

class Edit extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $cert = $this->_loadCertificate($id);

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('SSL Certificates'));

        if ($cert->getId())
            $resultPage->getConfig()->getTitle()->prepend(__("Edit %1", $cert->getName()));
        else
            $resultPage->getConfig()->getTitle()->prepend(__("Add Certificate"));

        return $resultPage;
    }

}
