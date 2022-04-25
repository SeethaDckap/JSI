<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class Edit extends \Epicor\Lists\Controller\Lists
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_group_edit';

    /**
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('id', null)) {
            if($this->getRequest()->getParam('view-only', null)) {
                return parent::_isAccessAllowed('Epicor_Customer::my_account_group_details');
            } else {
                return parent::_isAllowed();
            }
        }

        return true;
    }
}
