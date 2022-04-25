<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Advanced\Entityreg;

class MarkForDeletion extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Advanced\Entityreg
{

    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('rowid');
        foreach ($ids as $id) {
            $this->delete($id, true);
        }
        $this->messageManager->addSuccessMessage(__(count($ids) . ' Uploaded items marked for deletion'));
        $this->_redirect('*/*/');
    }

}
