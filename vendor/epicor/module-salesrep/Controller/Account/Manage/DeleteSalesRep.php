<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class DeleteSalesRep extends \Epicor\SalesRep\Controller\Account\Manage
{

    public function execute()
    {
        $ids = (array)$this->getRequest()->getParam('salesreps');

        $error = $this->_processSalesRep($ids, 'delete');

        if (!$error) {
            //M1 > M2 Translation Begin (Rule 55)
            //$this->messageManager->addSuccess(__('%s Sales Reps deleted', count($ids)));
            $this->messageManager->addSuccessMessage(__('%1 Sales Reps deleted', count($ids)));
            //M1 > M2 Translation End
        } else {
            $this->messageManager->addErrorMessage(__('Could not delete one or more Sales Reps, please try again'));
        }

        $this->_redirectToSalesReps();
    }

}
