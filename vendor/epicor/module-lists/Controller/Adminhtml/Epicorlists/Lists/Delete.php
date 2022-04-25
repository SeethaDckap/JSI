<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Delete extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Deletes single List
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
