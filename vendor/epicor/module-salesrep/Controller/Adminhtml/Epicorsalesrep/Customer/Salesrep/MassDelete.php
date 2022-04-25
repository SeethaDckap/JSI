<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class MassDelete extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    public function __construct(
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context
    )
    {
        parent::__construct($context);
    }


    public function execute()
    {
        $ids = (array)$this->getRequest()->getParam('accounts');

        foreach ($ids as $id) {
            $this->delete($id, true);
        }
        $this->messageManager->addSuccessMessage(__(count($ids) . ' Sales Rep Accounts deleted'));
        $this->_redirect('*/*/');
    }

}
