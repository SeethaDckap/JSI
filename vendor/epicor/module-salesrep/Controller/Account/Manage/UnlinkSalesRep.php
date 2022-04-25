<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class UnlinkSalesRep extends \Epicor\SalesRep\Controller\Account\Manage
{
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('salesreps');
        $error = $this->_processSalesRep($ids, 'unlink');

        if (!$error) {
            $this->messageManager->addSuccessMessage(__('%1 Sales Reps unlinked', count($ids)));
        } else {
            $this->messageManager->addErrorMessage(__('Could not unlink one or more Sales Reps, please try again'));
        }

        $this->_redirectToSalesReps();
    }

    }
