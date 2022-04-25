<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations;

class Index extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations
{


    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::locations');

        $resultPage->getConfig()->getTitle()->prepend(__('Locations'));

        return $resultPage;
    }

    }
