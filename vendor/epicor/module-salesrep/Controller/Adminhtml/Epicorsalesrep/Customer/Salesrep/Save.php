<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Save extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    public function execute()
    {

        if ($data = $this->getRequest()->getPost()) {
            $salesRep = $this->_initSalesRepAccount();
            /* @var $salesRep \Epicor\SalesRep\Model\Account */

            $this->_session->setFormData($data);

            try {
                $this->_processDetailsSave($salesRep, $data);

                if ($salesRep->isObjectNew()) {
                    $salesRep->save();
                }

                $this->_processSalesRepsSave($salesRep, $data);
                $this->_processErpAccountsSave($salesRep, $data);
                $this->_processHierarchySave($salesRep, $data);
                //$this->_processPricingRulesSave($salesRep, $data);

                $salesRep->save();

                if (!$salesRep->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Sales Rep Account'));
                }

                $this->messageManager->addSuccessMessage(__('Sales Rep Account was successfully saved.'));
                $this->_session->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $salesRep->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->_session->setFormData(false);
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($salesRep && $salesRep->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $salesRep->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addErrorMessage(__('No data found to save'));
        $this->_redirect('*/*/');
    }

}
