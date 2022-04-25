<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites;

class Edit extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $site = $this->_loadSite($id);

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sites'));


        if ($site->getId())
            $resultPage->getConfig()->getTitle()->prepend(__("Edit %1", $site->getName()));
        else
            $resultPage->getConfig()->getTitle()->prepend(__('Add Site'));

        return $resultPage;
    }

    }
