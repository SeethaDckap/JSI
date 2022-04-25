<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class ErpAccountSessionSet extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $request = $this->getRequest();
        /** @var \Magento\Framework\App\Request\Http $request */
        $data = $this->getRequest()->getPost();
        if ($data['linkTypeValue']) {
            $selectedErpAccount = $data['linkTypeValue'];
            $this->backendAuthSession->setlinkTypeValue($selectedErpAccount);
        }
    }
}
