<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class ErpAccountSessionSet extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data['linkTypeValue']) {
            $selectedErpAccount = $data['linkTypeValue'];
            $this->backendAuthSession->setlinkTypeValue($selectedErpAccount);
        }
    }

    }
