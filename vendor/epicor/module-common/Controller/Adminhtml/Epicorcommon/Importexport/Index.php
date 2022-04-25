<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport
{

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }

    }
