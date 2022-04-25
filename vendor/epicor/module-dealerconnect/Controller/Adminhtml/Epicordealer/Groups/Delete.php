<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class Delete extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Deletes single Dealer Group
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->delete($id);
        $this->_redirect('*/*/');
    }

}
