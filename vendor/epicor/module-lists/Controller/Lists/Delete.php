<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Delete extends \Epicor\Lists\Controller\Lists
{

    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_delete';
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->delete($id);
        $this->_redirect('*/*/');
    }

}
