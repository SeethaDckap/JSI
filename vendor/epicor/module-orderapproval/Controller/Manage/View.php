<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class View extends \Epicor\Customerconnect\Controller\Generic
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_group_details';
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->_forward('edit', null, null, ['view-only' => 1]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('id', null)) {
            return parent::_isAllowed();
        }

        return true;
    }
}
