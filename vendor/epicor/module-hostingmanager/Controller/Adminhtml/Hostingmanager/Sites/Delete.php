<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites;

class Delete extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites
{

    public function execute()
    {

        $id = $this->getRequest()->getParam('id', null);
        $site = $this->_loadSite($id);

        if (!$site->getIsDefault()) {
            $this->messageManager->addSuccessMessage(__('Site deleted'));
            $site->delete();
        } else {
            $this->messageManager->addErrorMessage(__('Default Site can not be deleted'));
        }

        $this->_redirect('*/*/');
    }

    }
