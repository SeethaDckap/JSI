<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates;

class NewAction extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates
{

    public function execute()
    {
        $this->_forward('edit');
    }

}
