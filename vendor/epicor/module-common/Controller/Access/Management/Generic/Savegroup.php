<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Controller\Access\Management\Generic;

class Savegroup extends \Epicor\Common\Controller\Access\Management\Generic
{


    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $group = $this->_loadGroup($id);

            try {

                $erpAccount = $this->registry->registry('access_erp_account');
                if (!$this->registry->registry('access_group_global')) {
                    $group->setErpAccountId($erpAccount->getId());
                    $group->setEntityName($data['name']);

                    if ($erpAccount->isTypeSupplier()) {
                        $group->setType('supplier');
                    } else {
                        $group->setType('customer');
                    }

                    $group->save();

                    if (!$group->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Access Group'));
                    }

                    $this->saveRights($data, $group);
                }

                $this->saveContacts($data, $group, $erpAccount);

                $this->generic->addSuccess(__('Access Group was successfully saved.'));
                $this->generic->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/editgroup', array('id' => $group->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->generic->addError($e->getMessage());
                if ($group && $group->getId()) {
                    $this->_redirect('*/*/editgroup', array('id' => $group->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }
        }
    }

}
