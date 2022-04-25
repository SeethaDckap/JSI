<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates;

class Delete extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $cert = $this->_loadCertificate($id);

        $cert->delete();

        $this->_redirect('*/*/');
    }

}
