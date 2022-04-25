<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locationgroups;

class Index extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::location_groups_manage');

        $resultPage->getConfig()->getTitle()->prepend(__('Locations - Groups'));

        return $resultPage;
    }

}
