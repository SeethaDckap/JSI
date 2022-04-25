<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class ErpAccountSessionSet extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
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
